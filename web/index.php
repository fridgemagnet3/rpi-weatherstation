<!DOCTYPE html>
<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css">
</style>
<script type="text/javascript" src="smoothie.js"></script>
<script type="text/javascript">
// this is responsible for dynamically updating the current readings
var wind_line = new TimeSeries();
// refresh interval
setInterval(loadDoc,2000) ;

function createTimeline()
{
    var wind_chart = new SmoothieChart({millisPerPixel:100,timestampFormatter:SmoothieChart.timeFormatter,grid:{fillStyle:'#d6d6d6',millisPerLine:5000},labels:{fillStyle:'#4444aa'}});
    wind_chart.addTimeSeries(wind_line, { strokeStyle: '#cc66ff', fillStyle: 'rgba(204, 102, 255, 0.2)', lineWidth: 3 });
    wind_chart.streamTo(document.getElementById("wind_graph"),2000);
}

// https://stackoverflow.com/questions/847185/convert-a-unix-timestamp-to-time-in-javascript
function timeConverter(UNIX_timestamp){
  var a = new Date(UNIX_timestamp * 1000);
  
  let options = {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "numeric",
    minute: "numeric",
    second: "numeric",
    timeZone: 'Europe/London',
    timeZoneName: "short",
  };
  // always display in UK date/time   
  var time = '<br>' + new Intl.DateTimeFormat(undefined, options).format(a) ;
  return time;
}

function timeOnlyConverter(UNIX_timestamp){
  var a = new Date(UNIX_timestamp * 1000);
  let options = {
    hour: "numeric",
    minute: "numeric",
    second: "numeric",
    timeZone: 'Europe/London',
  }; 
  // always display in UK time  
  var time = new Intl.DateTimeFormat(undefined, options).format(a) ;

  return time;
}

// invoked via "setInterval" at 2s interval - refetch the weather XML
function loadDoc() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      updateWeatherStats(this);
    }
  };
  xhttp.open("GET", "weather.xml", true);
  xhttp.send();
}

