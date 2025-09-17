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
echo "<IMG SRC=\"year-temp-stats-graph.php?&year=" . $_GET["year"] . "\"/>" ;
echo "</p>" ;

?>

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

function display_results($month,$min,$max,$min_time,$max_time,$days)
{
    $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$_GET["year"]) ;
    $month_str = date("F",strtotime("01-" . $month . "-" . $_GET["year"] )) ;
    echo "<tr><td width=\"20%\"><A HREF=\"month-temp-stats.php?year=" . $_GET["year"] . "&month=" . $month . "\" class=\"detailtextlink\">$month_str</A>"; 
    if ( $days < $days_in_month )
        echo "*" ;
    echo "</td>" ;
    echo "<td width=\"20%\">" . $max . "&deg;C / " . 
             to_fahrenheit($max) . "&deg;F</td>" ; 
    echo "<td width=\"20%\">" . date("l, jS H:i:s",$max_time) . "</td>" ; 
    echo "<td width=\"20%\">" . $min . "&deg;C/ " . 
             to_fahrenheit($min) . "&deg;F</td>" ;  
    echo "<td width=\"20%\">" . date("l, jS H:i:s",$min_time) . "</td></tr>" ; 
}

// connect to database for the archived stats
$mysqli = new mysqli("localhost", "weather", "weather", "weather") ;
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

// compute and run the query
$query = "select * from temperature where date<='" . 
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
           display_results($month,$min_temp,$max_temp,$min_time,$max_time,$days);
        // reset counters etc
        $month = $timestamp['mon'] ;
        $min_temp = 100 ;
        $max_temp = -100 ;
        $days = 0 ;
        $first_month = false ;
    }
    // track limits for the month
    if ( $row['Min'] < $min_temp )
    {
        $min_temp = $row['Min'] ;
        $min_time = strtotime($row['Date'] . " " . $row['MinTime'] . " UTC");
    }
    if ( $row['Max'] > $max_temp )
    {
        $max_temp = $row['Max'] ;
        $max_time = strtotime($row['Date'] . " " . $row['MaxTime'] . " UTC") ;
    }
    $days++ ;
}
if ( !$first_month )
    display_results($month,$min_temp,$max_temp,$min_time,$max_time,$days);

?>
</table>
<p align="center" span class="detailtext">*Incomplete data available</p>
</body>
</html>

