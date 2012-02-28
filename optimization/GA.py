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
def mutation(actuatorPlan, actuator):
    actuatorPlan += random.gauss(0, 0.25) * (actuator.upperPower - actuator.lowerPower) / 5
    if  actuatorPlan > actuator.upperPower:
        return actuator.upperPower
    elif actuatorPlan < actuator.lowerPower:
        return actuator.lowerPower
    else:
        return actuatorPlan
    
def generation(populations, parameters, actuators):
    global globalCounter
    ## temp populations to avoid replace the older populations
    populationsNew = [ActuatorPlan([None for k in range(parameters.maxNumActuators)],None,globalCounter) for i in range(parameters.maxPopulation)]
    ## for each population
    for i in range(parameters.maxPopulation):
        ## generate crossover, mutate sample
        sample = random.sample(xrange(parameters.maxPopulation), 3) # select 3 numbers from maxPopulation
        ## generate an new actuatorPlan
        actuatorPlanNew = []
        ## Crossover
        if  random.random() <= parameters.crossoverRate:
            for k in range(parameters.maxNumActuators):
                if  k/2 == 0:
                    actuatorPlanNew.append(populations[i].actuatorPlan[k])
                else:
                    actuatorPlanNew.append(populations[sample[1]].actuatorPlan[k])
                ## Mutation 1 : drop
                if  random.random() <= parameters.mutateRate:
                    actuatorPlanNew[k] = mutation(actuatorPlanNew[k], actuators[k])
                ## Mutation 2 : switch
                if  random.random() <= parameters.mutateRate:
                    actuatorPlanNew[k] = 0.0
            ## update the actuatorPlan
            populationsNew[i].actuatorPlan = actuatorPlanNew
        ## Inheritance
        else:
            populationsNew[i].actuatorPlan = populations[i].actuatorPlan
    return populationsNew
####################################################### phase 2
def fitness(populations, parameters, actuators, policy, models, context):
    ## for each population
    for population in populations:
        ## fitness each actuatorPlan
        score = 0
        for k in range(parameters.maxNumActuators):
            energyConsumption  = scaleEnergy(population.actuatorPlan[k],  actuators[k], policy, models, context)
            comfortImprovement = scaleComfort(population.actuatorPlan[k], actuators[k], policy, models, context)
            score = score + energyConsumption + comfortImprovement
        ## update the score and generation
        population.score = score
    return populations
    
def scaleEnergy(state, actuator, policy, models, context):
    for model in models:
        if  model.actuator_name == actuator.name:
            if  actuator.upperPower - actuator.lowerPower != 0:  penalty = abs(state - model.power_best) / (actuator.upperPower - actuator.lowerPower)
            else:                                                penalty = abs(state - model.power_best) / actuator.upperPower
            break
    return penalty
    """
    if  state != 0:
        return policy.energy_saving * float(actuator.lowerPower)/1000.0
    else:
        return 0
    """

def scaleComfort(state, actuator, policy, models, context):
    penalty = 0
    attributes = actuator.attribute.split(",")
    ## for each attribute such like [climate, lighting] for curtain
    for attribute in attributes:
        if attribute == 'climate':
            error = context.climate.current - (context.climate.upperStd + context.climate.lowerStd)/2
            error = (1-error/(context.climate.upperBound - context.climate.lowerBound))
        elif attribute == 'lighting':
            error = context.lighting.current - (context.lighting.upperStd + context.lighting.lowerStd)/2
            error = (1-abs(error)/(context.lighting.upperBound - context.lighting.lowerBound))
        elif attribute == 'ventilation':
            error = context.ventilation.current - (context.ventilation.upperStd + context.ventilation.lowerStd)/2
            error = (1-error/(context.ventilation.upperBound - context.ventilation.lowerBound))
        penalty = penalty + error
    return penalty
    """
    if  state != 0:
        penalty = 0
        attributes = actuator.attribute.split(",")
        ## for each attribute such like [climate, lighting] for curtain
        for attribute in attributes:
            if attribute == 'climate':
                error = context.climate.current - (context.climate.upperStd + context.climate.lowerStd)/2
                error = policy.climate * (1-error/(context.climate.upperBound - context.climate.lowerBound))
            elif attribute == 'lighting':
                error = context.lighting.current - (context.lighting.upperStd + context.lighting.lowerStd)/2
                error = policy.lighting * (1-abs(error)/(context.lighting.upperBound - context.lighting.lowerBound))
            elif attribute == 'ventilation':
                error = context.ventilation.current - (context.ventilation.upperStd + context.ventilation.lowerStd)/2
                error = policy.ventilation * (1-error/(context.ventilation.upperBound - context.ventilation.lowerBound))
            penalty = penalty + error
        return penalty / len(attributes) ## error / range
    else:
        penalty = 0
        attributes = actuator.attribute.split(",")
        for attribute in attributes:
            if   attribute == 'climate'     and policy.climate     > penalty: penalty = policy.climate
            elif attribute == 'lighting'    and policy.lighting    > penalty: penalty = policy.lighting
            elif attribute == 'ventilation' and policy.ventilation > penalty: penalty = policy.ventilation
        return penalty
    """
####################################################### phase 3
def selection(populations, solutionList, parameters):
    ## sorted
    temp = populations + solutionList
    temp.sort(key=operator.attrgetter('score'))
    ## get the top 10 actuatorPlan
    del temp[parameters.maxSelection+1:parameters.maxSelection+parameters.maxPopulation+1]
    ## replace the worst actuatorPlan
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
def getPlan(policy, models, context, parameters, actuators):
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
        populations = generation(populations, parameters, actuators)
        populations = fitness(populations, parameters, actuators, policy, models, context)
        populations, solutionList = selection(populations, solutionList, parameters)
        if  terminal(populations[0].score, parameters):
            break
        globalCounter += 1
    return solutionList[0]
    