function updateWeatherStats(xml) {
  var xmlDoc = xml.responseXML;
  var current_val = xmlDoc.getElementsByTagName("current")[0].childNodes[0].nodeValue;
  var attrs = xmlDoc.getElementsByTagName("current")[0].attributes;
  var timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  var temp_f = parseFloat(current_val) * (9.0/5.0) + 32 ;
  var avg_val ;
  var txt ;
  var DateNow = new Date() ;
  var DateTimestamp = new Date(timestamp*1000) ;
  let options = {
    timeZone: 'Europe/London',
  }; 
  
  // sanity check on the current figures actually being for today in the UK
  if ( DateTimestamp.toLocaleDateString(undefined,options) != DateNow.toLocaleDateString(undefined,options) )
      return ;
  
  // temperature - current value
  txt = "Current: " + current_val + "&deg;C / " + temp_f.toFixed(3) + "&deg;F" ;
  txt += timeConverter(timestamp) ;
  document.getElementById("temp_current").innerHTML = txt;
  // max value
  current_val = xmlDoc.getElementsByTagName("max")[0].childNodes[0].nodeValue;
  temp_f = parseFloat(current_val) * (9.0/5.0) + 32 ;
  txt = current_val + "&deg;C / " + temp_f.toFixed(1) + "&deg;F" ;
  document.getElementById("temp_max").innerHTML = txt;
  attrs = xmlDoc.getElementsByTagName("max")[0].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("temp_max_timestamp").innerHTML = timeOnlyConverter(timestamp);
  // min value
  current_val = xmlDoc.getElementsByTagName("min")[0].childNodes[0].nodeValue;
  temp_f = parseFloat(current_val) * (9.0/5.0) + 32 ;
  txt = current_val + "&deg;C / " + temp_f.toFixed(1) + "&deg;F" ;
  document.getElementById("temp_min").innerHTML = txt;
  attrs = xmlDoc.getElementsByTagName("min")[0].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("temp_min_timestamp").innerHTML = timeOnlyConverter(timestamp);
  
  // wind
  current_val = xmlDoc.getElementsByTagName("current")[1].childNodes[0].nodeValue;
  current_val = parseFloat(current_val) ;
  avg_val = xmlDoc.getElementsByTagName("day_average")[0].childNodes[0].nodeValue;
  avg_val = parseFloat(avg_val) ;
  attrs = xmlDoc.getElementsByTagName("current")[1].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  // update the graph
  txt = "Current: " + current_val.toFixed(1) + " mph, Avg: " ;
  txt += avg_val.toFixed(1) + " mph" + timeConverter(timestamp) ;
  document.getElementById("wind_current").innerHTML = txt;
  wind_line.append(new Date(timestamp * 1000), current_val);

  // max value
  current_val = xmlDoc.getElementsByTagName("max")[1].childNodes[0].nodeValue;
  current_val = parseFloat(current_val) ;
  txt = current_val.toFixed(1) + " mph" ;
  document.getElementById("wind_max").innerHTML = txt;
  attrs = xmlDoc.getElementsByTagName("max")[1].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("wind_max_timestamp").innerHTML = timeOnlyConverter(timestamp);
  // min value
  current_val = xmlDoc.getElementsByTagName("min")[1].childNodes[0].nodeValue;
  current_val = parseFloat(current_val) ;
  txt = current_val.toFixed(1) + " mph" ;
  document.getElementById("wind_min").innerHTML = txt;
  attrs = xmlDoc.getElementsByTagName("min")[1].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("wind_min_timestamp").innerHTML = timeOnlyConverter(timestamp);
  // day average
  current_val = xmlDoc.getElementsByTagName("day_average")[0].childNodes[0].nodeValue;
  current_val = parseFloat(current_val) ;
  txt = current_val.toFixed(1) + " mph" ;
  document.getElementById("wind_avg").innerHTML = txt;

  // recent stats...
  // max
  current_val = xmlDoc.getElementsByTagName("max_last_10_mins")[0].childNodes[0].nodeValue;
  attrs = xmlDoc.getElementsByTagName("max_last_10_mins")[0].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("wind_max_last10_mins").innerHTML = parseFloat(current_val).toFixed(1) + " mph" ;
  document.getElementById("wind_max_last10_mins_timestamp").innerHTML = timeOnlyConverter(timestamp) ;
  
  current_val = xmlDoc.getElementsByTagName("max_last_hour")[0].childNodes[0].nodeValue;
  attrs = xmlDoc.getElementsByTagName("max_last_hour")[0].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("wind_max_last_hour").innerHTML = parseFloat(current_val).toFixed(1) + " mph" ;
  document.getElementById("wind_max_last_hour_timestamp").innerHTML = timeOnlyConverter(timestamp) ;

  // min
  current_val = xmlDoc.getElementsByTagName("min_last_10_mins")[0].childNodes[0].nodeValue;
  attrs = xmlDoc.getElementsByTagName("min_last_10_mins")[0].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("wind_min_last10_mins").innerHTML = parseFloat(current_val).toFixed(1) + " mph" ;
  document.getElementById("wind_min_last10_mins_timestamp").innerHTML = timeOnlyConverter(timestamp) ;

  current_val = xmlDoc.getElementsByTagName("min_last_hour")[0].childNodes[0].nodeValue;
  attrs = xmlDoc.getElementsByTagName("min_last_hour")[0].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;
  document.getElementById("wind_min_last_hour").innerHTML = parseFloat(current_val).toFixed(1) + " mph" ;
  document.getElementById("wind_min_last_hour_timestamp").innerHTML = timeOnlyConverter(timestamp) ;

  // avg
  current_val = xmlDoc.getElementsByTagName("avg_last_10_mins")[0].childNodes[0].nodeValue;
  document.getElementById("wind_avg_last10_mins").innerHTML = parseFloat(current_val).toFixed(1) + " mph" ;
  current_val = xmlDoc.getElementsByTagName("avg_last_hour")[0].childNodes[0].nodeValue;
  document.getElementById("wind_avg_last_hour").innerHTML = parseFloat(current_val).toFixed(1) + " mph" ;

  // rain
  current_val = xmlDoc.getElementsByTagName("total")[0].childNodes[0].nodeValue;
  current_val = parseFloat(current_val) ;
  attrs = xmlDoc.getElementsByTagName("total")[0].attributes;
  timestamp = attrs.getNamedItem("timestamp").nodeValue ;

  txt = "Current: " + current_val.toFixed(1) + " mm" + timeConverter(timestamp) ;
  document.getElementById("rain_current").innerHTML = txt;
  document.getElementById("rain_total").innerHTML = current_val.toFixed(1) + " mm";
  
  // rain historical - today
  txt = "" ;
  if ( xmlDoc.getElementsByTagName("last_10_mins").length )
  {
    current_val = xmlDoc.getElementsByTagName("last_10_mins")[0].childNodes[0].nodeValue;
    document.getElementById("rain_max_last10_mins").innerHTML = parseFloat(current_val).toFixed(1) + " mm" ; 
  }
  if ( xmlDoc.getElementsByTagName("last_hour").length )
  {
    current_val = xmlDoc.getElementsByTagName("last_hour")[0].childNodes[0].nodeValue;
    document.getElementById("rain_max_last_hour").innerHTML = parseFloat(current_val).toFixed(1) + " mm" ; 
  }
}


</script>

</head>
<body class="detailtext" bgcolor="#FFFFFF" onload="createTimeline()">

<?php

// PHP Utility functions....

// connect to database for the archived stats
$mysqli = new mysqli("localhost", "weather", "weather", "weather") ;
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

// fetch the latest weather info
if ( file_exists('weather.xml') )
{
  $weather_xml = simplexml_load_file('weather.xml');
}

function to_fahrenheit($degrees_c)
{
   return (float)$degrees_c * (9.0/5.0) + 32 ;
}

function formatted_timestamp($timestamp)
{
  return date("H:i:s",(int)$timestamp);
}

function get_rainfall_totals_in_query_results($query_result)
{
  $max = -1 ;
  $min = 10000 ;
  $max_timestamp = 0 ;
  $min_timestamp = 0 ;
  $total = 0 ;
  $count = 0 ;
  while ( $row = $query_result->fetch_assoc())
  {
    $count++ ;
    if ( $row['Total'] > $max )
    {
      $max = $row['Total'] ;
      $max_timestamp = strtotime($row['Date']) ;
    }
    if ( $row['Total'] < $min )
    {
      $min = $row['Total'] ;
      $min_timestamp = strtotime($row['Date']) ;
    }
    $total = $total + $row['Total'] ;
  }
  return array('Count'=>$count,
               'Total'=>$total,
               'Min'=>$min,
               'MinTime'=>$min_timestamp,
               'Max'=>$max,
               'MaxTime'=>$max_timestamp) ;
  
}

