"""
INPUT:    policy, context
OUTPUT:   actuatorPlans = [num1,num2,0,num4]
usage:    populations[i].actuatorPlan[k] / populations[i].score
"""
import random, operator

class ActuatorPlan():
    def __init__(self, actuatorPlan, score, numOfGeneration):
        self.actuatorPlan    = actuatorPlan
        self.score           = score
        self.numOfGeneration = numOfGeneration

def inilization(parameters, actuators, policy, context):
    return [ActuatorPlan([random.uniform(actuators[k].lowerPower,actuators[k].upperPower) for k in range(parameters.maxNumActuators)],0,0)   for i in range(parameters.maxPopulation)], \
           [ActuatorPlan([None for k in range(parameters.maxNumActuators)],100,0) for i in range(parameters.maxSelection)]
####################################################### phase 1
def mask(state, actuator):
    state = actuator.upperPower - state
    if  state < actuator.lowerPower/2:
        state = 0.0
    elif state < actuator.lowerPower:
        state = actuator.lowerPower
    return state

def shift(state, actuator, parameters):
    state += random.gauss(0, parameters.mu) * (actuator.upperPower - actuator.lowerPower) * parameters.radius
    if  state > actuator.upperPower:
        return actuator.upperPower
    elif state < actuator.lowerPower:
        return actuator.lowerPower
    else:
        return state
        
def crossover(populations, parameters, actuators, index, policy, models, context):
    samples = random.sample(xrange(parameters.maxPopulation), parameters.maxSampling) # select 3 numbers from maxPopulation
    scoreNew = 100 # temp score
    for sample in samples:
        actuatorPlanSample = []
        for k in range(parameters.maxNumActuators):
            ## Crossover : uniform
            if k/2 == 0: actuatorPlanSample.append(populations[index].actuatorPlan[k])
            else:        actuatorPlanSample.append(populations[sample].actuatorPlan[k])
            ## Mutation 1 : shift
            if  random.random() <= parameters.shiftRate:
                actuatorPlanSample[k] = shift(actuatorPlanSample[k], actuators[k], parameters)
            ## Mutation 2 : mask
            if  random.random() <= parameters.maskRate:
                actuatorPlanSample[k] = mask(actuatorPlanSample[k], actuators[k])
        scoreSample = fitness(actuatorPlanSample, parameters, actuators, policy, models, context)
        if  scoreSample < scoreNew:
            actuatorPlanNew = actuatorPlanSample
            scoreNew        = scoreSample
    return actuatorPlanNew, scoreNew

def generation(populations, parameters, actuators, policy, models, context):
    global globalCounter
    ## new populations to avoid replace the old populations
    populationsNew = [ActuatorPlan([None for k in range(parameters.maxNumActuators)],None,globalCounter) for i in range(parameters.maxPopulation)]
    ## for each population
    for i in range(parameters.maxPopulation):
        ## Crossover
        if  random.random() <= parameters.crossoverRate:
            actuatorPlanNew, scoreNew = crossover(populations, parameters, actuators, i, policy, models, context)
        ## Inheritance
        else:
            actuatorPlanNew = populations[i].actuatorPlan
            scoreNew = fitness(actuatorPlanNew, parameters, actuators, policy, models, context)
        ## update the actuatorPlan and score
        populationsNew[i].actuatorPlan = actuatorPlanNew
        populationsNew[i].score = scoreNew
    return populationsNew
####################################################### phase 2
def fitness(actuatorPlan, parameters, actuators, policy, models, context):
    score = 0
    for k in range(parameters.maxNumActuators):
        energyConsumption  = scaleEnergy(actuatorPlan[k],  actuators[k], policy, models, context)
        comfortImprovement = scaleComfort(actuatorPlan[k], actuators[k], policy, models, context)
        score = score + energyConsumption + comfortImprovement
    return score
    
def scaleEnergy(state, actuator, policy, models, context):
    for model in models:
        if  model.actuator_name == actuator.name:
            if  actuator.upperPower - actuator.lowerPower != 0:
                penalty = abs(state - model.power_best) / (actuator.upperPower - actuator.lowerPower)
            else:
                penalty = abs(state - model.power_best) / actuator.upperPower
            #penalty *= 1-policy.energy_saving
            break
    return penalty
    
def scaleComfort(state, actuator, policy, models, context):
    penalty = 0
    attributes = actuator.attribute.split(",")
    ## for each attribute, such like [climate, lighting] for curtain
    for attribute in attributes:
        if attribute == 'climate':
            error = context.climate.current - (context.climate.upperStd + context.climate.lowerStd)/2
            error = (1-error/(context.climate.upperBound - context.climate.lowerBound))
            #error *= 1-policy.climate
        elif attribute == 'lighting':
            error = (context.lighting.upperStd + context.lighting.lowerStd)/2 - context.lighting.current
            error = (1-error/(context.lighting.upperBound - context.lighting.lowerBound))
            #error *= 1-policy.lighting
        elif attribute == 'ventilation':
            error = context.ventilation.current - (context.ventilation.upperStd + context.ventilation.lowerStd)/2
            error = (1-error/(context.ventilation.upperBound - context.ventilation.lowerBound))
            #error *= 1-policy.ventilation
        penalty = penalty + error
    return penalty
####################################################### phase 3
def selection(populations, solutionList, parameters):
    ## sorted from old 10 global solutions and new 50 local populations
    temp = populations + solutionList
    temp.sort(key=operator.attrgetter('score'))
    ## remain top 10 actuatorPlan
    del temp[parameters.maxSelection+1 : parameters.maxSelection+parameters.maxPopulation+1]
    ## replace the worst actuatorPlan to local population
    populations.sort(key=operator.attrgetter('score'))
    populations[parameters.maxPopulation-1] = temp[0]
    return populations, temp
####################################################### phase 4
def terminal(score, parameters):
    global localCounter
    global tempScore
    ## if the score stop over 2000 rounds
    if  localCounter == parameters.maxStandpoint:
        return True
    ## else update, or record counter
    elif score < tempScore:
        tempScore = score
        localCounter = 0
        return False
    else:
        localCounter += 1
        return False
#######################################################
def getPlan(policy, models, context, parameters, actuators, recordBest, recordAvg):
    global localCounter
    global globalCounter
    global tempScore
    localCounter  = 0
    globalCounter = 0
    tempScore = 100
    ## initialization
    populations, solutionList = inilization(parameters, actuators, policy, context)
    ## repeat generation + fitness + selection till the score stop over 1000 rounds
    while True:
        populations = generation(populations, parameters, actuators, policy, models, context)
        populations, solutionList = selection(populations, solutionList, parameters)
        ## records
        if  globalCounter < 10000:
            ## best
            recordBest[globalCounter] += tempScore
            ## average
            sum = 0
            for population in populations:
                sum += population.score
            recordAvg[globalCounter]  += sum/len(populations)
        ## terminal conditions
        if  terminal(populations[0].score, parameters):
            ## remaining records
            for i in range(globalCounter+1,10000):
                recordBest[i] = recordBest[globalCounter]
                recordAvg[i]  = recordAvg[globalCounter]
            break
        globalCounter += 1
    
    return solutionList[0], recordBest, recordAvg
    
