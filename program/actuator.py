import time

def logical_xor(str1, str2):
    return bool(str1) ^ bool(str2)

def remote(ser, cursor, profiles, RemoteFileName, instructionWaitTime, room):
    sql = ("select * from remote where room = '%s' and status = 1;" % room)
    cursor.execute(sql)
    row = cursor.fetchone()
    if  row != None:
        instruction = row[2]
        ser.write(instruction)
        
        ## update instruction state
        sql = ("update remote set status = 0 where room = '%s' and status = 1;" % room)
        cursor.execute(sql)
        
        ## update manual state
        for node in profiles[0]:
            if  instruction.split(" ")[0] == node.node_id:
                for actuator in node.actuator.split(","):
                    ##former = node.manuallyState[node.actuator.split(",").index(actuator)]
                    ##node.manuallyState[node.actuator.split(",").index(actuator)] = logical_xor(former, True)
                    node.manuallyState[node.actuator.split(",").index(actuator)] = True

def getValue(cursor, node_id, sensor_name):
    sql = ("select * from data where node_id = '%s' and sensor_name = '%s' order by context_id desc limit 1;" % (node_id, sensor_name))
    cursor.execute(sql)
    row = cursor.fetchone()
    
    return row[3]

def operator(stack, index, condition):
    if condition == 'NOT':
        temp1 = stack.pop()
        stack.append(not temp1)
        
    elif condition == 'OR':
        temp1 = stack.pop()
        temp2 = stack.pop()
        stack.append(temp1 or temp2)
        
    elif condition == 'AND':
        temp1 = stack.pop()
        temp2 = stack.pop()
        stack.append(temp1 and temp2)
    
    return stack

def operand(cursor, stack, index, condition, profiles, node, actuatorNum):
    ## find if the value > or < threshold
    for chromosome in profiles[1]:
        if  condition == 'motion' and condition == chromosome.sensor_name:
            value = getValue(cursor, node.node_id, condition)
            value = motionPostponing(value, chromosome.threshold, node, actuatorNum)  ## motion postpone
            if   int(value) == 1: stack.append(True)
            elif int(value) == 0: stack.append(False)
            index += 1
            break
        elif condition != 'motion' and condition == chromosome.sensor_name:
            value = getValue(cursor, node.node_id, condition)
            if   float(value) >  float(chromosome.threshold): stack.append(True)
            elif float(value) <= float(chromosome.threshold): stack.append(False)
            index += 1
            break
            
    return stack, index
    
def conditionDecision (cursor, profiles, conditions, node, actuatorNum):
    index = 0    ## logic expression index
    stack = []   ## logic expression list
    
    for condition in conditions:
        if  condition == 'NOT' or condition == 'OR' or condition == 'AND':
            stack = operator(stack, index, condition)
        else:
            stack, index = operand(cursor, stack, index, condition, profiles, node, actuatorNum)
    
    turn_on = stack.pop()
    return turn_on

def actuatorDecisionMaking(ser, cursor, node_id, profiles, instructionWaitTime):
    instructions = []
    for node in profiles[0]:  ## for each node
        if  node_id == node.node_id:
            ## for each actuator on the node
            actuatorNum = len(node.actuator.split(","))
            for actuator in node.actuator.split(","):
                actuatorName  = actuator.split(":")[0]
                actuatorPin   = actuator.split(":")[1].split(".")[1]
                actuatorState = node.actuatorState[node.actuator.split(",").index(actuator)]
                manuallyState = node.manuallyState[node.actuator.split(",").index(actuator)]
                
                ## find relation for each actuator
                for relation in profiles[2]:
                    if actuatorName == relation.actuator_name:
                        ## parse the logic expression
                        conditions = relation.conditions.split(" ")
                        turn_on = conditionDecision(cursor, profiles, conditions, node, actuatorNum)
                        
                        if  manuallyState == False:
                            ## find the latest instruction state
                            sql = ("select * from instruction where instruction like '%%%s write %s%%' order by context_id desc limit 1;" % (node.node_id, actuatorPin))
                            cursor.execute(sql)
                            row = cursor.fetchone()
                            if  row != None:
                                instructionState = int(row[1].split(" ")[2].split(":")[1])
                            else:
                                instructionState = 0
                            
                            if  actuatorState == False and turn_on == True and instructionState == 0:
                                instructions.append('%s write %s:1 ' % (node.node_id, actuatorPin))
                                actuatorState = True
                                break
                            elif instructionState == 1 and turn_on == False:
                                instructions.append('%s write %s:0 ' % (node.node_id, actuatorPin))
                                actuatorState = False
                                break
                        
                node.actuatorState[node.actuator.split(",").index(actuator)] = actuatorState
            break  ## one data mapping to one node
        
    ## execute instructions
    actuatorWrite(ser, cursor, instructions, instructionWaitTime)
    
def actuatorWrite(ser, cursor, instructions, instructionWaitTime):
    for instruction in instructions:
        print instruction
        sql = "INSERT INTO instruction (instruction) VALUES('%s')" % (instruction)
        cursor.execute(sql)
        ser.write(instruction)
        time.sleep(instructionWaitTime)
    
def motionPostponing(motionValue, motionThreshold, node, actuatorNum):
    ## motion counter is too large. multiply actuator number because of the repeat computation
    if  node.motionCounter == 0 or node.motionCounter >= 99999:
        node.motionCounter = motionThreshold * actuatorNum

    ## motion counter restart when get motion value
    if  int(motionValue) == 1:
        node.motionCounter = 1
    ## motion value = 0 when counter over  threshold
    ## motion value = 1 when counter under threshold
    elif int(motionValue) == 0:
        if node.motionCounter >= motionThreshold * actuatorNum: motionValue = 0
        else:                                                   motionValue = 1

    node.motionCounter += 1
    return motionValue

