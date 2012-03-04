"""
usage: context.climate.current        / context.climate.attribute
       context.lighting.lowerStd      / context.lighting.upperStd
       context.ventilation.lowerBound / context.ventilation.upperBound
"""
import math

class Context():
    def __init__(self, climate, lighting, ventilation):
        self.climate     = climate
        self.lighting    = lighting
        self.ventilation = ventilation
        
class Content():
    def __init__(self, current, lowerStd, upperStd, lowerBound, upperBound, attribute):
        self.current    = float(current)    ## current index,                 ex: THI = 18.5
        self.lowerStd   = float(lowerStd)   ## standard lower bound,          ex: THI in [16,26]
        self.upperStd   = float(upperStd)   ## standard upper bound,          ex: THI in [16,26]
        self.lowerBound = float(lowerBound) ## index lower bound (for scale), ex: THI in [0, 40]
        self.upperBound = float(upperBound) ## index upper bound (for scale), ex: THI in [0, 40]
        self.attribute  = attribute         ## THI -> climate, light -> lighting, CO2 -> ventilation
        
class Models():
    def __init__(self, degree_days, power_best, actuator_name, base, slope, intercept):
        self.degree_days   = degree_days
        self.power_best    = power_best
        self.actuator_name = actuator_name
        self.base          = base
        self.slope         = slope
        self.intercept     = intercept

def getPowerBest(context, degree_days, endpoint, slope, attribute):
    ## if over the max actuator power
    if  (attribute  == 'climate'    and context.climate.current     > endpoint) or \
        (attribute == 'lighting'    and context.lighting.current    < endpoint) or \
        (attribute == 'ventilation' and context.ventilation.current > endpoint):
        return slope * endpoint
    ## else, find the mapping actuator power
    else:
        return slope * degree_days

def getDegreeDays(context, base, attribute):
    ## get the current degree days data
    if  attribute  == 'climate':      return context.climate.current - base
    elif attribute == 'lighting':     return base - context.lighting.current
    elif attribute == 'ventilation':  return context.ventilation.current - base

def getModels(cursor, context):
    sql = ("select actuator_name, base, endpoint, slope, intercept, attribute from policy_models;")
    cursor.execute(sql)
    results = cursor.fetchall()
    ## models reading
    models = []
    for row in results:
        degree_days = getDegreeDays(context, row[1], row[5])
        power_best  = getPowerBest(context, degree_days, row[2], row[3], row[5])
        ## new model for each actuator
        models.append(Models(degree_days, power_best, row[0], row[1], row[3], row[4]))
    return models
#######################################################
def getStandard(cursor, standardName, current):
    sql = ("select lowerBound, upperBound, standard, attribute from policy_optimization where standard_name = '%s';" % standardName)
    cursor.execute(sql)
    row = cursor.fetchone()
    lowerStd = row[2].split("~")[0]
    upperStd = row[2].split("~")[1]
    return Content(current, lowerStd, upperStd, row[0], row[1], row[3])
#######################################################
def getValue(cursor, sensorName):
    sql = ("select value from data where node_id = '0001' and sensor_name = '%s' order by context_id desc limit 1;" % sensorName)
    cursor.execute(sql)
    row = cursor.fetchone()
    return float(row[0])
#######################################################
def getExp(element):
    return math.exp((17.269 * element)/(element + 237.30))

def getTHI(T, H):
    Td = (T * H)/100
    THI = T - 0.55 * (1 - getExp(Td)/getExp(T)) * (T-14)
    return THI
    
def getClimate(cursor):
    T = getValue(cursor, 'temperature')
    H = getValue(cursor, 'humidity')
    THI = getTHI(T, H)
    THI = 14.2
    return getStandard(cursor, 'THI', THI)
    
def getLighting(cursor):
    L = getValue(cursor, 'light')
    L = 50.0
    return getStandard(cursor, 'light', L)

def getVentilation(cursor):
    CO2 = getValue(cursor, 'CO2')
    CO2 = 1000.0
    return getStandard(cursor, 'CO2', CO2)
#######################################################
def getContext(cursor):
    return Context(getClimate(cursor),getLighting(cursor),getVentilation(cursor))
