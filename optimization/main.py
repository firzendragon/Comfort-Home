"""
Author:  Firzendragon
Date:    2012/02/24
Version: 2.4
Usage:   main.py
"""

import predefine, policy, context, GA, setting
import MySQLdb, sys, time

##################### connection and parameters
cursor     = predefine.databaseConnection()
parameters = predefine.getParameter()

while True:
    try:
        ## update: 1) number of actuators, 2) now policy
        actuators, parameters.maxNumActuators = policy.getAttributeOfActuator(cursor)
        nowPolicy = policy.getPolicy(cursor)

        ## update: 1) context, 2) models
        nowContext = context.getContext(cursor)
        nowModels  = context.getModels(cursor, nowContext)

        ## Genetic Algorithm
        population = GA.getPlan(nowPolicy, nowModels, nowContext, parameters, actuators)

        ## setting: 1) execute instruction, 2) print
        setting.getInstructions(population, actuators, parameters.maxNumActuators)
        setting.printSection(population, actuators, parameters.maxNumActuators)
        
    except KeyboardInterrupt:
        cursor.close()
