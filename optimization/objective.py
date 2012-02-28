"""
Author:  Firzendragon
Date:    2011/12/30
Version: 1.3
Usage:   objective.py climate lighting loudness energy
Ex:      objective.py 5 5 5 80
"""

import MySQLdb, random, sys, time
from operator import itemgetter

class Policy():
    def __init__(self, climate, lighting, loudness, energy):
        self.climate  = climate
        self.lighting = lighting
        self.loudness = loudness
        self.energy   = energy

class Node():
    def __init__(self, sensor_name, distribution):
        self.node_id      = sensor_name
        self.distribution = distribution

class Chromosome():
    def __init__(self, sensor_name, value, attribute, weight):
        self.sensor_name = sensor_name
        self.value       = [value for i in range(0,population)]
        self.attribute   = attribute
        self.weight      = weight


def getThreshold(sensorName, attribute):
    # get the threshold of specified data, such like temperature, humidity, light, etc.
    sql = ("select * from optimization_standard where sensor_name = '%s';" % sensorName)
    cursor.execute(sql)
    standard = cursor.fetchone()

    if  standard[5] == 'moderate' or standard[5] == 'lower':
        threshold = float(standard[2]) + (float(standard[3]) - float(standard[2])) * (1 - attribute/10.0)
    elif standard[5] == 'higher':
        threshold = float(standard[2]) + (float(standard[3]) - float(standard[2])) * (attribute/10.0)

    return threshold

def inilization(population, policy, loopCounterMax):
    # initialize the chromosome and nodes data
    alpha = policy.climate  / loopCounterMax
    beta  = policy.lighting / loopCounterMax
    gamma = policy.loudness / loopCounterMax
    tempThreshold  = getThreshold('temperature', policy.climate) 
    humiThreshold  = getThreshold('humidity',    policy.climate) 
    lightThreshold = getThreshold('light',       policy.lighting) 
    nodes       = [Node('0001',8), Node('0002',2)]
    chromosomes = [Chromosome('temperature', tempThreshold,  'climate',  alpha), \
                   Chromosome('humidity'   , humiThreshold,  'climate',  alpha), \
                   Chromosome('light'      , lightThreshold, 'lighting', beta)]
    
    return nodes, chromosomes

def scale(lowerBound, upperBound, value):
    # map the data range into [0,100]
    return ((float(value) - float(lowerBound)) * 100.0) / (float(upperBound) - float(lowerBound))

def getError(value, sensorName):
    # get the error of specified data, such like temperature, humidity, light, etc.
    sql = ("select * from optimization_standard where sensor_name = '%s';" % sensorName)
    cursor.execute(sql)
    standard = cursor.fetchone()
    
    if  standard[5] == 'moderate':
        value = abs(scale(standard[2],standard[3],value) - scale(standard[2],standard[3],standard[4]))
    elif standard[5] == 'lower':
        value = abs(scale(standard[2],standard[3],value) - scale(standard[2],standard[3],standard[4]))
    elif standard[5] == 'higher':
        value = abs(scale(standard[2],standard[3],standard[4]) - scale(standard[2],standard[3],value))
    
    return value

def generation(chromosomes, population):
    # generate populations by drop value for each data
    for chromosome in chromosomes:
        sql = ("select * from optimization_standard where sensor_name = '%s';" % chromosome.sensor_name)
        cursor.execute(sql)
        standard = cursor.fetchone()
    
        for i in range(1,population):
            randomValue = random.gauss(0, 0.25) * float(standard[6])
            chromosome.value[i] += randomValue
            
def mutation(chromosomes, population):
    pass
    
def evaluation(nodes, chromosomes, population, fitnessMin):
    # evaluate the populations
    fitness = [0 for i in range(0,population)]
    for node in nodes:
        for chromosome in chromosomes:
            for i in range(0,population):
                value = chromosome.weight * getError(chromosome.value[i], chromosome.sensor_name)
                fitness[i] = fitness[i] + value

    return fitness

def selection(chromosomes, population, fitness, fitnessMin):
    # select the best population, and put in the first class : chromosome[i].value[0]
    index = 0
    for i in range(0,population):
        if  fitness[i] <= fitnessMin:
            fitnessMin = fitness[i]
            index      = i
            
    for chromosome in chromosomes:
        for i in range(0,population):
            chromosome.value[i] = chromosome.value[index]
        
    return fitnessMin

