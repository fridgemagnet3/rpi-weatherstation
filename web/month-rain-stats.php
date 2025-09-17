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
echo "<IMG SRC=\"month-rain-stats-graph.php?month=". $_GET["month"] . "&year=" . $_GET["year"] . "\"/>" ;
echo "</p>" ;

?>

<table width="40%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="50%" span class="detailsubheading">&nbsp;</td>
    <td width="50%" span class="detailsubheading">Total</td>
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
$query = "select * from rainfall where date<='" . 
  $end_date . "' and date>='" . $start_date . " ';" ;
$result = $mysqli->query($query) ;

while ( $row = $result->fetch_assoc())
{
    echo "<tr><td width=\"50%\">" . date("D, j M",strtotime($row['Date'])) . "</td>" ; 
    echo "<td width=\"50%\">" . $row['Total'] . " mm</td>" ; 
}

?>
</table>

<h2 align="center">&nbsp;</h2>
<p>&nbsp; </p>
</body>
</html>

