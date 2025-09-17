<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css"></style>
</head>
<body class="detailsubheading">
<h1 align="center" span class="detailheading">Last Week</h1>
<p align="center">
<IMG SRC="lastweek-temp-graph.php"/>
</p>
<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="20%">&nbsp;</td>
    <td width="20%" span class="detailsubheading">Maximum</td>
    <td width="20%" span class="detailsubheading">Date/Time</td>
    <td width="20%" span class="detailsubheading">Minimum</td>
    <td width="20%" span class="detailsubheading">Date/Time</td>
  </tr>
  <tr> 
<?php

function to_fahrenheit($degrees_c)
{
   return (int)((float)$degrees_c * (9.0/5.0) + 32) ;
}

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
$query = "select * from temperature where date<='" . 
  $week_end . "' and date>='" . $week_start . " ';" ;
$result = $mysqli->query($query) ;

while ( $row = $result->fetch_assoc())
{
    echo "<tr><td width=\"20%\">" . date("l, jS M",strtotime($row['Date']) . " UTC") . "</td>" ; 
    echo "<td width=\"20%\">" . $row['Max'] . "&deg;C / " . 
             to_fahrenheit($row['Max']) . "&deg;F</td>" ; 
    echo "<td width=\"20%\">" . date("H:i:s",strtotime($row['Date'] . " " . $row['MaxTime'] . " UTC")) . "</td>" ; 
    echo "<td width=\"20%\">" . $row['Min'] . "&deg;C/ " . 
             to_fahrenheit($row['Min']) . "&deg;F</td>" ;  
    echo "<td width=\"20%\">" . date("H:i:s",strtotime($row['Date'] . " " . $row['MinTime'] . " UTC")) . "</td></tr>" ; 
}

?>
</table>

<h2 align="center">&nbsp;</h2>
<p>&nbsp; </p>
</body>
</html>

