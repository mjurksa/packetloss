#!/usr/bin/env python

import time                                # For for loop to time it
import subprocess, platform
import datetime
import MySQLdb

def pingOk(sHost):
    try:
            output = subprocess.check_output("ping -{} 1 {}".format('n' if platform.system().lower()=="windows" else 'c', sHost), shell=True)

    except Exception as e:
        return 1 #not reacheble

    return 0 #ping is ok

conn = MySQLdb.connect(host= "localhost",user="root",db="packetloss")
command = conn.cursor()

t_end = time.time() + 58 #in Seconds

ping_total = 0
pings_lost = 0

while time.time() < t_end:
    try:
        ping_code = pingOk("8.8.8.8")
        if ping_code == 1:
            pings_lost = pings_lost + 1
        ping_total = ping_total + 1

    except Exception as e:
        conn.rollback()

    time.sleep(1)

command.execute("""INSERT INTO tblPacket VALUES (%s,%s,%s,%s)""",(0,0,datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),ping_total))
command.execute("""INSERT INTO tblPacket VALUES (%s,%s,%s,%s)""",(0,1,datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),pings_lost))
conn.commit()

conn.close()
