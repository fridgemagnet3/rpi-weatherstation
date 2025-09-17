<?php

/* pChart library inclusions */
include("../pchart/class/pData.class.php");
include("../pchart/class/pDraw.class.php");
include("../pchart/class/pImage.class.php");

// populate the chart data
$RainChart = new pData();  
// fetch the latest weather info
if ( file_exists('weather.xml') )
{
  $weather_xml = simplexml_load_file('weather.xml');
}

$rain_this_hour = 0.0;
foreach($weather_xml->rain->hourly_readings->hour as $hourly_reading)
{
    $rain_this_hour = (float)$hourly_reading - $rain_this_hour;
    $RainChart->addPoints((float)$rain_this_hour,"Rain Per Hour") ;
    $RainChart->addPoints((float)$hourly_reading,"Rain Total") ;    
    $rain_this_hour = (float)$hourly_reading ;
    $RainChart->addPoints($hourly_reading['value'] . ":00","Labels") ;
}
$RainChart->setAxisName(0,"Rainfall");
$RainChart->setSerieDescription("Labels","Time");
$RainChart->setAbscissa("Labels");
$RainChart->setAxisColor(0,array("R"=>204,"G"=>102,"B"=>255));
$RainChart->setAxisColor(1,array("R"=>204,"G"=>102,"B"=>255));

$RainChart->loadPalette("../pchart/palettes/navy.color", TRUE);

/* Create the pChart object */
$RainGraph = new pImage(900,500,$RainChart);

/* Turn of Antialiasing */
$RainGraph->Antialias = TRUE;

// draw the background
$Settings = array("R"=>214, "G"=>214, "B"=>214);
$RainGraph->drawFilledRectangle(0,0,900,500,$Settings); 
 
/* Add a border to the picture */
$RainGraph->drawRectangle(0,0,899,499,array("R"=>0,"G"=>0,"B"=>0));
 
/* Write the chart title */ 
$RainGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>11));
$RainGraph->drawText(150,35,"Rainfall",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $RainGraph->setFontProperties(array("FontName"=>"../pchart/fonts/verdana.ttf","FontSize"=>9));

/* Define the chart area */
$RainGraph->setGraphArea(60,40,850,450);

 /* Draw the scale */
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
$RainGraph->drawScale($scaleSettings);

/* Turn on Antialiasing */
$RainGraph->Antialias = TRUE;

/* Draw the line chart */
$RainChart->setSerieDrawable("Rain Total",FALSE); 
$RainGraph->drawBarChart();
$RainChart->setSerieDrawable("Rain Total",TRUE); 
$RainChart->setSerieDrawable("Rain Per Hour",FALSE); 
$RainGraph->drawLineChart();

$RainChart->drawAll(); 

/* Write the chart legend */
$RainGraph->drawLegend(540,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

/* Render the picture (choose the best way) */
$RainGraph->autoOutput("rain-graph.png") ;
 
?>

