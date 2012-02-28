"""
INPUT:    policy, context
OUTPUT:   actuatorPlans = [num1,num2,0,num4]
usage:    populations[i].actuatorPlan[k] / populations[i].score
"""
import random, operator

class ActuatorPlan(object):
    def __init__(self, actuatorPlan=None, score=None):
        self.actuatorPlan = actuatorPlan
        self.score        = score

def inilization(parameters, actuators, policy, context):
    return [ActuatorPlan([random.randint(0,1) for k in range(parameters.maxNumActuators)],0) for i in range(parameters.maxPopulation)]
####################################################### phase 1
def generation(populations, parameters):
    ## for each population
    for i in range(parameters.maxSelection+1,parameters.maxPopulation):
        ## generate crossover, mutate sample
        sample = random.sample(xrange(parameters.maxPopulation), 3) # select 3 numbers from maxPopulation
        ## generate an new actuatorPlan
        actuatorPlanNew = []
        for k in range(parameters.maxNumActuators):
            ## Mutation
            if  random.random() <= parameters.mutateRate: 
                actuatorPlanNew.append(populations[sample[2]].actuatorPlan[k])
            ## Crossover
            else:
                if  k/2 == 0:
                    actuatorPlanNew.append(populations[sample[0]].actuatorPlan[k])
                else:
                    actuatorPlanNew.append(populations[sample[1]].actuatorPlan[k])
        ## update the actuatorPlan
        populations[i].actuatorPlan = actuatorPlanNew
    return populations
####################################################### phase 2
def fitness(populations, parameters, actuators, policy, context):
    ## for each population
    for population in populations:
        ## fitness each actuatorPlan
        score = 0
        for k in range(parameters.maxNumActuators):
            energyConsumption  = scaleEnergy(population.actuatorPlan[k],  actuators[k], policy, context)
            comfortImprovement = scaleComfort(population.actuatorPlan[k], actuators[k], policy, context)
            score = score + energyConsumption + comfortImprovement
        ## update the score
        population.score = score
    return populations
    
def scaleEnergy(state, actuator, policy, context):
    if  state == 1:
        return policy.energy_saving * float(actuator.lowerPower)/1000.0
    else:
        return 0

def scaleComfort(state, actuator, policy, context):
    if  state == 1:
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
            if   attribute == 'climate' and policy.climate > penalty:         penalty = policy.climate
            elif attribute == 'lighting' and policy.lighting > penalty:       penalty = policy.lighting
            elif attribute == 'ventilation' and policy.ventilation > penalty: penalty = policy.ventilation
        return penalty
####################################################### phase 3
def selection(populations):
    populations.sort(key=operator.attrgetter('score'))
    return populations
####################################################### phase 4
def terminal(score, parameters):
    global counter
    global tempScore
    ## if the score stop over 2000 rounds
    if  counter == parameters.maxStandpoint:
        return True
    ## else update, or record counter
    elif score < tempScore:
        tempScore = score
        counter = 0
        return False
    else:
        counter += 1
        return False
#######################################################
def getPlan(policy, context, parameters, actuators):
    global counter
    global tempScore
    counter = 0
    tempScore = 100
    ## initialization
    populations = inilization(parameters, actuators, policy, context)
    ## repeat generation + fitness + selection till the score stop over 1000 rounds
    while True:
        populations = generation(populations, parameters)
        populations = fitness(populations, parameters, actuators, policy, context)
        populations = selection(populations)
        if  terminal(populations[0].score, parameters):
            break
    return populations[0]
    