// retrieve max/min range values from the returned set of query results
// also now computes average across range (if data in query - wind only)
function get_max_min_in_query_results($query_result)
{
  $max = -100 ;
  $min = 100 ;
  $count = 0 ;
  $max_timestamp = 0 ;
  $min_timestamp = 0 ;
  $sum_avg = 0 ;
  $avg = -1 ;
  
  while ( $row = $query_result->fetch_assoc())
  {
    $count++ ;
    if ( $row['Max'] > $max )
    {
      $max = $row['Max'] ;
      $max_timestamp = strtotime($row['Date'] . " " . $row['MaxTime'] . " UTC" ) ;
    }
    if ( $row['Min'] < $min )
    {
      $min = $row['Min'] ;
      $min_timestamp = strtotime($row['Date'] . " " . $row['MinTime'] . " UTC" ) ;
    }
    if ( isset($row['Average'] ) )
        $sum_avg+=$row['Average'] ;
  }
  
  if ( $count )
      $avg = $sum_avg / $count ;
  
  return array('Count'=>$count,
               'Min'=>$min,
               'MinTime'=>$min_timestamp,
               'Max'=>$max,
               'MaxTime'=>$max_timestamp,
               'Avg'=>$avg) ;
}

function is_leap_year($year) {
	return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
}

function convert_to_timezone($utc_timestamp, $fmt)
{
    $convert_time = date_create($utc_timestamp, timezone_open("UTC")) ;
    date_timezone_set ( $convert_time, timezone_open(date_default_timezone_get()) ) ;
    return date_format ( $convert_time, $fmt ) ;
}

// here we go then....
?>
<h1 align="center" span class="detailheading">Weather @ OnAStick Central</h1>
<h2 align="center" span class="detailheading">Today - Summary</h2>
<p align="center" span class="detailtext"><IMG SRC="today-all-graph.php"></p>

<?php
echo "<h2 align=\"center\" span class=\"detailsubheading\">Page Updated: " . date( "M j, Y, H:i:s T", (int)$weather_xml->temperature->current['timestamp']) . "</h2>" ;
?>
<p align="center" span class="detailtext">Historical figures and summary graph will need a page refresh to update, today's readings, limits should update automatically every few seconds.</p>
<h2 align="center" span class="detailheading">Temperature</h2>
<?php
// display latest - timestamps in the XML are Unix timestamps, so UTC & 'date' displays this correctly for the current timezone
// NOTE: A lot of the "current" stuff rendered here is now obsolete as the Javascript will update it shortly afterwards....
echo "<p align=\"center\" span class=\"detailsubheading\" id=\"temp_current\">Current: " . $weather_xml->temperature->current . "&deg;C / " . 
            to_fahrenheit($weather_xml->temperature->current) . "&deg;F - " .
            date( "M j, Y, H:i:s", (int)$weather_xml->temperature->current['timestamp']) . "</p>" ;
?>
<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="20%">&nbsp;</td>
    <td width="20%" span class="detailsubheading" align="center">Maximum</td>
    <td width="20%" span class="detailsubheading" align="center">Date/Time</td>
    <td width="20%" span class="detailsubheading" align="center">Minimum</td>
    <td width="20%" span class="detailsubheading" align="center">Date/Time</td>
  </tr>
  <tr> 
    <td width="20%"><A HREF="today-temp.php" class="detailtextlink">Today</A></td>
<?php
    // make sure the last set of captured data is in fact today
   $current_date = getdate() ;
   $captured_timeinfo = getdate((int)$weather_xml->temperature->current['timestamp']) ;
   if ( $captured_timeinfo['yday'] == $current_date['yday'] )
   {
     echo "<td width=\"20%\" id=\"temp_max\">" . $weather_xml->temperature->max . "&deg;C / " . 
             to_fahrenheit($weather_xml->temperature->max) . "&deg;F</td>";
     echo "<td width=\"20%\" id=\"temp_max_timestamp\">" . formatted_timestamp($weather_xml->temperature->max['timestamp']) . "</td>";
     echo "<td width=\"20%\" id=\"temp_min\">" . $weather_xml->temperature->min . "&deg;C / " . 
             to_fahrenheit($weather_xml->temperature->min) . "&deg;F</td>";
     echo "<td width=\"20%\" id=\"temp_min_timestamp\">" . formatted_timestamp($weather_xml->temperature->min['timestamp']) . "</td>";
   }
   else
   {
     echo "<td width=\"20%\">Unavailable</td>";
     echo "<td width=\"20%\">Unavailable</td>";
     echo "<td width=\"20%\">Unavailable</td>";
     echo "<td width=\"20%\">Unavailable</td>";
   }
?>
  </tr>
  <tr> 
    <td width="20%">Yesterday</td>
<?php
    // run query to get results from yesterday
    $yesterday = date( "Y-m-d",time() - (24*60*60)) ;
    
    $query = "select * from temperature where date='" . 
      $yesterday . "';" ;
    $result = $mysqli->query($query) ;
    if ( $row = $result->fetch_assoc() )
    {
      echo "<td width=\"20%\">" . $row['Max'] . "&deg;C / " . 
              to_fahrenheit($row['Max']) . "&deg;F</td>";
      
      echo "<td width=\"20%\">" . convert_to_timezone ( $yesterday . " " . $row['MaxTime'], "H:i:s" ) . "</td>";    
      echo "<td width=\"20%\">" . $row['Min'] . "&deg;C / " . 
              to_fahrenheit($row['Min']) . "&deg;F</td>";
      echo "<td width=\"20%\">" . convert_to_timezone ( $yesterday . " " . $row['MinTime'], "H:i:s" ) . "</td>";    
    }
    else
    {
      echo "<td width=\"20%\">Unavailable</td>";
      echo "<td width=\"20%\">Unavailable</td>";
      echo "<td width=\"20%\">Unavailable</td>";
      echo "<td width=\"20%\">Unavailable</td>";
    }
