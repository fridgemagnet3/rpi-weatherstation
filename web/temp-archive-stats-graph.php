<?php

/* pChart library inclusions */
include("../pchart/class/pData.class.php");
include("../pchart/class/pDraw.class.php");
include("../pchart/class/pImage.class.php");

$month_lookup = array( 1=> "January", 2=> "February", 3=> "March", 4=> "April", 5=> "May", 6=> "June", 7=> "July", 8=> "August", 9=> "September", 10=> "October", 11 => "November", 12=> "December" ) ;

if ( !isset($_GET["month"]))
    die("No month data" ) ;
if ( $_GET["month"] < 0 || $_GET["month"] > 12 )
    die("Invalid month" ) ;
$the_month = $_GET["month"] ;

// construct the array of years of interest
$idx = 0 ;
$temp_years = array() ;
while ( isset ( $_GET[$idx] ) )
{
    $temp_years[] = $_GET[$idx] ;
    $idx++ ;
}
if ( !$idx )
    die("No year data") ;

// what follows is pretty much the same as the main stats page except when it comes
// to generating the graph

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

// connect to database for the archived stats
$mysqli = new mysqli("localhost", "weather", "weather", "weather") ;
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

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

// populate the chart data
$TempChart = new pData();  

// if we're just generating a single month, this loop will only execute once
for ( $month=$the_month ; $month <=$month_end ; $month++ )
{
    $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$temp_years[0]) ;

    for ( $mday=1 ; $mday <= $days_in_month ; $mday++ )
    {
        // first do a parse of all the results for this date, we only generate a graph entry
        // if we have data for all of them
        $use_data = True ;
        for ( $idx=0 ; $idx < $year_count ; $idx++ )
        {
            // lookup the date in the temperature data retrieved from the database
            // include it in the table if it's available
            $the_year = $temp_years[$idx] ;
            $the_date = sprintf( "%04d-%02d-%02d", $the_year, $month, $mday) ;
            if ( !array_key_exists($the_date,$temp_stats[$the_year]['Temp1']) )
                $use_data = False ;
        }
        
        
        if ( $use_data )
        {
            // output calender date
            if ( $compute_year )
            {
                if ( $mday == 15 )
                    $TempChart->addPoints($month_lookup[$month],"Labels") ;
                else
                    $TempChart->addPoints(".","Labels") ;
            }
            else
                $TempChart->addPoints($mday,"Labels") ;
            for ( $idx=0 ; $idx < $year_count ; $idx++ )
            {
                // lookup the date in the temperature data retrieved from the database
                // include it in the table if it's available
                $the_year = $temp_years[$idx] ;
                $the_date = sprintf( "%04d-%02d-%02d", $the_year, $month, $mday) ;
                if ( $the_year < 2018 )
                    $TempChart->addPoints($temp_stats[$the_year]['Temp1'][$the_date],$the_year) ;
                else
                {
                    $TempChart->addPoints($temp_stats[$the_year]['Temp1'][$the_date],$the_year . "Min") ;
                    $TempChart->addPoints($temp_stats[$the_year]['Temp2'][$the_date],$the_year . "Max") ;
                }
            }
        }
    }
}

$TempChart->setAxisName(0,"Temperatures");  
$TempChart->setSerieDescription("Labels","Days");
$TempChart->setAbscissa("Labels");
$TempChart->setAxisColor(0,array("R"=>204,"G"=>102,"B"=>255));
$TempChart->setAxisColor(1,array("R"=>204,"G"=>102,"B"=>255));

$TempChart->loadPalette("../pchart/palettes/blind.color", TRUE);

/* Create the pChart object */
if ( $compute_year )
    $g_width = 1500 ;
else
    $g_width = 900 ;

$TempGraph = new pImage($g_width,500,$TempChart);

/* Turn of Antialiasing */
$TempGraph->Antialias = TRUE;
// draw the background
$Settings = array("R"=>214, "G"=>214, "B"=>214);
$TempGraph->drawFilledRectangle(0,0,$g_width,500,$Settings); 

/* Add a border to the picture */
$TempGraph->drawRectangle(0,0,$g_width-1,499,array("R"=>0,"G"=>0,"B"=>0));
 
/* Write the chart title */
$TempGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>11));
if ( $compute_year )
    $TempGraph->drawText(150,35,"Temperature",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
else
    $TempGraph->drawText(150,35,$month_lookup[$the_month],array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

/* Set the default font */
$TempGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>10));

/* Define the chart area */
$TempGraph->setGraphArea(60,40,$g_width-50,450);

 /* Draw the scale */
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
$TempGraph->drawScale($scaleSettings);

/* Turn on Antialiasing */
$TempGraph->Antialias = TRUE;

/* Draw the line chart */
$TempGraph->drawLineChart();

/* Write the chart legend */
$TempGraph->drawLegend(540,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

/* Render the picture (choose the best way) */
$TempGraph->autoOutput("temp-graph.png") ;
 
?>

