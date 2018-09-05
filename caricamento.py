#!/usr/bin/env python
import Adafruit_BMP.BMP085 as BMP085

import urllib
import urllib2
import MySQLdb
import os
import glob
import time
import sys
import RPi.GPIO as GPIO

os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

base_dir = '/sys/bus/w1/devices/'
device_folder = glob.glob(base_dir + '28*')[0]
device_file = device_folder + '/w1_slave'

GPIO.setmode(GPIO.BCM)
GPIO.setup(17, GPIO.OUT)

db = MySQLdb.connect("localhost", "monitor", "password", "temps")
curs=db.cursor()

a=0
default=30.0

sensor = BMP085.BMP085()

def read_temp_raw():
    f = open(device_file, 'r')
    lines = f.readlines()
    f.close()
    return lines

def read_temp():

    pressure = '{0:0.2f}'.format(sensor.read_pressure())
    altitude = '{0:0.2f}'.format(12)


    lines = read_temp_raw()
    while lines[0].strip()[-3:] != 'YES': time.sleep(0.0)
    lines = read_temp_raw()
    equals_pos = lines[1].find('t=')
    if equals_pos != -1: temp_string = lines[1][equals_pos+2:]
    temp_c = float(temp_string)/1000.0
    try:
	#Inserimento valori nel database
	curs.execute ("SELECT * FROM tempdat ORDER BY tdate DESC, ttime DESC LIMIT 1")
	for reading in curs.fetchall():
		 termostato=str(reading[4])
		 print termostato
	curs.execute ("""INSERT INTO tempdat	values(CURRENT_DATE(), NOW() , 'prova', %s,%s,%s,%s)""",(temp_c,termostato,pressure,altitude))
	db.commit()


	#print(type(temp_c))
	termostato1=float(termostato)
	if temp_c>=termostato1:
        	GPIO.output(17, GPIO.HIGH)
    	else:
        	GPIO.output(17, GPIO.LOW)


        print "Data committed"
    except:
        #print "Error: the database is being rolled back"
        #db.rollback()
	#CREA IL PRIMO VALORE (DI DEFAULT) NEL CASO IN CUI IL DATABASE SIA VUOTO
	curs.execute ("""INSERT INTO tempdat values(CURRENT_DATE(), NOW() , 'prova', %s,100,12,100)""",(temp_c,))
	db.commit()

    curs.execute

    temp_f = temp_c * 9.0 / 5.0 + 32.0
    return temp_c, temp_f




#CELSIUS CALCULATION
def read_temp_c():
    lines = read_temp_raw()
    while lines[0].strip()[-3:] != 'YES':  time.sleep(0.0)
    lines = read_temp_raw()
    equals_pos = lines[1].find('t=')
    if equals_pos != -1: temp_string = lines[1][equals_pos+2:]
    temp_c = int(temp_string) / 1000.0 # TEMP_STRING IS THE SENSOR OUTPUT, MAKE SURE IT'S AN INTEGER TO DO THE MATH
    temp_c = str(round(temp_c, 1)) # ROUND THE RESULT TO 1 PLACE AFTER THE DECIMAL, THEN CONVERT IT TO A STRING
    return temp_c


while a<1 :
    a=a+1
    print(read_temp())
    time.sleep(0.0)

