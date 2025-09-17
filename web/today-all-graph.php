<?php

/* pChart library inclusions */
include("../pchart/class/pData.class.php");
include("../pchart/class/pDraw.class.php");
include("../pchart/class/pImage.class.php");

// populate the chart data
$TempChart = new pData();  
// fetch the latest weather info
if ( file_exists('weather.xml') )
{
  $weather_xml = simplexml_load_file('weather.xml');
}

foreach($weather_xml->temperature->hourly_readings->hour as $hourly_reading)
{
    $TempChart->addPoints((float)$hourly_reading,"Temp") ;
    $TempChart->addPoints($hourly_reading['value'] . ":00","Labels") ;
}
foreach($weather_xml->wind_speed->hourly_readings->hour as $hourly_reading)
{
    $TempChart->addPoints((float)$hourly_reading,"Avg Wind") ;
    $TempChart->addPoints((float)$hourly_reading['max'],"Max Wind") ;
}

$rain_this_hour = 0.0;
foreach($weather_xml->rain->hourly_readings->hour as $hourly_reading)
{
    $rain_this_hour = (float)$hourly_reading - $rain_this_hour;
    $TempChart->addPoints((float)$rain_this_hour,"Rainfall") ;
    $rain_this_hour = (float)$hourly_reading ;
}

//$TempChart->setAxisName(0,"Temperatures");
$TempChart->setSerieDescription("Labels","Time");
$TempChart->setAbscissa("Labels");
$TempChart->setAxisColor(0,array("R"=>204,"G"=>102,"B"=>255));
$TempChart->setAxisColor(1,array("R"=>204,"G"=>102,"B"=>255));

$TempChart->loadPalette("../pchart/palettes/navy.color", TRUE);

/* Create the pChart object */
$TempGraph = new pImage(800,400,$TempChart);

/* Turn of Antialiasing */
$TempGraph->Antialias = TRUE;

// draw the background
$Settings = array("R"=>214, "G"=>214, "B"=>214);
$TempGraph->drawFilledRectangle(0,0,800,400,$Settings); 
 
/* Add a border to the picture */
$TempGraph->drawRectangle(0,0,799,399,array("R"=>0,"G"=>0,"B"=>0));
 
/* Write the chart title */ 
//$TempGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>11));
//$TempGraph->drawText(150,35,"All readings",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $TempGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>8));

/* Define the chart area */
$TempGraph->setGraphArea(60,40,750,350);

 /* Draw the scale */
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
$TempGraph->drawScale($scaleSettings);

/* Turn on Antialiasing */
$TempGraph->Antialias = TRUE;

$TempChart->setSerieDrawable("Temp",FALSE);
$TempChart->setSerieDrawable("Avg Wind",FALSE);
$TempChart->setSerieDrawable("Max Wind",FALSE);
$TempGraph->drawBarChart();

/* Draw the line chart */
$TempChart->setSerieDrawable("Rainfall",FALSE);
$TempChart->setSerieDrawable("Temp",TRUE);
$TempChart->setSerieDrawable("Avg Wind",TRUE);
$TempChart->setSerieDrawable("Max Wind",TRUE);
$TempGraph->drawLineChart();

$TempChart->drawAll(); 

/* Write the chart legend */
$TempGraph->drawLegend(340,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

/* Render the picture (choose the best way) */
$TempGraph->autoOutput("temp-graph.png") ;
 
?>

