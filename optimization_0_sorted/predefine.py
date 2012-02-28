"""
usage: parameters.maxTime / parameters.maxStandpoint ...
"""
import MySQLdb

class Parameter():
    def __init__(self, maxTime, maxStandpoint, maxPopulation, mutateRate, maxNumActuators, maxSelection):
        self.maxTime         = maxTime          ## computation time   < 10s
        self.maxStandpoint   = maxStandpoint    ## computation rounds < 2000
        self.maxPopulation   = maxPopulation    ## population size    = 50
        self.mutateRate      = mutateRate       ## mutation rate      = 0.2, crossover rate = 0.8
        self.maxNumActuators = maxNumActuators  ## number of actuator
        self.maxSelection    = maxSelection     ## number of actuator plans will be saved

def databaseConnection():
    DBHost   = 'gardenia.csie.ntu.edu.tw'
    DBUser   = 'firzendragon'
    DBPasswd = 'dragon#336'
    DBName   = 'smartpower2'
    db = MySQLdb.connect(host = DBHost,user = DBUser, passwd = DBPasswd, db = DBName)
    cursor = db.cursor()
    return cursor
    
def getParameter():
    return Parameter(10, 2000, 50, 0.2, 0, 10)