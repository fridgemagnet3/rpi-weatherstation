#!/usr/bin/python2
import socket
import time
import xml.etree.ElementTree as ET
import datetime
import MySQLdb
import collections

# this receives the weather packets, broadcast by rasp-pi4
# and makes them available via a number of routes
# - the current info is written to /dev/shm/weather.xml
#

# tuple defining available data returned from rasp-pi4
WeatherTuple = collections.namedtuple('Weather', ['timestamp', 'temp', 'wind_speed', 'rain'])
WindStatsTuple = collections.namedtuple('WindStats', ['avg', 'max'])


# create socket to listen for the broadcast packets
# sent periodically by the temperature sensor rpi
temp_sfd = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
temp_sfd.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
listen_address = ('0.0.0.0',52003)
temp_sfd.bind(listen_address)

epoch = datetime.datetime.utcfromtimestamp(0)

# min and max temps for today
min_temp = 100.0
max_temp = -100.0
min_temp_timestamp = 0
max_temp_timestamp = 0
# hourly temperatures
hour_temps = list(None for index in range(24))

# min and max wind speed for today
min_wind = 100.0 
max_wind = 0.0
min_wind_timestamp = 0
max_wind_timestamp = 0
# these are used to compute the average wind speed for the day
day_wind_samples = 0
day_wind_totals = 0.0
# these are used to compute the average wind speed for the last hour
hour_wind_samples = 0
hour_wind_totals = 0.0
# these track max wind speed this hour
hour_wind_max = 0.0
hour_winds = list(None for index in range(24))

#rainfall
total_rainfall = 0.0
hour_rain = list(None for index in range(24))
last_rainfall_timestamp = 0

# FIFOs for holding recent readings for last 10 minutes & hour respectively
stats_last10min_queue = collections.deque()
stats_last60min_queue = collections.deque()

# tracking current day and hour
current_hour = datetime.datetime.now().hour
current_day = datetime.datetime.now().day

# read latest weather data from rpi4 broadcast packet
def read_weather_data(sfd):
    data_available = False
    wind = -1 
    rain = -1 
    while not data_available:
        try:
            packet, address = sfd.recvfrom(256)
            weather_data = packet.split()
            utc_timestamp = int(weather_data[0])
            temperature = weather_data[1]
            if len(weather_data)>2:
              wind = weather_data[2]
              if len(weather_data)>3:
                rain = weather_data[3]
            data_available = True
        except socket.error:
            time.sleep(1)
            continue
    current_data=WeatherTuple(timestamp=utc_timestamp,temp=float(temperature),wind_speed=float(wind),rain=float(rain))
    
    # return latest temperature if available
    return current_data

# commit the current set of readings to the database
def update_db():

    # set the report date - at the point of reporting we've just
    # transitioned to the following day so we want to log it for yesterday
    report_date = datetime.date.today()
    report_date = report_date - datetime.timedelta(days=1)
    
    try:
        db = MySQLdb.connect(host="localhost",
                             user="weather",
                             passwd="weather",
                             db="weather")
        cur = db.cursor()
        
        # convert temperature min/max timestamps back to a datetime
        # note that the timestamps are retained and submitted to the database as UTC values
        # however the transition point (when this happens) is of course *local* time
        # so the check that follows prior to performing the update needs to be performed with
        # a local timestamp
        local_timestamp = datetime.datetime.fromtimestamp(min_temp_timestamp)
        # check these settings correspond to the expected date
        if local_timestamp.date()==report_date:
            report_max_datetime = datetime.datetime.utcfromtimestamp(max_temp_timestamp)
            report_min_datetime = datetime.datetime.utcfromtimestamp(min_temp_timestamp)
            cur.execute("INSERT INTO temperature(date,min,mintime,max,maxtime) VALUES('{0}',{1},'{2}',{3},'{4}')".format(
                        report_date.strftime("%Y-%m-%d"),min_temp,
                        report_min_datetime.strftime("%H:%M:%S"),max_temp,
                        report_max_datetime.strftime("%H:%M:%S")))
                        
        # same for wind speed
        local_timestamp = datetime.datetime.fromtimestamp(min_wind_timestamp)
        # check these settings correspond to the expected date
        if local_timestamp.date()==report_date:
            report_max_datetime = datetime.datetime.utcfromtimestamp(max_wind_timestamp)
            report_min_datetime = datetime.datetime.utcfromtimestamp(min_wind_timestamp)
            # compute average of all the maximums recorded today
            samples = 0.0 
            total = 0.0
            for hour,wind in enumerate(hour_winds):
                if wind!=None:
                    samples = samples + 1
                    total = total + wind.max
            cur.execute("INSERT INTO wind_speed(date,min,mintime,max,maxtime,average,maxavg) VALUES('{0}',{1},'{2}',{3},'{4}',{5},{6})".format(
                        report_date.strftime("%Y-%m-%d"),min_wind,
                        report_min_datetime.strftime("%H:%M:%S"),max_wind,
                        report_max_datetime.strftime("%H:%M:%S"),day_wind_totals/day_wind_samples,total/samples))
                        
        local_timestamp = datetime.datetime.fromtimestamp(last_rainfall_timestamp)
        # check timestamp is for expected date and log the rainfall
        if local_timestamp.date()==report_date:
            report_rainfall_datetime = datetime.datetime.utcfromtimestamp(last_rainfall_timestamp)
            cur.execute("INSERT INTO rainfall(date,total) VALUES('{0}',{1})".format(
                report_rainfall_datetime.strftime("%Y-%m-%d"),total_rainfall))
                
        cur.close()
        db.commit()
        db.close()
    except MySQLdb.Error, e:
        print "MySQL Error: %s" % str(e)