def calculateOptimization():
    # loop for fixed times
    fitnessMin  = 10000
    loopCounter = 0
    while True:
        if  loopCounter >= loopCounterMax:
            #print "fitness", fitnessMin
            for chromosome in chromosomes:
                print chromosome.sensor_name, chromosome.value[0]
            break
        
        generation(chromosomes, population-1)
        mutation(chromosomes, population)
        fitness    = evaluation(nodes, chromosomes, population, fitnessMin)
        fitnessMin = selection(chromosomes, population, fitness, fitnessMin)
        loopCounter += 1

def getValue(sensorName):
    # get current data
    sql = ("select * from data where node_id = '0001' and sensor_name = '%s' order by context_id desc limit 1;" % sensorName)
    cursor.execute(sql)
    row = cursor.fetchone()

    return float(row[3])

def actuatorStrategy(chromosomes, sensor_name):
    # know if the actuator should turn on/off : available[i]
    shift = 0
    sql = ("select * from optimization_standard where sensor_name = '%s';" % sensor_name)
    cursor.execute(sql)
    standard = cursor.fetchone()

    value = getValue(sensor_name)

    avail = 0
    for i in range(0,len(chromosomes)):
        if  sensor_name == chromosomes[i].sensor_name:
            if  standard[5] == 'moderate' or standard[5] == 'lower':
                if  value + shift >= chromosomes[i].value[0]:
                    avail = 1
            elif standard[5] == 'higher':
                if  value < chromosomes[i].value[0]:
                    avail = 1

            break

    return avail

def getPower(chromosomes, sensor_name, lowPower, highPower):
    shift = 0
    # calculate the power consumption of each actuator
    sql = ("select * from policy_attribute where sensor_name = '%s';" % sensor_name)
    cursor.execute(sql)
    row = cursor.fetchone()

    lowThreshold  = float(row[3].split("~")[0])
    highThreshold = float(row[3].split("~")[1])

    cost = 0
    for i in range(0,len(chromosomes)):
        if  sensor_name == chromosomes[i].sensor_name:
            cost = highPower - (chromosomes[i].value[0]+10 - lowThreshold) * (highPower - lowPower) / (highThreshold - lowThreshold)
            if  cost < lowPower:
                cost = lowPower
            break

    return cost

def energyStrategy(chromosomes):
    totalPower = 0
    profilePower = []

    sql = ("select * from profile_node where location = 'CSIE_R513'")
    cursor.execute(sql)
    results = cursor.fetchall()
    for result in results: # for each actuator, get the power range
        actuators = result[6].split(",")
        for actuator in actuators:
            actuatorName = actuator.split(":")[0]
            actuatorPin  = actuator.split(":")[1].split(".")[1]

            # get power
            sql = ("select * from profile_actuator where actuator_name = '%s'" % actuatorName)
            cursor.execute(sql)
            row = cursor.fetchone()
            tempPower = row[3].split("~")
            if  len(tempPower) == 2:
                lowPower  = float(tempPower[0])
                highPower = float(tempPower[1])
            else:
                lowPower  = float(tempPower[0])
                highPower = float(tempPower[0])

            sql = ("select * from profile_relation where actuator_name = '%s';" % row[1]) # get the first sensor_name of actuator relation
            cursor.execute(sql)
            sensor = cursor.fetchone()
            ############## Have to deal with the relation ##############
            sensor_name = sensor[2].split(" ")[0]
            

            # calculate cost and available, ex: 40W vs 1/0
            cost  = getPower(chromosomes, sensor_name, lowPower, highPower)
            avail = actuatorStrategy(chromosomes, sensor_name)

            # put all the information
            if  avail == 1:
                profilePower.append([row[1], float(tempPower[0]), cost, 1, '%s write %s:1 ' % (result[1], actuatorPin)])

    for i in range(len(profilePower)): # calculate the total power in this policy setting
        totalPower = totalPower + profilePower[i][2] * profilePower[i][3]

    print 'totalPower', totalPower
    return totalPower, profilePower

