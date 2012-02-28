import serial, MySQLdb

def serialConnection(serialNum):
    ser = serial.Serial(int(serialNum), 9600)
    if not ser.isOpen():
        print "You must set serial prot first!"
    return ser

def databaseConnection():
    DBHost   = 'gardenia.csie.ntu.edu.tw'
    DBUser   = 'firzendragon'
    DBPasswd = 'dragon#336'
    DBName   = 'smartpower2'
    db = MySQLdb.connect(host = DBHost,user = DBUser, passwd = DBPasswd, db = DBName)
    cursor = db.cursor()
    if not cursor:
        print "Connection failed!"
    return cursor
    
def startingUp(ser):
    data = None
    while data != 'Starting up':
        data = ser.readline().strip("\r\n")
    print "Starting up"
