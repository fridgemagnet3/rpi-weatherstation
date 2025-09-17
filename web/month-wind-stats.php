<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css"></style>
</head>
<body class="detailsubheading">

<?php
// compute date range
$start_date = $_GET["year"] . "-" . $_GET["month"] . "-01" ;
$days_in_month = cal_days_in_month(CAL_GREGORIAN,$_GET["month"],$_GET["year"]) ;
$end_date = $_GET["year"] . "-" . $_GET["month"] . "-" . $days_in_month ;
// use this to give us a title
echo "<h1 align=\"center\">" . date("F",strtotime($start_date)) . " " . $_GET["year"] . "</h1>";
echo "<p align=\"center\">" ;
echo "<IMG SRC=\"month-wind-stats-graph.php?month=". $_GET["month"] . "&year=" . $_GET["year"] . "\"/>" ;
echo "</p>" ;

?>

<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="16%" span class="detailsubheading">&nbsp;</td>
    <td width="16%" span class="detailsubheading">Maximum</td>
    <td width="16%" span class="detailsubheading">Date/Time</td>
    <td width="16%" span class="detailsubheading">Minimum</td>
    <td width="16%" span class="detailsubheading">Date/Time</td>
    <td width="16%" span class="detailsubheading">Average</td>
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

// compute and run the query
$query = "select * from wind_speed where date<='" . 
  $end_date . "' and date>='" . $start_date . " ';" ;
$result = $mysqli->query($query) ;

while ( $row = $result->fetch_assoc())
{
    echo "<tr><td width=\"16%\">" . date("D, j M",strtotime($row['Date'])) . "</td>" ; 
    echo "<td width=\"16%\">" . $row['Max'] . " mph</td>" ; 
    echo "<td width=\"16%\">" . date("H:i:s",strtotime($row['Date'] . " " . $row['MaxTime'] . " UTC")) . "</td>" ; 
    echo "<td width=\"16%\">" . $row['Min'] . " mph</td>" ;  
    echo "<td width=\"16%\">" . date("H:i:s",strtotime($row['Date'] . " " . $row['MinTime'] . " UTC")) . "</td>" ; 
    echo "<td width=\"16%\">" . $row['Average'] . " mph</td></tr>" ;  
}

?>
</table>

<h2 align="center">&nbsp;</h2>
<p>&nbsp; </p>
</body>
</html>