# read current settings from (xml) config
def read_config():
    global max_temp,max_temp_timestamp,min_temp,min_temp_timestamp,hour_temps
    global max_wind,max_wind_timestamp,min_wind,min_wind_timestamp,hour_winds
    global day_wind_samples, day_wind_totals, hour_wind_samples, hour_wind_totals
    global total_rainfall, hour_rain
    
    try:
        tree = ET.parse('/dev/shm/weather.xml')
        root_node = tree.getroot()
        # load temperature values
        max = root_node.find('./temperature/max')
        if max!=None:
            max_temp = float(max.text)
            max_temp_timestamp = int(max.get('timestamp'))
        min = root_node.find('./temperature/min')
        if min!=None:
            min_temp = float(min.text)
            min_temp_timestamp = int(min.get('timestamp'))        
        hourly_node = root_node.find('./temperature/hourly_readings')
        if hourly_node!=None:
            for temp_value in hourly_node:
                hour = int(temp_value.get('value'))
                hour_temps[hour] = float(temp_value.text)

        # restore the current_hour from the last written timestamp
        current = root_node.find('./temperature/current')
        if current!=None:
            last_timestamp = datetime.datetime.fromtimestamp(int(current.get('timestamp')))
            current_hour = last_timestamp.hour
            
        # load wind values
        max = root_node.find('./wind_speed/max')
        if max!=None:
            max_wind = float(max.text)
            max_wind_timestamp = int(max.get('timestamp'))
        min = root_node.find('./wind_speed/min')
        if min!=None:
            min_wind = float(min.text)
            min_wind_timestamp = int(min.get('timestamp'))        
        hourly_node = root_node.find('./wind_speed/hourly_readings')
        if hourly_node!=None:
            for wind_value in hourly_node:
                hour = int(wind_value.get('value'))
                t = wind_value.get('max')
                if t==None:
                    t=0 
                wind_stats = WindStatsTuple(avg=float(float(wind_value.text)),max=float(t))
                hour_winds[hour] = wind_stats
        day_avg_node = root_node.find('./wind_speed/day_average')
        if day_avg_node!=None:
            day_wind_samples = int(day_avg_node.get('samples'))
            day_wind_totals = float(day_avg_node.get('totals'))
        hour_avg_node = root_node.find('./wind_speed/hour_average')
        if hour_avg_node!=None:
            hour_wind_samples = int(hour_avg_node.get('samples'))
            hour_wind_totals = float(hour_avg_node.get('totals'))
        
        # rainfall
        current = root_node.find('./rain/total')
        if current!=None:
            total_rainfall = float(current.text)
        hourly_node = root_node.find('./rain/hourly_readings')
        if hourly_node!=None:
            for rain_value in hourly_node:
                hour = int(rain_value.get('value'))
                hour_rain[hour] = float(rain_value.text)

    except:
        print "failed to load config"

