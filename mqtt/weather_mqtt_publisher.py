#!/usr/bin/python3
import socket
import time
import paho.mqtt.client as mqtt

# push weather data to MQTT broker

# home assistant MQTT auto discovery messages
ha_temp_discovery = ''' 
{
	"name": "Outside temperature",
	"state_topic": "weather/temp",
	"device_class": "temperature",
	"unit_of_measurement": "°C",
	"suggested_display_precision": 1,
	"platform": "sensor",
	"expire_after": 60
}'''

ha_wind_discovery = '''
{
	"name": "Wind speed",
	"state_topic": "weather/wind",
	"device_class": "wind_speed",
	"unit_of_measurement": "mph",
	"platform": "sensor",
	"suggested_display_precision": 1,
	"expire_after": 60
}'''

ha_rain_discovery = '''
{
	"name": "Rainfall total today",
	"state_topic": "weather/rain",
	"device_class": "precipitation",
	"unit_of_measurement": "mm",
	"platform": "sensor",
	"suggested_display_precision": 1,
	"state_class": "total_increasing",
	"expire_after": 60
}'''

# read latest weather data from rpi4 broadcast packet
def read_weather_data(sfd):
    wind = None
    rain = None
    packet, address = sfd.recvfrom(256)
    weather_data = packet.split()
    utc_timestamp = int(weather_data[0])
    # round values to a sensible precision
    temperature = round(float(weather_data[1]),1)
    if len(weather_data)>2:
        wind = round(float(weather_data[2]),1)
    if len(weather_data)>3:
        rain = round(float(weather_data[3]),1)
    return utc_timestamp, temperature, wind, rain

def on_mqtt_connect(client, userdata, flags, rc):
    print("broker connect: %d" %(rc))
    if rc!=0:
        exit(rc)

# connect to mqtt broker 
mqttc = mqtt.Client(client_id="weather-publisher")
mqttc.on_connect = on_mqtt_connect
mqttc.connect("localhost")

# publish HA MQTT auto-discovery configs
mqttc.publish("homeassistant/sensor/weather/temp/config",ha_temp_discovery,retain=True)
mqttc.publish("homeassistant/sensor/weather/wind/config",ha_wind_discovery,retain=True)
mqttc.publish("homeassistant/sensor/weather/rain/config",ha_rain_discovery,retain=True)

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
    mqttc.publish("weather/temp",str(temp))
    if wind!=None:
        mqttc.publish("weather/wind",str(wind))
    if rain!=None:
        mqttc.publish("weather/rain",str(rain))
    
mqttc.disconnect()
mqttc.loop_stop()
