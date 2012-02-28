"""
Designer : Firzendragon
Date     : 02/09/2012
Version  : 3.0
Testing  : main.py serial_port
           a. motion keep printing 0, but actuator not turn of  because of postponing
           b. actuator must turn at begining
"""

import time, sys
import predefine, policy, profile, data, actuator

################## connection
cursor = predefine.databaseConnection()
#ser    = predefine.serialConnection(sys.argv[1])
ser = []
################## Variables
nowPolicy = policy.initialPolicy(cursor, room)
instructionWaitTime = 2
room = 'CSIE_R513'

while True:
    try:
        ## policy switching
        reconfigureFlag, nowPolicy = policy.readPolicy(cursor, room, nowPolicy)
        
        ## profile analysing
        if  reconfigureFlag == 1:
            nowProfile = profile.readProfile(cursor, room)
            profile.reconfiguration(ser, cursor, nowPolicy, nowProfile, instructionWaitTime)
            
        ## data collection
        data.dataCollection(ser, cursor)
        
        ## device decision making
        #actuator.remote(ser, cursor, profiles, RemoteFileName, instructionWaitTime, room)
        #actuator.actuatorDecisionMaking(ser, cursor, node_id, profiles, instructionWaitTime)
        
    except KeyboardInterrupt:
        ser.close()
        cursor.close()
        break
 