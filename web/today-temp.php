<html>
<head>
<title>Temperatures - Today</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css">
</style>

</head>
<body class="detailsubheading" bgcolor="#FFFFFF">
<?php
// fetch the latest weather info
if ( file_exists('weather.xml') )
{
  $weather_xml = simplexml_load_file('weather.xml');
}

function to_fahrenheit($degrees_c)
{
   return (float)$degrees_c * (9.0/5.0) + 32 ;
}

?>
<h1 align="center" span class="detailheading">Today</h1>
<p align="center">
<IMG SRC="today-temp-graph.php"/>
</p>

<table width="40%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="50%" align="center" span class="detailsubheading">Time</td>
    <td width="50%" align="center" span class="detailsubheading">Temperature</td>
  </tr>
<?php
    // just dump the hourly readings from the xml
    foreach($weather_xml->temperature->hourly_readings->hour as $hourly_reading)
    {
        echo "<tr><td width=\"50%\" align=\"center\">" . $hourly_reading['value'] . ":00</td>" ;
        echo "<td width=\"50%\" align=\"center\">" . $hourly_reading . "&deg;C / " . 
             to_fahrenheit($hourly_reading) . "&deg;F</td>";
    }
?>
</table>
</body>
</html>

