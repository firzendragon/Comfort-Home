class Policy():
    def __init__(self, climate, lighting, ventilation, energy_saving, activity_level):
        self.climate        = float(climate)/10.0
        self.lighting       = float(lighting)/10.0
        self.ventilation    = float(ventilation)/10.0
        self.energy_saving  = float(energy_saving)
        self.activity_level = activity_level

def initialPolicy(cursor, room):
    sql = ("select climate, lighting, ventilation, energy_saving, activity_level,status from policy where room = '%s' and status = 2;" % room)
    cursor.execute(sql)
    row = cursor.fetchone()
    return Policy(row[0], row[1], row[2], row[3], row[4])

def readPolicy(cursor, room, nowPolicy):
    sql = ("select climate, lighting, ventilation, energy_saving, activity_level,status from policy where room = '%s' and status = 1;" % room)
    cursor.execute(sql)
    if  cursor.rowcount != 0:
        row = cursor.fetchone()
        if  row[5] == 1:
            ## flush old policy
            sql = ("update policy set status = 0 where room = '%s' and status = 2;" % room)
            cursor.execute(sql)
            ## update new policy
            sql = ("update policy set status = 2 where room = '%s' and status = 1;" % room)
            cursor.execute(sql)
        
        return 1, Policy(row[0], row[1], row[2], row[3], row[4])
    else:
        return 0, nowPolicy