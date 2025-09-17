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
$TempChart->setAxisName(0,"Temperatures");
$TempChart->setSerieDescription("Labels","Time");
$TempChart->setAbscissa("Labels");
$TempChart->setAxisColor(0,array("R"=>204,"G"=>102,"B"=>255));
$TempChart->setAxisColor(1,array("R"=>204,"G"=>102,"B"=>255));

$TempChart->loadPalette("../pchart/palettes/navy.color", TRUE);

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
 $TempGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>9));

/* Define the chart area */
$TempGraph->setGraphArea(60,40,850,450);

 /* Draw the scale */
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
$TempGraph->drawScale($scaleSettings);

/* Turn on Antialiasing */
$TempGraph->Antialias = TRUE;

/* Draw the line chart */
$TempGraph->drawLineChart();

/* Render the picture (choose the best way) */
$TempGraph->autoOutput("temp-graph.png") ;
 
?>

