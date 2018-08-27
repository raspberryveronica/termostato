#!/usr/bin/env python

import MySQLdb
db = MySQLdb.connect("localhost", "monitor", "password", "temps")

curs=db.cursor()


#curs.execute ("SELECT * FROM tempdat")
curs.execute ("SELECT * FROM tempdat ORDER BY tdate DESC, ttime DESC LIMIT 1")



print "\nDate     	Time		Zone		Temperature		Termostato"
print "======================================================================================"

for reading in curs.fetchall():
    print str(reading[0])+"	"+str(reading[1])+" 	"+\
                reading[2]+" 	 	"+str(reading[3]+"		 	   "+str(reading[4]))