# update rolling weather stats, return rainfall readings 
def update_rolling_weather_stats(timestamp,weather_data):
    ten_min_ago = timestamp - 60*10
    one_hour_ago = timestamp - 60*60
    rain_hour_ago = None
    rain_ten_min_ago = None
    # append current readings to FIFOs
    stats_last10min_queue.append(weather_data)
    stats_last60min_queue.append(weather_data)
    # remove old readings
    remove_old = True
    while remove_old and len(stats_last10min_queue):
        if stats_last10min_queue[0].timestamp < ten_min_ago:
            rain_ten_min_ago = stats_last10min_queue.popleft().rain
        else:
            remove_old = False
            
    remove_old = True
    while remove_old and len(stats_last60min_queue):
        if stats_last60min_queue[0].timestamp < one_hour_ago:
            rain_hour_ago = stats_last60min_queue.popleft().rain
        else:
            remove_old = False

    # normally the last item popped will be the figure we want but
    # the jitter with which we get readings means we may not remove
    # anything, in which case apply a 5s margin of error and return
    # the last figure if it's in the right arena
    if rain_ten_min_ago==None and len(stats_last10min_queue):
        ten_min_ago = ten_min_ago + 5
        if stats_last10min_queue[0].timestamp < ten_min_ago:
            rain_ten_min_ago = stats_last10min_queue[0].rain

    if rain_hour_ago==None and len(stats_last60min_queue):
        one_hour_ago = one_hour_ago + 5
        if stats_last60min_queue[0].timestamp < one_hour_ago:
            rain_hour_ago = stats_last60min_queue[0].rain
            
    return rain_ten_min_ago, rain_hour_ago

# update wind stats for last 10 minutes, last hour
def wind_rolling_stats(wind_node):
    max_wind = -1.0
    min_wind = 1000 ;
    totals = 0.0
    for reading in stats_last10min_queue:
        totals = totals + reading.wind_speed
        if reading.wind_speed > max_wind:
            max_wind = reading.wind_speed
            max_wind_timestamp = reading.timestamp
        if reading.wind_speed < min_wind:
            min_wind = reading.wind_speed
            min_wind_timestamp = reading.timestamp
            
    if len(stats_last10min_queue):
        avg_wind = totals / len(stats_last10min_queue)
        ET.SubElement(wind_node, "max_last_10_mins",timestamp=str(max_wind_timestamp)).text = str(max_wind)
        ET.SubElement(wind_node, "min_last_10_mins",timestamp=str(min_wind_timestamp)).text = str(min_wind)
        ET.SubElement(wind_node, "avg_last_10_mins").text = str(avg_wind)
        
    max_wind = -1.0
    min_wind = 1000 ;
    totals = 0.0
    for reading in stats_last60min_queue:
        totals = totals + reading.wind_speed
        if reading.wind_speed > max_wind:
            max_wind = reading.wind_speed
            max_wind_timestamp = reading.timestamp
        if reading.wind_speed < min_wind:
            min_wind = reading.wind_speed
            min_wind_timestamp = reading.timestamp

    if len(stats_last60min_queue):
        avg_wind = totals / len(stats_last60min_queue)
        ET.SubElement(wind_node, "max_last_hour",timestamp=str(max_wind_timestamp)).text = str(max_wind)
        ET.SubElement(wind_node, "min_last_hour",timestamp=str(min_wind_timestamp)).text = str(min_wind)
        ET.SubElement(wind_node, "avg_last_hour").text = str(avg_wind)
       

# -- START --
read_config()

