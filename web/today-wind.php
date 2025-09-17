<html>
<head>
<title>Wind Speed - Today</title>
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

?>
<h1 align="center" span class="detailheading">Today</h1>
<h2 align="center" span class="detailsubheading">Wind speeds are taken from the previous hour</h1>

<p align="center">
<IMG SRC="today-wind-graph.php"/>
</p>
<table width="40%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="33%" align="center" span class="detailsubheading">Time</td>
    <td width="33%" align="center" span class="detailsubheading">Avg Wind Speed</td>
    <td width="33%" align="center" span class="detailsubheading">Max Wind Speed</td>
  </tr>
<?php
    // just dump the hourly readings from the xml
    foreach($weather_xml->wind_speed->hourly_readings->hour as $hourly_reading)
    {
        echo "<tr><td width=\"33%\" align=\"center\">" . $hourly_reading['value'] . ":00</td>" ;
        echo "<td width=\"33%\" align=\"center\">" . number_format((float)$hourly_reading,1) . " mph";
        echo "<td width=\"33%\" align=\"center\">" . number_format((float)$hourly_reading['max'],1) . " mph";
    }
?>
</table>
</body>
</html>