?>
  </tr>
  <tr> 
    <td width="20%"><A HREF="lastweek-temp.php" class="detailtextlink">Last Week</A><?php
   // compute date range for last week - already got "yesterday" so just need to
   // compute the starting point, 7 days before
   $week_start = date( "Y-m-d",time() - (7*24*60*60)) ;
   $query = "select * from temperature where date<='" . 
      $yesterday . "' and date>='" . $week_start . " ';" ;
      
   $result = $mysqli->query($query) ;
   // extract the max/min limits in this range
   $limits = get_max_min_in_query_results($result);
   // check got a full weeks worth & indicate incomplete if not
   if ( $limits['Count'] <7 )
     echo "*" ;
   echo "</td>" ;
   if ( $limits['Count'] > 0 )
   {
     echo "<td width=\"20%\">" . $limits['Max'] . "&deg;C / " . 
             to_fahrenheit($limits['Max']) . "&deg;F</td>";
     echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
     echo "<td width=\"20%\">" . $limits['Min'] . "&deg;C / " . 
             to_fahrenheit($limits['Min']) . "&deg;F</td>";
     echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
   }
   else
   {
     echo "<td width=\"20%\">Unavailable</td>";
     echo "<td width=\"20%\">Unavailable</td>";
     echo "<td width=\"20%\">Unavailable</td>";
     echo "<td width=\"20%\">Unavailable</td>";
   }
?>
  </tr>
  <tr> 
<?php
  
  $date_info = getdate() ;
  
  // days in the month - use this to compute the end date range
  $days_in_month = cal_days_in_month(CAL_GREGORIAN,$date_info["mon"],$date_info["year"]) ;
  
  $this_month = sprintf ( "%02d",$date_info["mon"]) ; 

  echo "<td width=\"20%\"><A HREF=\"month-temp-stats.php?year=" . $date_info["year"] . "&month=" . $this_month . "\" class=\"detailtextlink\">This Month</A>"; 
  
  // the date ranges
  $start_date = $date_info['year'] . "-" . $this_month . "-01" ;
  $end_date = $date_info['year'] . "-" . $this_month . "-" . $days_in_month ;
  $query = "select * from temperature where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_max_min_in_query_results($result);
  if ( $limits['Count'] < $days_in_month )
    echo "*" ;
  echo "</td>" ;
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"20%\">" . $limits['Max'] . "&deg;C / " . 
            to_fahrenheit($limits['Max']) . "&deg;F</td>";
    echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"20%\">" . $limits['Min'] . "&deg;C / " . 
            to_fahrenheit($limits['Min']) . "&deg;F</td>";
    echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
  }
  else
  {
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
  }
?>    
  </tr>
  <tr> 
<?php

  // this is for the last calender month - first determine this and the year
  // which may of course change
  if ( $date_info["mon"] == 1 )
  {
    $last_month = "12" ;
    $year = $date_info["year"] - 1 ;
  }
  else
  {
    $last_month = sprintf ( "%02d", $date_info["mon"] - 1 ) ;
    $year = $date_info["year"] ;
  }

  echo "<td width=\"20%\"><A HREF=\"month-temp-stats.php?year=" . $year . "&month=" . $last_month . "\" class=\"detailtextlink\">Last Month</A>"; 
  
  // days in the month - use this to compute the end date range
  $days_in_month = cal_days_in_month(CAL_GREGORIAN,$last_month,$year) ;
  
  // the date ranges
  $start_date = $year . "-" . $last_month . "-01" ;
  $end_date = $year . "-" . $last_month . "-" . $days_in_month ;
  $query = "select * from temperature where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_max_min_in_query_results($result);
  if ( $limits['Count'] < $days_in_month )
    echo "*" ;
  echo "</td>" ;
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"20%\">" . $limits['Max'] . "&deg;C / " . 
            to_fahrenheit($limits['Max']) . "&deg;F</td>";
    echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"20%\">" . $limits['Min'] . "&deg;C / " . 
            to_fahrenheit($limits['Min']) . "&deg;F</td>";
    echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
  }
  else
  {
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
  }
?>    
  </tr>
  <tr>
<?php
  echo "<td width=\"20%\"><A HREF=\"year-temp-stats.php?year=" . $date_info['year'] . "\" class=\"detailtextlink\">This Year</A>"; 


  // the date ranges
  $start_date = $date_info['year'] . "-01-01" ;
  $end_date = date("Y-m-d") ;
  $query = "select * from temperature where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_max_min_in_query_results($result);
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"20%\">" . $limits['Max'] . "&deg;C / " . 
            to_fahrenheit($limits['Max']) . "&deg;F</td>";
    echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"20%\">" . $limits['Min'] . "&deg;C / " . 
            to_fahrenheit($limits['Min']) . "&deg;F</td>";
    echo "<td width=\"20%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
  }
  else
  {
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
    echo "<td width=\"20%\">Unavailable</td>";
  }
?>    
  </tr>