while True:
    weather_data = read_weather_data(temp_sfd)
    current_time = datetime.datetime.now()
    current_utc_time = current_time.utcnow()
    time_from_epoch = int((current_utc_time - epoch).total_seconds())
    
    # on transition to next day, write the previous stats
    if current_day != current_time.day:
        update_db()
        # reset states ready for next day
        max_temp = -100
        min_temp = 100
        current_day = current_time.day
        hour_temps = list(None for index in range(24))
        max_wind = 0
        min_wind = 100
        hour_winds = list(None for index in range(24))
        day_wind_samples = 0
        day_wind_totals = 0.0
        total_rainfall = 0.0
        hour_rain = list(None for index in range(24))
        
    # build up a list of the extremes per hour
    if current_hour!=current_time.hour:
      current_hour=current_time.hour
      hour_temps[current_hour] = weather_data.temp
      # wind stats comprise avg and max this hour
      wind_stats=WindStatsTuple(avg=float(hour_wind_totals/hour_wind_samples),max=float(hour_wind_max))
      hour_winds[current_hour] = wind_stats 
      hour_wind_totals = 0.0
      hour_wind_samples = 0
      hour_wind_max = 0.0
      hour_rain[current_hour] = total_rainfall
    
    # track the max/min temperatures
    if weather_data.temp > max_temp:
        max_temp = weather_data.temp
        max_temp_timestamp = time_from_epoch
    if weather_data.temp < min_temp:
        min_temp = weather_data.temp
        min_temp_timestamp = time_from_epoch

    # track the max/min wind speeds
    if weather_data.wind_speed > max_wind:
        max_wind = weather_data.wind_speed
        max_wind_timestamp = time_from_epoch
    if weather_data.wind_speed < min_wind:
        min_wind = weather_data.wind_speed
        min_wind_timestamp = time_from_epoch
    # max this hour
    if weather_data.wind_speed > hour_wind_max:
        hour_wind_max = weather_data.wind_speed
    
    # track averages
    day_wind_samples = day_wind_samples + 1
    day_wind_totals = day_wind_totals + weather_data.wind_speed
    hour_wind_samples = hour_wind_samples + 1
    hour_wind_totals = hour_wind_totals + weather_data.wind_speed
        
    # create current stats as an xml file
    root_node = ET.Element('weather')
    temp_node = ET.SubElement(root_node, "temperature")
    # write the weather data
    ET.SubElement(temp_node, "current", timestamp=str(time_from_epoch)).text = str(weather_data.temp)
    ET.SubElement(temp_node, "max", timestamp=str(max_temp_timestamp)).text = str(max_temp)
    ET.SubElement(temp_node, "min", timestamp=str(min_temp_timestamp)).text = str(min_temp)
    
    hourly_nodes = ET.SubElement(temp_node, "hourly_readings")
    for hour,temp in enumerate(hour_temps):
        if temp!=None:
            ET.SubElement(hourly_nodes,"hour", value=str(hour)).text = str(temp)
          
    # write the wind speed data
    wind_node = ET.SubElement(root_node, "wind_speed")
    if weather_data.wind_speed!=-1:
        ET.SubElement(wind_node, "current", timestamp=str(time_from_epoch)).text = str(weather_data.wind_speed)
    # rolling wind stats
    wind_rolling_stats(wind_node)

    # stats overall for today
    ET.SubElement(wind_node, "max", timestamp=str(max_wind_timestamp)).text = str(max_wind)
    ET.SubElement(wind_node, "min", timestamp=str(min_wind_timestamp)).text = str(min_wind)
    ET.SubElement(wind_node, "day_average", samples=str(day_wind_samples), totals=str(day_wind_totals)).text = str(day_wind_totals/day_wind_samples)
    ET.SubElement(wind_node, "hour_average", samples=str(hour_wind_samples), totals=str(hour_wind_totals)).text = str(hour_wind_totals/hour_wind_samples)
    ET.SubElement(wind_node, "hour_max").text = str(hour_wind_max)
    hourly_nodes = ET.SubElement(wind_node, "hourly_readings")
    for hour,wind in enumerate(hour_winds):
        if wind!=None:
            ET.SubElement(hourly_nodes,"hour", value=str(hour),max=str(wind.max)).text = str(wind.avg)

    # rainfall
    rain_node = ET.SubElement(root_node, "rain")
    if weather_data.rain!=-1:
        # convert the timestamp sent with the weather packet to something usable
        last_rainfall_timestamp = time_from_epoch
        origin_datetime = datetime.datetime.fromtimestamp(weather_data.timestamp)
        # only update the rainfall if we're both on the same day...
        # this ensures that we will always log the previous days readings and likewise
        # don't reset back to zero prematurely (if rpi4 is behind us)
        if current_day==origin_datetime.day:
            ET.SubElement(rain_node, "total", timestamp=str(time_from_epoch)).text = str(weather_data.rain)
            if weather_data.rain > total_rainfall:
                total_rainfall = weather_data.rain
            # recent rainfall stats
            rain_stats = update_rolling_weather_stats(time_from_epoch,weather_data)
            if rain_stats[0]!=None:
                ET.SubElement(rain_node, "last_10_mins").text = str(weather_data.rain-rain_stats[0])
            if rain_stats[1]!=None:
                ET.SubElement(rain_node, "last_hour").text = str(weather_data.rain-rain_stats[1])
                
    hourly_nodes = ET.SubElement(rain_node, "hourly_readings")
    for hour,rainfall in enumerate(hour_rain):
        if rainfall!=None:
            ET.SubElement(hourly_nodes,"hour", value=str(hour)).text = str(rainfall)
        
    tree = ET.ElementTree(root_node)
    tree.write("/dev/shm/weather.xml",xml_declaration=True,encoding='utf-8')

            

