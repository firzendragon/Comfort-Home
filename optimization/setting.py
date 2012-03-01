import time

def getInstructions(population, actuators, maxNumActuators):
    pass

def printSection(population, actuators, maxNumActuators):
    print '----------'
    timeNow = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(time.time()))
    print timeNow
    print population.score, population.numOfGeneration
    for k in range(maxNumActuators):
        print population.actuatorPlan[k], actuators[k].name