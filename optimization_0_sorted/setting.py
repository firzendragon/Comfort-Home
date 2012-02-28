def getInstructions(population, actuators, maxNumActuators):
    pass

def printSection(population, actuators, maxNumActuators):
    print '----------'
    print population.score
    for k in range(maxNumActuators):
        print population.actuatorPlan[k], actuators[k].name

        