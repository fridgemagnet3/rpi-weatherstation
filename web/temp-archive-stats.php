<!DOCTYPE html>
<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css"></style>
</head>

<body class="detailtext">

<?php

if ( !isset($_POST["year"]) || !isset($_POST["month"]))
    die("No form values") ;
if ( !is_array($_POST["year"]))
    die("Invalid form values") ;
if ( $_POST["month"] < 0 || $_POST["month"] > 12 )
    die("Invalid month" ) ;

$month_lookup = array( 1=> "Jan", 2=> "Feb", 3=> "Mar", 4=> "Apr", 5=> "May", 6=> "Jun", 7=> "Jul", 8=> "Aug", 9=> "Sep", 10=> "Oct", 11 => "Nov", 12=> "Dec" ) ;

function is_leap_year($year) {
	return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
}

function to_fahrenheit($degrees_c)
{
   return (float)$degrees_c * (9.0/5.0) + 32 ;
}


// connect to database for the archived stats
$mysqli = new mysqli("localhost", "weather", "weather", "weather") ;
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

// array of years of interest
$temp_years = $_POST["year"] ;
// month of interest
$the_month = $_POST["month"] ;

echo "<h1 align=\"center\">Temperature Archive</h1>";
echo "<p align=\"center\">" ;
// generate then output URL for generating graph
$qs = http_build_query($temp_years) ;
echo "<IMG SRC=\"temp-archive-stats-graph.php?month=" . $the_month . "&" . $qs . "\"/>" ;
echo "</p>" ;

// if zero, means entire year
if ( !$the_month )
{
    $compute_year = True ;
    $the_month = 1 ;
    $month_end = 12 ;
}
else
{
    $compute_year = False ;
    $month_end = $the_month ;
    // just use first year as basis for this, the only time it'll differ is for a leap year
    // and not going to worry about missing the one day here
    $days_in_month = cal_days_in_month(CAL_GREGORIAN,$the_month,$temp_years[0]) ;
}

// generate the start of the table
echo "<table width=\"100%\" border=\"1\" align=\"center\" span class=\"detailtext\">\n" ;
echo "  <tr>\n";
echo "    <td span class=\"detailsubheading\">&nbsp;</td>\n" ;


// multi-dimensional array which will hold the temperature data from the db
$temp_stats = array() ;
// no. of years to process
$year_count = count($temp_years);

for ( $idx=0 ; $idx < $year_count ; $idx++ )
{
    $the_year = $temp_years[$idx] ;
    $temp_stats[$the_year] = array( "Temp1" => array(),
                                    "Temp2" => array() );

    // compute date range for query
    $start_date = $the_year . "-" . $the_month . "-01" ;
    if ( $compute_year )
        $end_date = $the_year . "-12-31" ;
    else
        $end_date = $the_year . "-" . $the_month . "-" . $days_in_month ;
    if ( $temp_years[$idx] < 2018 )
    {
        // output the table header
        echo "    <td span class=\"detailsubheading\">" . $the_year ."</td>\n" ;
        // run the query 
        $query = "select * from temperature_archive where date<='" . $end_date . "' and date>='" . $start_date . " ';" ;
        $result = $mysqli->query($query) ;
        while ( $row = $result->fetch_assoc())
        {
            $temp_stats[$the_year]['Temp1'][$row['Date']] = $row['Temp'] ;
        }
    }
    else
    {
        // output the table header
        echo "    <td span class=\"detailsubheading\">" . $the_year ." min</td>\n" ;
        echo "    <td span class=\"detailsubheading\">" . $the_year ." max</td>\n" ;
        // run the query
        $query = "select * from temperature where date<='" . $end_date . "' and date>='" . $start_date . " ';" ;
        $result = $mysqli->query($query) ;
        while ( $row = $result->fetch_assoc())
        {
            $temp_stats[$the_year]['Temp1'][$row['Date']] = $row['Min'] ;
            $temp_stats[$the_year]['Temp2'][$row['Date']] = $row['Max'] ;
        }
    }
}

// complete the table header
echo "  </tr>\n";

// now generate the table contents
// if we're just generating a single month, this loop will only execute once
for ( $month=$the_month ; $month <=$month_end ; $month++ )
{
    $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$temp_years[0]) ;

    for ( $mday=1 ; $mday <= $days_in_month ; $mday++ )
    {
        echo "  <tr>\n";
        // output calender date
        echo "      <td>" . $mday . " " . $month_lookup[$month] . "</td>\n";
        for ( $idx=0 ; $idx < $year_count ; $idx++ )
        {
            // lookup the date in the temperature data retrieved from the database
            // include it in the table if it's available
            $the_year = $temp_years[$idx] ;
            $the_date = sprintf( "%04d-%02d-%02d", $the_year, $month, $mday) ;
            if ( array_key_exists($the_date,$temp_stats[$the_year]['Temp1']) )
            {
                echo "      <td>" . $temp_stats[$the_year]['Temp1'][$the_date] . "&deg;C / " . to_fahrenheit($temp_stats[$the_year]['Temp1'][$the_date]) . "&deg;F</td>\n";
                if ( $the_year >= 2018 )
                    echo "      <td>" . $temp_stats[$the_year]['Temp2'][$the_date] . "&deg;C / " . to_fahrenheit($temp_stats[$the_year]['Temp2'][$the_date]) . "&deg;F</td>\n";
            }
            else
            {
                echo "      <td>No data</td>\n";
                if ( $the_year >= 2018 )
                    echo "      <td>No data</td>\n";
            }
        }
        echo "  </tr>\n";
    }
}

echo "</table>\n"; 
?>
</body>
</html>
