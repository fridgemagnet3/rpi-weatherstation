#!/usr/bin/python2

import socket
import glob
import time
import Adafruit_ADS1x15
import RPi.GPIO as GPIO
import datetime
import threading
import os

# for the temperature stuff, very much lifted from
# https://learn.adafruit.com/adafruits-raspberry-pi-lesson-11-ds18b20-temperature-sensing/software

# for the ADC (wind)
# https://github.com/adafruit/Adafruit_Python_ADS1x15/blob/master/examples/simpletest.py

# for the rain gauge:
# https://pi.gate.ac.uk/posts/2014/01/25/raingauge/

epoch = datetime.datetime.utcfromtimestamp(0)

# count of rain measured today
rain = 0.0
# tracking current day for when we need to reset the rain gauge
current_day = datetime.datetime.now().day
# lock for write access to the rain counter
rain_lock = threading.Lock()
# initialise time of last tip to something sensible
last_tip_time = datetime.datetime.utcnow()
# previous rainfall (used in dealing with spurious bucket tips)
previous_rain = rain
# rainfall state file - holds current rainfall reading
rainfile = "/var/cache/rain.txt"

def read_temp_raw():
    f = open(device_file, 'r')
    lines = f.readlines()
    f.close()
    return lines

def read_temp():
    try:
        lines = read_temp_raw()
        while lines[0].strip()[-3:] != 'YES':
            time.sleep(0.2)
            lines = read_temp_raw()
        equals_pos = lines[1].find('t=')
        if equals_pos != -1:
            temp_string = lines[1][equals_pos+2:]
            temp_c = float(temp_string) / 1000.0
            return temp_c
    except:
        return 0

# the call back function for each bucket tip
def rain_tip(channel):
    global rain, last_tip_time, previous_rain
    
    calibration = 0.2794  # ml per tip
    # we can get spurious tips possibly due to the wind rocking the bucket
    # solitary ones we can't do anything about but often we get 2 or 3 in
    # close proximity which we can try and filter out...
    
    # work out the elapsed time between the last tip and now
    time_now = datetime.datetime.utcnow()
    time_delta = time_now - last_tip_time
    last_tip_time = time_now
    rain_lock.acquire()
    if time_delta.total_seconds() > 10:
        # delta is greater than 10 seconds, save the current value & time
        # prior to incrementing the count
        previous_rain = rain
        rain = rain + calibration
    else:
        # tip has occurred within a 10s window so deem as spurious and restore
        # the previous rainfall figure
        rain = previous_rain
    # write value to statefile for recovery purposes
    try:
        fd = open(rainfile,"w")
        fd.write(str(rain))
        fd.close()
    except IOError:
        pass
    rain_lock.release()

### begin
# recover the last rainfall reading (if available)
try:
    fd = open(rainfile,"r")
    value = fd.read()
    rain = float(value)
    fd.close()
except IOError:
    pass
    
# Create an ADS1115 ADC (16-bit) instance - ADC 1 interfaces to the anemometer
adc = Adafruit_ADS1x15.ADS1115()

# anemometer has a voltage range of 0.4 to 2V
# the ADC has a range of 0 to +/-4.096V (returned as +/-32768)
# The ADC will therefore map the anemometer to a range of values from 3200 to 16000 (12800 samples)
# the voltage range then gives us wind speed from 0 to 32.4 metres/second
adc_sample_range = 12800.0
adc_zero_ref = 3200
metres_per_sec_to_miles_per_hour = 2.236936
max_wind_speed = 32.4
 
# configure the GPIO for the rain gauge
rain_gpio = 27
GPIO.setmode(GPIO.BCM)  
GPIO.setup(rain_gpio, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.add_event_detect(rain_gpio, GPIO.FALLING, callback=rain_tip, bouncetime=700)


# one wire interface to the temp sensor
base_dir = '/sys/bus/w1/devices/'
device_folder_list = glob.glob(base_dir + '28*')
if device_folder_list:
    device_folder = device_folder_list[0]
    device_file = device_folder + '/w1_slave'

    # start the ADC running
    adc.start_adc(0, gain=1)
    
    print "Entering broadcast loop"
    # create broadcast capable socket    
    sfd = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sfd.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    sfd.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST, 1)
    # periodically generate broadcast packets with the current temperature
    while True:
        
        # form up string to send:
        # - write in Unix timestamp
        current_utc_time = datetime.datetime.utcnow()
        time_from_epoch = int((current_utc_time - epoch).total_seconds())
        packet = str(time_from_epoch)
        # - followed by temperature
        temp = read_temp()
        # filter out any spurious reading from the sensor
        if (temp > 60) or (temp < -30):
            continue
        packet = packet + " " + str(temp)
        try:
            # read the wind speed from ADC and convert to mph
            adc_wind = adc.get_last_result()
            wind_speed = ((adc_wind-adc_zero_ref)/adc_sample_range) * max_wind_speed
            if wind_speed<0.0:
              wind_speed = 0.0
            # append to the temperature string
            packet = packet + " " + str(wind_speed*metres_per_sec_to_miles_per_hour)
            # followed by the current rain measurement
            packet = packet + " " + str(rain)
        except:
            continue  # wind speed unavailable
            
        try:
            sfd.sendto(packet, ('255.255.255.255', 52003) )
        except socket.error:
            sfd.sendto(packet, ('127.0.0.1', 52003) )
        #time.sleep(0.5)
        
        current_time = datetime.datetime.now()
        # on transition to next day, reset the rain gauge stats
        if current_day != current_time.day:
            rain_lock.acquire()
            rain = 0 
            if os.path.isfile(rainfile):
                os.unlink(rainfile)
            rain_lock.release()
            current_day = current_time.day

else:
    print "No slave devices found"
    
