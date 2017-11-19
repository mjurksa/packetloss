#!/usr/bin/env python

import time                                # For for loop to time it
import subprocess, platform
import datetime
import MySQLdb
import ConfigParser

config_dir = "/opt/packetloss/conf.ini"

def pingOk(sHost):
    try:
            output = subprocess.check_output("ping -{} 1 {}".format('n' if platform.system().lower()=="windows" else 'c', sHost), shell=True)

    except Exception as e:
        return 1 #not reacheble

    return 0 #ping is ok

Config = ConfigParser.ConfigParser()
Config.read(config_dir)

conn = MySQLdb.connect(host= Config.get('database','db_host'),user= Config.get('database','db_python_user'),db= Config.get('database','db_name'))
command = conn.cursor()

t_end = time.time() + int(Config.get('script','time'))

pings_total = 0
pings_lost = 0

while time.time() < t_end:
    try:
        ping_status = pingOk(str(Config.get('script','ping_server')))
        if ping_status == 1:
            pings_lost = pings_lost + 1
        pings_total = pings_total + 1

    except Exception as e:
        conn.rollback()

    time.sleep(int(Config.get('script','sleep')))

command.execute("""INSERT INTO tblPacket VALUES (%s,%s,%s,%s)""",(0,0,datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),pings_total))
command.execute("""INSERT INTO tblPacket VALUES (%s,%s,%s,%s)""",(0,1,datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),pings_lost))
conn.commit()

conn.close()
