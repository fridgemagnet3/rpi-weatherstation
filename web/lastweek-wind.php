<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css"></style>
</head>
<body class="detailtext">
<h1 align="center" span class="detailheading">Last Week</h1>
<p align="center">
<IMG SRC="lastweek-wind-graph.php"/>
</p>
<p><b>Maximum</b> is the peak wind speed measured on a given day.</p>
<p><b>Average</b> is the average wind speed measured throughout the day. Since the wind is rarely constant often gusting then dropping to very little, this can yield a relatively low figure even on windy days.</p>
<p><b>Max Average</b> is the average of the maximum wind speeds measured per hour each day. This can give a better guide to how "windy" the day actually was. Note that this information has only been computed since 9th November</p>
 
<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="20%">&nbsp;</td>
    <td width="20%" span class="detailsubheading">Maximum</td>
    <td width="20%" span class="detailsubheading">Date/Time</td>
    <td width="20%" span class="detailsubheading">Average</td>
    <td width="20%" span class="detailsubheading">Max Average</td>
  </tr>
  <tr> 
<?php

// connect to database for the archived stats
$mysqli = new mysqli("localhost", "weather", "weather", "weather") ;
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

// compute date range for last week 
$week_start = date( "Y-m-d",time() - (7*24*60*60)) ;
$week_end = date( "Y-m-d",time() - (24*60*60)) ;
$query = "select * from wind_speed where date<='" . 
  $week_end . "' and date>='" . $week_start . " ';" ;
$result = $mysqli->query($query) ;

while ( $row = $result->fetch_assoc())
{
    echo "<tr><td width=\"20%\">" . date("l, jS M",strtotime($row['Date'])) . "</td>" ; 
    echo "<td width=\"20%\">" . $row['Max'] . " mph</td>" ; 
    echo "<td width=\"20%\">" . date("H:i:s",strtotime($row['Date'] . " " . $row['MaxTime'] . " UTC")) . "</td>" ; 
    echo "<td width=\"20%\">" . $row['Average'] . " mph</td>" ; 
    echo "<td width=\"20%\">" . $row['MaxAvg'] . " mph</td></tr>" ; 
}

?>
</table>

<h2 align="center">&nbsp;</h2>
<p>&nbsp; </p>
</body>
</html>

