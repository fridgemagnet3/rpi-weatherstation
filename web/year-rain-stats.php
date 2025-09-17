<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css"></style>
</head>
<body class="detailsubheading">

<?php
// compute date range for entire year
$start_date = $_GET["year"] . "-01-01" ;
$end_date = $_GET["year"] . "-12-31" ;
// use this to give us a title
echo "<h1 align=\"center\">" . $_GET["year"] . "</h1>";
echo "<p align=\"center\">" ;
echo "<IMG SRC=\"year-rain-stats-graph.php?&year=" . $_GET["year"] . "\"/>" ;
echo "</p>" ;

?>

<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="25%">&nbsp;</td>
    <td width="25%" span class="detailsubheading">Maximum</td>
    <td width="25%" span class="detailsubheading">Date/Time</td>
    <td width="25%" span class="detailsubheading">Total</td>
  </tr>
  <tr> 
<?php

function display_results($month,$max,$max_time,$total,$days)
{
    $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$_GET["year"]) ;
    $month_str = date("F",strtotime("01-" . $month . "-" . $_GET["year"] )) ;
    echo "<tr><td width=\"25%\"><A HREF=\"month-rain-stats.php?year=" . $_GET["year"] . "&month=" . $month . "\" class=\"detailtextlink\">$month_str</A>"; 
    if ( $days < $days_in_month )
        echo "*" ;
    echo "</td>" ;
    echo "<td width=\"25%\">" . $max . " mm</td>" ; 
    echo "<td width=\"25%\">" . date("l, jS",$max_time) . "</td>" ; 
    echo "<td width=\"25%\">" . $total . " mm</td>" ; 
}

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
$first_month = true ;
$month = -1 ;

while ( $row = $result->fetch_assoc())
{
    // track month by month...
    $timestamp = getdate(strtotime($row['Date'])) ;
    if ( $timestamp['mon'] != $month )
    {
        // on transition, display the max/min for each month
        if ( !$first_month )
           display_results($month,$max_rain,$max_time,$total,$days);
        // reset counters etc
        $month = $timestamp['mon'] ;
        $max_rain = -100 ;
        $days = 0 ;
        $first_month = false ;
        $total = 0 ;
    }
    // track limits for the month
    if ( $row['Total'] > $max_rain )
    {
        $max_rain = $row['Total'] ;
        $max_time = strtotime($row['Date'] . " " . $row['MaxTime']) ;
    }
    $days++ ;
    $total=$total+ $row['Total'] ;
}
if ( !$first_month )
    display_results($month,$max_rain,$max_time,$total,$days);

?>
</table>
<p align="center" span class="detailtext">*Incomplete data available</p>
</body>
</html>