<?php
    
  $last_year = $date_info['year']-1 ;
  while ( $last_year >= 2018 )
  {
      echo "<tr><td width=\"20%\"><A HREF=\"year-temp-stats.php?year=" . $last_year . "\" class=\"detailtextlink\">" . $last_year . "</A>"; 

      // the date ranges
      $start_date = $last_year . "-01-01" ;
      $end_date = $last_year . "-12-31" ;
      $query = "select * from temperature where date<='" . 
        $end_date . "' and date>='" . $start_date . " ';" ;
      $result = $mysqli->query($query) ;
      // extract the max/min limits in this range
      $limits = get_max_min_in_query_results($result);
      $days_in_year = 365 ;
      if ( is_leap_year($last_year) )
        $days_in_year++ ;
      if ( $limits['Count'] < $days_in_year )
        echo "*" ;
      echo "</td>" ;
      if ( $limits['Count'] > 0 )
      {
        echo "<td width=\"20%\">" . $limits['Max'] . "&deg;C / " . 
                to_fahrenheit($limits['Max']) . "&deg;F</td>";
        echo "<td width=\"20%\">" . date("j M, H:i:s",$limits['MaxTime']) . "</td>";    
        echo "<td width=\"20%\">" . $limits['Min'] . "&deg;C / " . 
                to_fahrenheit($limits['Min']) . "&deg;F</td>";
        echo "<td width=\"20%\">" . date("j M, H:i:s",$limits['MinTime']) . "</td>";    
      }
      else
      {
        echo "<td width=\"20%\">Unavailable</td>";
        echo "<td width=\"20%\">Unavailable</td>";
        echo "<td width=\"20%\">Unavailable</td>";
        echo "<td width=\"20%\">Unavailable</td>";
      }
      $last_year-- ;
      echo "</tr>\n";

  }
?>    
</table>
<p span class="detailtext" align="center"><A HREF="temp-archive.php" class="detailtextlink">Year on Year Comparison</A> - graph and compare temperatures from previous years</p>
<p span class="detailtext" align="center"><A HREF="trend-temp.php" class="detailtextlink">Temperature Trends</A></p>

<h2 align="center" span class="detailheading">Wind Speed</h2>
<?php
// display latest - timestamps in the XML are Unix timestamps, so UTC & 'date' displays this correctly for the current timezone
echo "<p align=\"center\" span class=\"detailsubheading\" id=\"wind_current\">Current: " . number_format((float)$weather_xml->wind_speed->current,1) . " mph, Avg: " . 
    number_format((float)$weather_xml->wind_speed->day_average,1) . " mph - " . date( "M j, Y, H:i:s", (int)$weather_xml->wind_speed->current['timestamp']) . "</p>" ;
?>
<div style = "text-align:center;">
<canvas id="wind_graph" width="650" height="110"></canvas>
</div>
<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="16%">&nbsp;</td>
    <td width="16%" span class="detailsubheading" align="center">Maximum</td>
    <td width="16%" span class="detailsubheading" align="center">Date/Time</td>
    <td width="16%" span class="detailsubheading" align="center">Minimum</td>
    <td width="16%" span class="detailsubheading" align="center">Date/Time</td>
    <td width="16%" span class="detailsubheading" align="center">Average</td>
  </tr>
  <tr>
  <td width="16%">Last 10 minutes</td>
  <td width="16%" id="wind_max_last10_mins">Unavailable</td>
  <td width="16%" id="wind_max_last10_mins_timestamp">Unavailable</td>
  <td width="16%" id="wind_min_last10_mins">Unavailable</td>
  <td width="16%" id="wind_min_last10_mins_timestamp">Unavailable</td>
  <td width="16%" id="wind_avg_last10_mins">Unavailable</td>
  </tr>
  <tr>
  <td width="16%">Last hour</td>
  <td width="16%" id="wind_max_last_hour">Unavailable</td>
  <td width="16%" id="wind_max_last_hour_timestamp">Unavailable</td>
  <td width="16%" id="wind_min_last_hour">Unavailable</td>
  <td width="16%" id="wind_min_last_hour_timestamp">Unavailable</td>
  <td width="16%" id="wind_avg_last_hour">Unavailable</td>
  </tr>
  <tr> 
    <td width="16%"><A HREF="today-wind.php" class="detailtextlink">Today</A></td>
<?php
    // make sure the last set of captured data is in fact today
   $captured_timeinfo = getdate((int)$weather_xml->wind_speed->current['timestamp']) ;
   if ( $captured_timeinfo['yday'] == $current_date['yday'] )
   {
     echo "<td width=\"16%\" id=\"wind_max\">" . number_format((float)$weather_xml->wind_speed->max,1) . " mph</td>";
     echo "<td width=\"16%\" id=\"wind_max_timestamp\">" . formatted_timestamp($weather_xml->wind_speed->max['timestamp']) . "</td>";
     echo "<td width=\"16%\" id=\"wind_min\">" . number_format((float)$weather_xml->wind_speed->min,1) . " mph";
     echo "<td width=\"16%\" id=\"wind_min_timestamp\">" . formatted_timestamp($weather_xml->wind_speed->min['timestamp']) . "</td>";
     echo "<td width=\"16%\" id=\"wind_avg\">" . number_format((float)$weather_xml->wind_speed->day_average,1) . " mph";
   }
   else
   {
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
   }
?>
  </tr>
  <tr> 
    <td width="16%">Yesterday</td>
