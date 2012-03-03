"""
usage: parameters.maxTime / parameters.maxStandpoint ...
"""
import MySQLdb

class Parameter():
    def __init__(self, maxTime, maxStandpoint, maxPopulation, crossoverRate, shiftRate, maskRate, maxNumActuators, maxSelection, maxSampling, mu, radius):
        self.maxTime         = maxTime          ## computation time   < 10s
        self.maxStandpoint   = maxStandpoint    ## computation rounds < 5000
        self.maxPopulation   = maxPopulation    ## population size = 40                    (v1)
        self.crossoverRate   = crossoverRate    ## crossover rate  = 0.7                   (v2)
        self.shiftRate       = shiftRate        ## shift rate      = 0.7                   (v3)
        self.maskRate        = maskRate         ## mask rate       = 0.1                   (v4)
        self.maxNumActuators = maxNumActuators  ## number of actuator
        self.maxSelection    = maxSelection     ## number of actuator plans be saved = 10  (v5)
        self.maxSampling     = maxSampling      ## number of sampling actuator plans = 3   (v6)
        self.mu              = mu               ## Gaussian parameter = 0.25               (v7)
        self.radius          = radius           ## Gaussian radius    = 0.5                (v8)

def databaseConnection():
    DBHost   = 'gardenia.csie.ntu.edu.tw'
    DBUser   = 'firzendragon'
    DBPasswd = 'dragon#336'
    DBName   = 'smartpower2'
    db = MySQLdb.connect(host = DBHost,user = DBUser, passwd = DBPasswd, db = DBName)
    cursor = db.cursor()
    return cursor
    
def getParameter():
    return Parameter(10, 5000, 40, 0.7, 0.7, 0.1, 0, 10, 3, 0.25, 0.5)