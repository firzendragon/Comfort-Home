import time

def getValue(cursor, node_id, sensor_name):
    sql = ("select * from data where node_id = '%s' and sensor_name = '%s' order by context_id desc limit 1;" % (node_id, sensor_name))
    cursor.execute(sql)
    row = cursor.fetchone()
    return row[3]

def dataCollection(ser, cursor):
    ## read data, ex: 0001,temperature:23.4,humidity:65.7,light:235,sound:2,motion:0
    packet  = ser.readline().strip("\r\n")
    print packet
    datas   = packet.strip("\r").strip("\n").split(",")
    node_id = datas[0]

    ## deal with each type of sensor, ex: temperature:23.4
    for i in range(1,len(datas)):
        data = datas[i].split(":")
        if  len(data) == 2: ## special case for error data like '' or 'motion:'
            sensor_name = data[0]
            value       = data[1].split(" ")[0]  ## special case for motion data like '0 \r0001'
            dataStore(cursor, node_id, sensor_name, value)

def dataStore(cursor, node_id, sensor_name, value):
    oldvalue = getValue(cursor, node_id, sensor_name)
    if  float(oldvalue) != float(value):
        timeNow = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(time.time()))
        sql = "insert into data (node_id,sensor_name,value,timestamp) VALUES('%s','%s','%s','%s')" % (node_id,sensor_name,value,timeNow)
        cursor.execute(sql)
    