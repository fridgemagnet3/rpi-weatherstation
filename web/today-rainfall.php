<html>
<head>
<title>Rain - Today</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css">
</style>

</head>
<body class="detailtext" bgcolor="#FFFFFF">
<?php
// fetch the latest weather info
if ( file_exists('weather.xml') )
{
  $weather_xml = simplexml_load_file('weather.xml');
}
?>
<h1 align="center" span class="detailheading">Today</h1>
<p span class="detailtext" align="center">Rainfaill is from the preceding hour</p>
<p align="center">
<IMG SRC="today-rain-graph.php"/>
</p>
<p align="center">Note: The rain gauge is one of the <A HREF="https://en.wikipedia.org/wiki/Rain_gauge#Tipping_bucket_rain_gauge"><span class="detailtextlink">tipping bucket variety</A> as
 a result readings only update when the bucket is full. This can yield misleading results particularly over light rainfall</p>
<table width="50%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="33%" align="center" span class="detailsubheading">Time</td>
    <td width="33%" align="center" span class="detailsubheading">Rainfall Per Hour</td>
    <td width="33%" align="center" span class="detailsubheading">Rainfall Total</td>
  </tr>
<?php
    // just dump the hourly readings from the xml
    $rain_this_hour = 0.0;
    foreach($weather_xml->rain->hourly_readings->hour as $hourly_reading)
    {
        echo "<tr><td width=\"33%\" align=\"center\">" . $hourly_reading['value'] . ":00</td>" ;
        $rain_this_hour = (float)$hourly_reading - $rain_this_hour;
        echo "<td width=\"33%\" align=\"center\">" . number_format($rain_this_hour,1) . " mm</td>";
        $rain_this_hour = (float)$hourly_reading ;
        echo "<td width=\"33%\" align=\"center\">" . number_format((float)$hourly_reading,1) . " mm</td>";
    }
?>
</table>
</body>
</html>