<?php
    // run query to get results from yesterday
    $query = "select * from wind_speed where date='" . 
      $yesterday . "';" ;
    $result = $mysqli->query($query) ;
    if ( $row = $result->fetch_assoc() )
    {
      echo "<td width=\"16%\">" . $row['Max'] . " mph</td>";
      echo "<td width=\"16%\">" . convert_to_timezone ( $yesterday . " " . $row['MaxTime'], "H:i:s" ) . "</td>";    
      echo "<td width=\"16%\">" . $row['Min'] . " mph</td>";
      echo "<td width=\"16%\">" . convert_to_timezone ( $yesterday . " " . $row['MinTime'], "H:i:s" ) . "</td>";    
      echo "<td width=\"16%\">" . $row['Average'] . " mph</td>";
    }
    else
    {
      echo "<td width=\"16%\">Unavailable</td>";
      echo "<td width=\"16%\">Unavailable</td>";
      echo "<td width=\"16%\">Unavailable</td>";
      echo "<td width=\"16%\">Unavailable</td>";
      echo "<td width=\"16%\">Unavailable</td>";
    }
?>
  </tr>
  <tr> 
    <td width="16%"><A HREF="lastweek-wind.php" class="detailtextlink">Last Week</A><?php
   $query = "select * from wind_speed where date<='" . 
      $yesterday . "' and date>='" . $week_start . " ';" ;
   $result = $mysqli->query($query) ;
   // extract the max/min limits in this range
   $limits = get_max_min_in_query_results($result);
   // check got a full weeks worth & indicate incomplete if not
   if ( $limits['Count'] <7 )
     echo "*" ;
   echo "</td>" ;
   if ( $limits['Count'] > 0 )
   {
     echo "<td width=\"16%\">" . $limits['Max'] . " mph</td>";
     echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
     echo "<td width=\"16%\">" . $limits['Min'] . " mph</td>";
     echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
     echo "<td width=\"16%\">" . number_format((float)$limits['Avg'],1) . " mph</td>";
   }
   else
   {
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
   }
?>
  </tr>
  <tr> 
<?php
  
  $date_info = getdate() ;
  
  // days in the month - use this to compute the end date range
  $days_in_month = cal_days_in_month(CAL_GREGORIAN,$date_info["mon"],$date_info["year"]) ;
  
  $this_month = sprintf ( "%02d",$date_info["mon"]) ; 

  echo "<td width=\"16%\"><A HREF=\"month-wind-stats.php?year=" . $date_info["year"] . "&month=" . $this_month . "\" class=\"detailtextlink\">This Month</A>"; 
  
  // the date ranges
  $start_date = $date_info['year'] . "-" . $this_month . "-01" ;
  $end_date = $date_info['year'] . "-" . $this_month . "-" . $days_in_month ;
  $query = "select * from wind_speed where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_max_min_in_query_results($result);
  if ( $limits['Count'] < $days_in_month )
    echo "*" ;
  echo "</td>" ;
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"16%\">" . $limits['Max'] . " mph</td>";
    echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"16%\">" . $limits['Min'] . " mph</td>";
    echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
    echo "<td width=\"16%\">" . number_format((float)$limits['Avg'],1) . " mph</td>";
  }
  else
  {
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
  }
?>    
  </tr>
  <tr> 
<?php

  // this is for the last calender month - first determine this and the year
  // which may of course change
  if ( $date_info["mon"] == 1 )
  {
    $last_month = "12" ;
    $year = $date_info["year"] - 1 ;
  }
  else
  {
    $last_month = sprintf ( "%02d", $date_info["mon"] - 1 ) ;
    $year = $date_info["year"] ;
  }

  echo "<td width=\"16%\"><A HREF=\"month-wind-stats.php?year=" . $year . "&month=" . $last_month . "\" class=\"detailtextlink\">Last Month</A>"; 
  
  // days in the month - use this to compute the end date range
  $days_in_month = cal_days_in_month(CAL_GREGORIAN,$last_month,$year) ;
  
  // the date ranges
  $start_date = $year . "-" . $last_month . "-01" ;
  $end_date = $year . "-" . $last_month . "-" . $days_in_month ;
  $query = "select * from wind_speed where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_max_min_in_query_results($result);
  if ( $limits['Count'] < $days_in_month )
    echo "*" ;
  echo "</td>" ;
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"16%\">" . $limits['Max'] . " mph</td>";
    echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"16%\">" . $limits['Min'] . " mph</td>";
    echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
    echo "<td width=\"16%\">" . number_format((float)$limits['Avg'],1) . " mph</td>";
  }
  else
  {
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
  }
?>    
  </tr>
  <tr>
<?php
  echo "<td width=\"16%\"><A HREF=\"year-wind-stats.php?year=" . $date_info['year'] . "\" class=\"detailtextlink\">This Year</A>"; 
  // the date ranges
  $start_date = $date_info['year'] . "-01-01" ;
  $end_date = date("Y-m-d") ;
  $query = "select * from wind_speed where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_max_min_in_query_results($result);
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"16%\">" . $limits['Max'] . " mph</td>";
    echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"16%\">" . $limits['Min'] . " mph</td>";
    echo "<td width=\"16%\">" . date("D jS M, H:i:s",$limits['MinTime']) . "</td>";    
    echo "<td width=\"16%\">" . number_format((float)$limits['Avg'],1) . " mph</td>";
  }
  else
  {
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
  }
?>    
  </tr>
