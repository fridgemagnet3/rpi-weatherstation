<?php

/* pChart library inclusions */
include("../pchart/class/pData.class.php");
include("../pchart/class/pDraw.class.php");
include("../pchart/class/pImage.class.php");

// connect to database for the archived stats
$mysqli = new mysqli("localhost", "weather", "weather", "weather") ;
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

// compute date range for entire year
$start_date = $_GET["year"] . "-01-01" ;
$end_date = $_GET["year"] . "-12-31" ;
$query = "select Date,Min,Max from temperature where date<='" . 
  $end_date . "' and date>='" . $start_date . " ';" ;
$result = $mysqli->query($query) ;
$month = -1 ;

// populate the chart data
$TempChart = new pData();  
while ( $row = $result->fetch_assoc())
{
    // track month by month...
    $timestamp = getdate(strtotime($row['Date'])) ;
    if ( $timestamp['mon'] != $month )
    {
        // reset counters etc
        $month = $timestamp['mon'] ;
        $month_str = substr($timestamp['month'],0,3) ;
    }
    $TempChart->addPoints($row['Max'],"Max") ;
    $TempChart->addPoints($row['Min'],"Min") ;
    $TempChart->addPoints($month_str,"Labels") ;
}

$TempChart->setAxisName(0,"Temperatures");
$TempChart->setSerieDescription("Labels","Months");
$TempChart->setAbscissa("Labels");
$TempChart->setAxisColor(0,array("R"=>204,"G"=>102,"B"=>255));
$TempChart->setAxisColor(1,array("R"=>204,"G"=>102,"B"=>255));

$TempChart->loadPalette("../pchart/palettes/blind.color", TRUE);

/* Create the pChart object */
$TempGraph = new pImage(900,500,$TempChart);

/* Turn of Antialiasing */
$TempGraph->Antialias = TRUE;
// draw the background
$Settings = array("R"=>214, "G"=>214, "B"=>214);
$TempGraph->drawFilledRectangle(0,0,900,500,$Settings); 

/* Add a border to the picture */
$TempGraph->drawRectangle(0,0,899,499,array("R"=>0,"G"=>0,"B"=>0));
 
/* Write the chart title */ 
$TempGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>11));
$TempGraph->drawText(150,35,"Temperature",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

/* Set the default font */
$TempGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>10));

/* Define the chart area */
$TempGraph->setGraphArea(60,40,850,450);

 /* Draw the scale */
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"LabelingMethod"=>LABELING_DIFFERENT);
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

