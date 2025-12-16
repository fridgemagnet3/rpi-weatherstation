#!/usr/bin/python3
import socket
import time
import paho.mqtt.client as mqtt

# push weather data to MQTT broker

# read latest weather data from rpi4 broadcast packet
def read_weather_data(sfd):
    wind = None
    rain = None
    packet, address = sfd.recvfrom(256)
    weather_data = packet.split()
    utc_timestamp = int(weather_data[0])
    temperature = weather_data[1]
    if len(weather_data)>2:
        wind = weather_data[2]
    if len(weather_data)>3:
        rain = weather_data[3]
    return utc_timestamp, temperature, wind, rain

def on_mqtt_connect(client, userdata, flags, rc):
    print("broker connect: %d" %(rc))
    if rc!=0:
        exit(rc)

# connect to mqtt broker 
mqttc = mqtt.Client(client_id="weather-publisher")
mqttc.on_connect = on_mqtt_connect
mqttc.connect("localhost")

# create socket to listen for the broadcast packets
# sent periodically by the temperature sensor rpi
temp_sfd = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
listen_address = ('0.0.0.0',52003)
temp_sfd.bind(listen_address)

mqttc.loop_start()
print("starting loop")
while True:
    # wait for then fetch next UDP packet with weather data
    timestamp, temp, wind, rain = read_weather_data(temp_sfd)
    # publish it
    mqttc.publish("weather/temp",temp)
    if wind!=None:
        mqttc.publish("weather/wind",wind)
    if rain!=None:
        mqttc.publish("weather/rain",rain)
    
mqttc.disconnect()
mqttc.loop_stop()
