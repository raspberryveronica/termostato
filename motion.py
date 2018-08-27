import RPi.GPIO as GPIO
import time

GPIO.setmode(GPIO.BCM)

pirPin = 26
GPIO.setup(pirPin, GPIO.IN)
while True:
    if GPIO.input(pirPin) == GPIO.HIGH:
        print "Motion detected!"
	GPIO.setmode(GPIO.BCM)
	GPIO.setup(17, GPIO.OUT)
	GPIO.output(17, GPIO.HIGH)
	time.sleep(1)
    else:
	#print "No motion"
	GPIO.setmode(GPIO.BCM)
        GPIO.setup(17, GPIO.OUT)
        GPIO.output(17, GPIO.LOW)

