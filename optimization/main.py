"""
Author:  Firzendragon
Date:    2012/03/02
Version: 2.7
Usage:   main.py
"""
import predefine, policy, context, GA, setting
import MySQLdb, sys, time

def recordWrite(document, name, recordBest, recordAvg, recordCounter):
    f = file('%s/fitness_generation_best_%s.dat' % (document, name),'w')
    for i in range(1,10000):
        f.write('%s %s\n' % (i, recordBest[i]/recordCounter))
    f.close()
    ## average records
    f = file('%s/fitness_generation_avg_%s.dat' % (document, name),'w')
    for i in range(1,10000):
        f.write('%s %s\n' % (i, recordAvg[i]/recordCounter))
    f.close()

def mainWhile(cursor, parameters, recordBest, recordAvg, recordCounter):
    while True:
        try:
            if  recordCounter <= 0:
                break
            else:
                recordCounter -= 1
                
            ## update: 1) number of actuators, 2) now policy
            actuators, parameters.maxNumActuators = policy.getAttributeOfActuator(cursor)
            nowPolicy = policy.getPolicy(cursor)

            ## update: 1) context, 2) models
            nowContext = context.getContext(cursor)
            nowModels  = context.getModels(cursor, nowContext)

            ## Genetic Algorithm
            population, recordBest, recordAvg = GA.getPlan(nowPolicy, nowModels, nowContext, parameters, actuators, recordBest, recordAvg)

            ## setting: 1) execute instruction, 2) print
            setting.getInstructions(population, actuators, parameters.maxNumActuators)
            setting.printSection(population, actuators, parameters.maxNumActuators)
            
        except KeyboardInterrupt:
            cursor.close()
    return recordBest, recordAvg

##################### connection and parameters
cursor     = predefine.databaseConnection()
parameters = predefine.getParameter()

recordBest = [0 for j in range(10000)]
recordAvg  = [0 for j in range(10000)]
recordCounter = 10

## controlled variables
key  = sys.argv[1]

if  key != 'base':
    num  = sys.argv[2]
    name = sys.argv[3]
    if key == 'population':
        parameters.maxPopulation = int(num)
        parameters.shiftRate = 0.0
        parameters.maskRate  = 0.0
    elif key == 'crossover':
        parameters.crossoverRate = float(num)
        parameters.shiftRate = 0.0
        parameters.maskRate  = 0.0
    elif key == 'shift':
        parameters.shiftRate = float(num)
        parameters.crossoverRate = 0.0
    elif key == 'mask':
        parameters.maskRate = float(num)
        parameters.crossoverRate = 0.0
else:
    name = key
    
## run test and record
recordBest, recordAvg = mainWhile(cursor, parameters, recordBest, recordAvg, recordCounter)
recordWrite(key, name, recordBest, recordAvg, recordCounter)