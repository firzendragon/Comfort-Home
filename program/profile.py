import time

class Profile():
    def __init__(self, nodes, sensors, actuators):
        self.nodes     = nodes
        self.sensors   = sensors
        self.actuators = actuators
    
class Node():
    def __init__(self, node_id, room, sensor, actuator):
        self.node_id  = node_id
        self.room     = room
        self.sensor   = sensor
        self.actuator = actuator
        #self.motionCounter = 0
        #self.actuatorState = [False for i in range(len(self.actuator.split(",")))]
        #self.manuallyState = [False for i in range(len(self.actuator.split(",")))]
        
class Sensor():
    def __init__(self, sensor_name, sample_period, threshold, attribute):
        self.sensor_name   = sensor_name
        self.sample_period = sample_period
        self.threshold     = threshold
        self.attribute     = attribute

class Actuator():
    def __init__(self, actuator_name, conditions):
        self.actuator_name = actuator_name
        self.conditions    = conditions  ## string of logic expression

########################## read profile    

def getNode(cursor, room):
    sql = ("select node_id, room, sensor, actuator from profile_node;")
    cursor.execute(sql)
    results = cursor.fetchall()
    nodes = [Node(row[0], row[1], row[2], row[3]) for row in results]
    return nodes

def getSensor(cursor):
    sql = ("select sensor_name, sample_period, threshold, attribute from policy_attribute;")
    cursor.execute(sql)
    results = cursor.fetchall()
    sensors = [Sensor(row[0], row[1], row[2], row[3]) for row in results]
    return sensors

def getActuator(cursor):
    sql = ("select actuator_name, conditions from policy_relation;")
    cursor.execute(sql)
    results = cursor.fetchall()
    actuators = [Actuator(row[0], row[1]) for row in results]
    return actuators

def readProfile(cursor, room):
    return Profile(getNode(cursor, room), getSensor(cursor), getActuator(cursor))

########################## reconfiguration    
    
def reconfiguration(ser, cursor, policy, profile, instructionWaitTime):
    ## generate complete instructions and reconfigure: READ, WRITE, EXCHANGE
    instructions = searching(cursor, policy, profile)
    settingWrite(ser, cursor, instructions, instructionWaitTime)
    
def initialData(cursor, node_id, sensor_name, value):
    ## log the threshold as first data into 
    timeNow = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(time.time()))
    sql = "insert into data (node_id,sensor_name,value,timestamp) VALUES('%s','%s','%s','%s')" % (node_id,sensor_name,value,timeNow)
    cursor.execute(sql)

def getSensorName(cursor, sensorID):
    sql = ("select sensor_name from profile_sensor where sensor_id = '%s';" % sensorID)
    cursor.execute(sql)
    row = cursor.fetchone()
    return row[0]

def getSamplePeriod(policy, lowerSample, upperSample, attribute):
    if  attribute == 'climate':
        samplePeriod = lowerSample + (1-policy.climate) * (upperSample - lowerSample)
    elif  attribute == 'lighting':
        samplePeriod = lowerSample + (1-policy.lighting) * (upperSample - lowerSample)
    elif  attribute == 'ventilation':
        samplePeriod = lowerSample + (1-policy.ventilation) * (upperSample - lowerSample)
    elif  attribute == 'activity_level':
        if  policy.activity_level == 'rapid':
            samplePeriod = 500
        elif policy.activity_level == 'slow':
            samplePeriod = 1000
    return samplePeriod
    
def getThreshold(policy, lowerThreshold, upperThreshold, attribute):
    if  attribute == 'climate':
        threshold = lowerThreshold + (1-policy.climate) * (upperThreshold - lowerThreshold)
    elif  attribute == 'lighting':
        threshold = lowerThreshold + policy.lighting * (upperThreshold - lowerThreshold)
    elif  attribute == 'ventilation':
        threshold = lowerThreshold + (1-policy.ventilation) * (upperThreshold - lowerThreshold)
    elif  attribute == 'activity_level':
        threshold = 0
    return threshold
    
def searching(cursor, policy, profile):
    instructions = []
    for node in profile.nodes:
        for sensorOfNode in node.sensor.split(","):
            sensorID   = sensorOfNode.split(":")[0]
            sensorName = getSensorName(cursor, sensorID)
            sensorType = sensorOfNode.split(":")[1].split(".")[0]
            sensorPin  = sensorOfNode.split(":")[1].split(".")[1]
            
            ## reconfigure sample period
            for sensor in profile.sensors:
                if sensorName == sensor.sensor_name:
                    ## get sample period
                    lowerSample = float(sensor.sample_period.split("~")[0])
                    upperSample = float(sensor.sample_period.split("~")[1])
                    samplePeriod = getSamplePeriod(policy, lowerSample, upperSample, sensor.attribute)
                    ## get threshold
                    lowerThreshold = float(sensor.threshold.split("~")[0])
                    upperThreshold = float(sensor.threshold.split("~")[1])
                    threshold = getThreshold(policy, lowerThreshold, upperThreshold, sensor.attribute)
                    
                    ## put
                    instructions.append('%s read %s:%s:%s ' % (node.node_id, sensorName, sensorPin, samplePeriod))
                    initialData(cursor, node.node_id, sensorName, threshold)
    return instructions

def settingWrite(ser, cursor, instructions, instructionWaitTime):
    for instruction in instructions:
        print instruction
        ## log
        sql = "insert into log_instruction (instruction) values('%s')" % (instruction)
        cursor.execute(sql)
        ## reconfigure
        #ser.write(instruction)
        ## wait
        #time.sleep(instructionWaitTime)
