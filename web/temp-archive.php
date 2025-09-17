<!DOCTYPE html>
<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css"></style>
</head>

<body class="detailtext">
<p class="detailheading" align="center">Temperatures - Year on Year Comparison</p>
<p class="detailtext">This page allows you to graph and compare temperatures for 
  specific months (or even entire years). </p>
<p class="detailsubheading">How to use the comparison</p>
<p class="detailtext">Select the month (or 'All' for the entire year) of interest 
  from the <i>Month</i> drop down. Hold the shift key, then select the year's 
  of interest for comparison. It's generally best to stick to 2 or at most 3 years 
  at a time otherwise the graphs become confusing. Press <i>Submit</i> - note 
  that it takes a little while to generate the graph so be patient. </p>
<form method="post" action="temp-archive-stats.php">
  <div align="center">Year: 
    <select name="year[]" multiple size="6">
      <?php

$date_info = getdate() ;
$year = 2018;
while ( $year <= $date_info['year'] )
{
    echo "<option value=\"" . $year . "\">" . $year . "</option>\n" ;
    $year++ ;
}

?> 
    </select>
    Month: 
    <select name="month" size="1">
      <option value="0">All</option>
      <option value="1" selected>January</option>
      <option value="2">February</option>
      <option value="3">March</option>
      <option value="4">April</option>
      <option value="5">May</option>
      <option value="6">June</option>
      <option value="7">July</option>
      <option value="8">August</option>
      <option value="9">September</option>
      <option value="10">October</option>
      <option value="11">November</option>
      <option value="12">December</option>
    </select>
    <input type="submit" name="Submit" value="Submit">
  </div>
</form>
<p class="detailtext">&nbsp;</p>
</body>
</html>