<?php
  $last_year = $date_info['year']-1 ;
  while ( $last_year >= 2018 )
  {
      echo "<tr><td width=\"16%\"><A HREF=\"year-wind-stats.php?year=" . $last_year . "\" class=\"detailtextlink\">" . $last_year . "</A>"; 
      
      // the date ranges
      $start_date = $last_year . "-01-01" ;
      $end_date = $last_year . "-12-31" ;
      $query = "select * from wind_speed where date<='" . 
        $end_date . "' and date>='" . $start_date . " ';" ;
      $result = $mysqli->query($query) ;
      // extract the max/min limits in this range
      $limits = get_max_min_in_query_results($result);
      $days_in_year = 365 ;
      if ( is_leap_year($last_year) )
        $days_in_year++ ;
      if ( $limits['Count'] < $days_in_year )
        echo "*" ;
      echo "</td>" ;
      if ( $limits['Count'] > 0 )
      {
        echo "<td width=\"16%\">" . $limits['Max'] . " mph</td>";
        echo "<td width=\"16%\">" . date("j M, H:i:s",$limits['MaxTime']) . "</td>";    
        echo "<td width=\"16%\">" . $limits['Min'] . " mph</td>";
        echo "<td width=\"16%\">" . date("j M, H:i:s",$limits['MinTime']) . "</td>";    
        echo "<td width=\"16%\">" . number_format((float)$limits['Avg'],1) . " mph</td>";
      }
      else
      {
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
      }
      echo "</tr>\n" ;  
      $last_year-- ;
  }
?>    
</table>
<h2 align="center" span class="detailheading">Rainfall</h2>
<?php
// display latest - timestamps in the XML are Unix timestamps, so UTC & 'date' displays this correctly for the current timezone
echo "<p align=\"center\" span class=\"detailsubheading\" id=\"rain_current\">Current: " . number_format((float)$weather_xml->rain->total,1) . " mm - " . date( "M j, Y, H:i:s", (int)$weather_xml->rain->total['timestamp']) . "</p>" ;
?>

<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="16%">&nbsp;</td>
    <td width="16%" span class="detailsubheading" align="center">Total</td>
    <td width="16%" span class="detailsubheading" align="center">Maximum</td>
    <td width="16%" span class="detailsubheading" align="center">Date</td>
    <td width="16%" span class="detailsubheading" align="center">Minimum</td>
    <td width="16%" span class="detailsubheading" align="center">Date</td>
  </tr>
  <tr>
  <td width="16%">Last 10 minutes</td>
  <td width="16%" id="rain_max_last10_mins">Unavailable</td>
  <td width="16%" align="center">-</td>
  <td width="16%" align="center">-</td>
  <td width="16%" align="center">-</td>
  <td width="16%" align="center">-</td>
  </tr>
  <tr>
  <td width="20%">Last hour</td>
  <td width="16%" id="rain_max_last_hour">Unavailable</td>
  <td width="16%" align="center">-</td>
  <td width="16%" align="center">-</td>
  <td width="16%" align="center">-</td>
  <td width="16%" align="center">-</td>
  </tr>
  <tr> 
    <td width="16%"><A HREF="today-rainfall.php" class="detailtextlink">Today</A></td>
<?php
    // make sure the last set of captured data is in fact today
   $current_date = getdate() ;
   $captured_timeinfo = getdate((int)$weather_xml->rain->total['timestamp']) ;
   if ( $captured_timeinfo['yday'] == $current_date['yday'] )
   {
     echo "<td width=\"16%\" id=\"rain_total\">" . number_format((float)$weather_xml->rain->total,1) . " mm</td>";
   }
   else
   {
     echo "<td width=\"16%\">Unavailable</td>";
   }
?>
  <td align="center">-</td><td align="center">-</td><td align="center">-</td><td align="center">-</td>
  </tr>
  <tr> 
    <td width="16%">Yesterday</td>
<?php
    // run query to get results from yesterday
    $query = "select Total from rainfall where date='" . 
      $yesterday . "';" ;
    $result = $mysqli->query($query) ;
    if ( $row = $result->fetch_assoc() )
    {
      echo "<td width=\"16%\">" . $row['Total'] . " mm</td>";
    }
    else
    {
      echo "<td width=\"16%\">Unavailable</td>";
    }
?>
  <td align="center">-</td><td align="center">-</td><td align="center">-</td><td align="center">-</td>
  </tr>
  <tr>
    <td width="16%"><A HREF="lastweek-rain.php" class="detailtextlink">Last Week</A><?php
   $query = "select * from rainfall where date<='" . 
      $yesterday . "' and date>='" . $week_start . " ';" ;
   $result = $mysqli->query($query) ;
   $limits = get_rainfall_totals_in_query_results($result);
   
   // check got a full weeks worth & indicate incomplete if not
   if ( $limits['Count'] <7 )
     echo "*" ;
   echo "</td>" ;
   if ( $limits['Count'] > 0 )
   {
     echo "<td width=\"16%\">" . $limits['Total'] . " mm</td>";
     echo "<td width=\"16%\">" . $limits['Max'] . " mm</td>";
     echo "<td width=\"16%\">" . date("D jS M",$limits['MaxTime']) . "</td>";    
     echo "<td width=\"16%\">" . $limits['Min'] . " mm</td>";
     echo "<td width=\"16%\">" . date("D jS M",$limits['MinTime']) . "</td>";    
   }
   else
   {
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
     echo "<td width=\"16%\">Unavailable</td>";
   }   
?>    
    </tr>
    <tr>
