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
    return getStandard(cursor, 'THI', THI)
    
def getLighting(cursor):
    L = getValue(cursor, 'light')
    return getStandard(cursor, 'light', L)

def getVentilation(cursor):
    CO2 = getValue(cursor, 'CO2')
    return getStandard(cursor, 'CO2', CO2)
#######################################################
def getContext(cursor):
    return Context(getClimate(cursor),getLighting(cursor),getVentilation(cursor))