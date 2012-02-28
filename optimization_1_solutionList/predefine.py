"""
usage: parameters.maxTime / parameters.maxStandpoint ...
"""
import MySQLdb

class Parameter():
    def __init__(self, maxTime, maxStandpoint, maxPopulation, crossoverRate, mutateRate, maxNumActuators, maxSelection):
        self.maxTime         = maxTime          ## computation time   < 10s
        self.maxStandpoint   = maxStandpoint    ## computation rounds < 2000
        self.maxPopulation   = maxPopulation    ## population size    = 50
        self.crossoverRate   = crossoverRate    ## crossover rate = 0.9
        self.mutateRate      = mutateRate       ## mutation rate  = 0.1
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
    return Parameter(10, 1000, 50, 0.9, 0.1, 0, 10)