<?php
  
  $date_info = getdate() ;
  
  // days in the month - use this to compute the end date range
  $days_in_month = cal_days_in_month(CAL_GREGORIAN,$date_info["mon"],$date_info["year"]) ;
  
  $this_month = sprintf ( "%02d",$date_info["mon"]) ; 

  echo "<td width=\"20%\"><A HREF=\"month-rain-stats.php?year=" . $date_info["year"] . "&month=" . $this_month . "\" class=\"detailtextlink\">This Month</A>"; 
  
  // the date ranges
  $start_date = $date_info['year'] . "-" . $this_month . "-01" ;
  $end_date = $date_info['year'] . "-" . $this_month . "-" . $days_in_month ;
  $query = "select * from rainfall where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_rainfall_totals_in_query_results($result);
  if ( $limits['Count'] < $days_in_month )
    echo "*" ;
  echo "</td>" ;
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"16%\">" . $limits['Total'] . " mm</td>";
    echo "<td width=\"16%\">" . $limits['Max'] . " mm</td>";
    echo "<td width=\"16%\">" . date("D jS M",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"16%\">" . $limits['Min'] . " mm</td>";
    echo "<td width=\"16%\">" . date("D jS M",$limits['MinTime']) . "</td>";    
  }
  else
  {
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
  }   
?>    
  </tr>
  <tr> 
<?php

  // this is for the last calender month - first determine this and the year
  // which may of course change
  if ( $date_info["mon"] == 1 )
  {
    $last_month = "12" ;
    $year = $date_info["year"] - 1 ;
  }
  else
  {
    $last_month = sprintf ( "%02d", $date_info["mon"] - 1 ) ;
    $year = $date_info["year"] ;
  }

  echo "<td width=\"20%\"><A HREF=\"month-rain-stats.php?year=" . $year . "&month=" . $last_month . "\" class=\"detailtextlink\">Last Month</A>"; 
  
  // days in the month - use this to compute the end date range
  $days_in_month = cal_days_in_month(CAL_GREGORIAN,$last_month,$year) ;
  
  // the date ranges
  $start_date = $year . "-" . $last_month . "-01" ;
  $end_date = $year . "-" . $last_month . "-" . $days_in_month ;
  $query = "select * from rainfall where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_rainfall_totals_in_query_results($result);
  if ( $limits['Count'] < $days_in_month )
    echo "*" ;
  echo "</td>" ;
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"16%\">" . $limits['Total'] . " mm</td>";
    echo "<td width=\"16%\">" . $limits['Max'] . " mm</td>";
    echo "<td width=\"16%\">" . date("D jS M",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"16%\">" . $limits['Min'] . " mm</td>";
    echo "<td width=\"16%\">" . date("D jS M",$limits['MinTime']) . "</td>";    
  }
  else
  {
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
  }   
?>    
  </tr>
  <tr>
<?php
  echo "<td width=\"20%\"><A HREF=\"year-rain-stats.php?year=" . $date_info['year'] . "\" class=\"detailtextlink\">This Year</A>"; 
  // the date ranges
  $start_date = $date_info['year'] . "-01-01" ;
  $end_date = date("Y-m-d") ;
  $query = "select * from rainfall where date<='" . 
    $end_date . "' and date>='" . $start_date . " ';" ;
  $result = $mysqli->query($query) ;
  // extract the max/min limits in this range
  $limits = get_rainfall_totals_in_query_results($result);
  if ( $limits['Count'] > 0 )
  {
    echo "<td width=\"16%\">" . $limits['Total'] . " mm</td>";
    echo "<td width=\"16%\">" . $limits['Max'] . " mm</td>";
    echo "<td width=\"16%\">" . date("D jS M",$limits['MaxTime']) . "</td>";    
    echo "<td width=\"16%\">" . $limits['Min'] . " mm</td>";
    echo "<td width=\"16%\">" . date("D jS M",$limits['MinTime']) . "</td>";    
  }
  else
  {
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
    echo "<td width=\"16%\">Unavailable</td>";
  }
?>    
  </tr>
<?php

  $last_year = $date_info['year']-1 ;
  while ( $last_year >= 2018 )
  {
      echo "<tr><td width=\"20%\"><A HREF=\"year-rain-stats.php?year=" . $last_year . "\" class=\"detailtextlink\">" . $last_year . "</A>"; 
        
      // the date ranges
      $start_date = $last_year . "-01-01" ;
      $end_date = $last_year . "-12-31" ;
      $query = "select * from rainfall where date<='" . 
        $end_date . "' and date>='" . $start_date . " ';" ;
      $result = $mysqli->query($query) ;
      // extract the max/min limits in this range
      $limits = get_rainfall_totals_in_query_results($result);
      $days_in_year = 365 ;
      if ( is_leap_year($last_year) )
        $days_in_year++ ;
      if ( $limits['Count'] < $days_in_year )
        echo "*" ;
      echo "</td>" ;
      if ( $limits['Count'] > 0 )
      {
        echo "<td width=\"16%\">" . $limits['Total'] . " mm</td>";
        echo "<td width=\"16%\">" . $limits['Max'] . " mm</td>";
        echo "<td width=\"16%\">" . date("D jS M",$limits['MaxTime']) . "</td>";    
        echo "<td width=\"16%\">" . $limits['Min'] . " mm</td>";
        echo "<td width=\"16%\">" . date("D jS M",$limits['MinTime']) . "</td>";    
      }
      else
      {
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
        echo "<td width=\"16%\">Unavailable</td>";
      }
      echo "</tr>\n";
      $last_year-- ;
  }
?>    
    
</table>

<p align="center" span class="detailtext">*Incomplete data available</p>

<p align="center"><span class="footertext">&copy;2018-2025 <a href="http://www.onasticksoftware.co.uk" class="detailtextlink">OnAStickSoftware</a></span></p>

</body>
</html>
