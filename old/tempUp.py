import RPi.GPIO as GPIO
import time
import os
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.setup(4, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)
GPIO.setup(23, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)
GPIO.setup(24, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)
GPIO.setup(18,GPIO.OUT, initial = 0)
GPIO.setup(27,GPIO.OUT, initial = 0)
GPIO.setup(17,GPIO.OUT, initial = 0)
try:
	in_file = open("temp","r")
	temp = int(in_file.readline())
	in_file.close()
	print ("La temperatura di avvio è:" + str(temp)+"°C")
except IOError:
   print ("File non esistente temperatura default a 0")
   temp=0
not_pressed = True
while not_pressed:
	if GPIO.input(24):
		not_pressed=False
	if GPIO.input(4):
		temp=temp+1
		time.sleep(0.3)
		print (temp)
	elif GPIO.input(23):
		temp=temp-1
		time.sleep(0.3)
		print (temp)
	if temp<=15:
		GPIO.output(27,True)#verde
		GPIO.output(18,False)
		GPIO.output(17,False)
	if (temp >=16 and temp <=35):
		GPIO.output(18,True)#giallo
		GPIO.output(27,False)
		GPIO.output(17,False)
	if temp >= 36:
		GPIO.output(17,True)#rosso
		GPIO.output(18,False)
		GPIO.output(27,False)
	time.sleep(0.3)
print("Ciao Ciao, la temperatura è: " + str(temp)+"°C")
out_file = open("temp","w")
out_file.write(str(temp))
out_file.close()
GPIO.cleanup()
