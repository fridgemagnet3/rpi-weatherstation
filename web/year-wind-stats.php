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
echo "<IMG SRC=\"year-wind-stats-graph.php?&year=" . $_GET["year"] . "\"/>" ;
echo "</p>" ;

?>

<table width="66%" border="1" align="center" span class="detailtext">
  <tr> 
    <td width="25%">&nbsp;</td>
    <td width="25%" span class="detailsubheading">Maximum</td>
    <td width="25%" span class="detailsubheading">Date/Time</td>
    <td width="25%" span class="detailsubheading">Average</td>
  </tr>
  <tr> 
<?php

function display_results($month,$max_speed,$max_time,$avg_speed_cum,$days)
{
    $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$_GET["year"]) ;
    $month_str = date("F",strtotime("01-" . $month . "-" . $_GET["year"] )) ;
    echo "<tr><td width=\"25%\"><A HREF=\"month-wind-stats.php?year=" . $_GET["year"] . "&month=" . $month . "\" class=\"detailtextlink\">$month_str</A>"; 
    if ( $days < $days_in_month )
        echo "*" ;
    echo "</td>" ;
    echo "<td width=\"25%\">" . $max_speed . " mph</td>" ; 
    echo "<td width=\"25%\">" . date("l, jS H:i:s",$max_time) . "</td>" ; 
    echo "<td width=\"25%\">" . round($avg_speed_cum/$days,1) . " mph</td></tr>" ;  
}

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
           display_results($month,$max_speed,$max_time,$avg_speed_cum,$days);
        // reset counters etc
        $month = $timestamp['mon'] ;
        $max_speed = -100 ;
        $avg_speed_cum = 0 ;
        $days = 0 ;
        $first_month = false ;
    }
    // track limits for the month
    if ( $row['Max'] > $max_speed )
    {
        $max_speed = $row['Max'] ;
        $max_time = strtotime($row['Date'] . " " . $row['MaxTime'] . " UTC") ;
    }
    $avg_speed_cum = $avg_speed_cum + $row['Average'] ;
    $days++ ;
}
if ( !$first_month )
     display_results($month,$max_speed,$max_time,$avg_speed_cum,$days);

?>
</table>
<p align="center" span class="detailtext">*Incomplete data available</p>
</body>
</html>