def sortandprint(listPower, profilePower, instructions):
    # sort the power list
    finalPower  = sorted(listPower, key=itemgetter(0), reverse=True)
    instructionTemps = []

    if  finalPower != []:
        for index in finalPower[0][1]:
            profilePower[index][3] = 0

        # print section
        print 'The finalPower is :', finalPower[0][0]
        print 'The actuator decision is :\n'
    
        for i in range(len(profilePower)):
            if  profilePower[i][3] == 1:
                print 'Actuator Name :', profilePower[i][0]
                print 'Power Consuming :', profilePower[i][2]
                print 'Instruction :', profilePower[i][4]
                instructionTemps.append(profilePower[i][4])
        
        # clear
        for instruction in instructions:
            if  instruction not in instructionTemps:
                instruction = instruction.split(":")[0]+':0 '
                sql = ("update remote set status = 1 where instruction = '%s'" % instruction)
                cursor.execute(sql)
                sql = ("insert into instruction (instruction) values ('%s')" % instruction)
                cursor.execute(sql)
        # update
        for instructionTemp in instructionTemps:
            if  instructionTemp not in instructions:
                sql = ("update remote set status = 1 where instruction = '%s'" % instructionTemp)
                cursor.execute(sql)
                sql = ("insert into instruction (instruction) values ('%s')" % instructionTemp)
                cursor.execute(sql)
    else:
        print 'There is no actuator can be turned on.'
        for instruction in instructions:
            if  instruction not in instructionTemps:
                instruction = instruction.split(":")[0]+':0 '
                sql = ("update remote set status = 1 where instruction = '%s'" % instruction)
                cursor.execute(sql)
                sql = ("insert into instruction (instruction) values ('%s')" % instruction)
                cursor.execute(sql)

    return instructionTemps

def secondcheck(totalPower, profilePower, savePower):
    # second check to number
    finalPower = 0
    for i in range(len(profilePower)): # calculate the total power in this policy setting
        finalPower = finalPower + profilePower[i][2] * profilePower[i][3]

    # evolutionary computation
    listPower = []
    for loop in range(0,10000):
        # generation : random find one to cut down
        number = random.randint(0,len(profilePower)-1)
        indexList = [random.randint(0,len(profilePower)-1) for i in range(0,number)]
        for index in indexList:
            profilePower[index][3] = 0

        # evaluation
        finalPower = 0
        for i in range(len(profilePower)): # calculate the total power in this policy setting
            finalPower += profilePower[i][2] * profilePower[i][3]
        # selection
        if  totalPower >= finalPower + savePower:
            listPower.append([finalPower,indexList])

        # recovery
        for index in indexList:
            profilePower[index][3] = 1

    return listPower

def firstcheck(totalPower, profilePower, savePower):
    reduceIndex  = [(i, profilePower[i][2]-profilePower[i][1]) for i in range(len(profilePower)) if profilePower[i][2] > profilePower[i][1]]
    reduceIndex  = sorted(reduceIndex,  key=itemgetter(1), reverse=True)

    # first check to reduce power
    countPower = 0
    for i in range(len(reduceIndex)):
        if  reduceIndex[i][1] > savePower:  # if first one power > savePower
            profilePower[reduceIndex[i][0]][2] -= savePower
            break
        elif countPower + reduceIndex[i][1] > savePower:  # if totalPower > savePower
            profilePower[reduceIndex[i][0]][2] -= savePower - countPower
            break

        profilePower[reduceIndex[i][0]][2] = profilePower[reduceIndex[i][0]][1]
        countPower = countPower + reduceIndex[i][1]

    return profilePower

def energyConstraint(totalPower, profilePower, policy, instructions):
    # calculate how many power should be cut down
    savePower = totalPower * policy.energy / 100.0
    print 'You want to savePower :', savePower

    profilePower = firstcheck(totalPower, profilePower, savePower)
    listPower    = secondcheck(totalPower, profilePower, savePower)
    
    return sortandprint(listPower, profilePower, instructions)

def getPolicy():
    sql = ("select * from nowPolicy;")
    cursor.execute(sql)
    row = cursor.fetchone()
    policy_name = row[2]

    sql = ("select * from policy where policy_name = '%s';" % policy_name)
    cursor.execute(sql)
    row = cursor.fetchone()
    policy = Policy(float(row[2]), float(row[3]), float(row[4]), float(row[5]))

    return policy

DBHost   = 'gardenia.csie.ntu.edu.tw'
DBUser   = 'smartpower'
DBPasswd = 'smartpower#336'
DBName   = 'smartpower2'
db = MySQLdb.connect(host = DBHost,user = DBUser, passwd = DBPasswd, db = DBName)
cursor = db.cursor()
#####################
instructions = []
while True:
    policy = getPolicy()
    loopCounterMax = (policy.climate + policy.lighting + policy.loudness)
    population = 20
    #####################
    nodes, chromosomes = inilization(population, policy, loopCounterMax)
    print "\n1. Optimal Goal:\n"
    calculateOptimization()
    print "\n2. Total Power:\n"
    totalPower, profilePower = energyStrategy(chromosomes)
    print "\n3. Energy Constraint:\n"
    instructions = energyConstraint(totalPower, profilePower, policy, instructions)
    print "\n"

    #time.sleep(10)
#####################
cursor.close()
db.close()
