"""
usage: policy.climate / policy.lighting ...
       actuators.actuator_id / actuators.actuator_name ...
"""
class Policy():
    def __init__(self, climate, lighting, ventilation, energy_saving):
        self.climate       = float(climate)/10.0
        self.lighting      = float(lighting)/10.0
        self.ventilation   = float(ventilation)/10.0
        self.energy_saving = float(energy_saving)/100.0

class Actuator():
    def __init__(self, id, name, power, attribute):
        self.id         = id                        ## id,                 ex: 0001
        self.name       = name                      ## name,               ex: air-conditioner
        self.lowerPower = self.getLowerPower(power) ## lower power,        ex: 700
        self.upperPower = self.getUpperPower(power) ## upper power,        ex: 2500
        self.attribute  = attribute                 ## list of attributes, ex: [climate, ventilation]
        
    def getLowerPower(self, actuator_power):
        return actuator_power.split("~")[0]
        
    def getUpperPower(self, actuator_power):
        powers = actuator_power.split("~")
        if len(powers) == 2:  return powers[1]
        else:                 return powers[0]
        
def getAttributeOfActuator(cursor):
    sql = ("select actuator_id, actuator_name, power, attribute from profile_actuator;")
    cursor.execute(sql)
    results = cursor.fetchall()
    return [Actuator(row[0],row[1],row[2],row[3]) for row in results], cursor.rowcount

def getPolicy(cursor):
    sql = ("select climate, lighting, ventilation, energy_saving from policy where status = 2;")
    cursor.execute(sql)
    row = cursor.fetchone()
    return Policy(row[0], row[1], row[2], row[3])
