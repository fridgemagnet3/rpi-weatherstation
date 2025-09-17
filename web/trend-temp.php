<html>
<head>
<title>Weather</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="onastick.css">
<style type="text/css"></style>
</head>
<body class="detailsubheading">
<h1 align="center" span class="detailheading">Temperature Trends</h1>
<h2 span class="detailsubheading" align="center">Number of days where maximum temperature fell in the following temperature ranges</h2>
<table width="85%" border="1" align="center" span class="detailtext">
<?php
function to_fahrenheit($degrees_c)
{
   return (float)$degrees_c * (9.0/5.0) + 32 ;
}

function is_leap_year($year) {
	return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
}

// connect to database for the archived stats
$mysqli = new mysqli("localhost", "weather", "weather", "weather") ;
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

print("<tr>\n<td width=\"10%\">&nbsp;</td>\n");
for($i=0 ; $i <=40 ; $i+=5 )
{
    print("<td width=\"10%\" span class=\"detailsubheading\" align=\"center\">" . $i . " - " .  ($i+4) . "&deg;C<br>" 
      . to_fahrenheit($i) . " - " . to_fahrenheit($i+4) . "&deg;F</td>\n") ;
}
print("</tr>\n");
$date_info = getdate() ;
$current_year = $date_info['year'];
while ( $current_year >= 2019 )
{
	 if ( is_leap_year($current_year))
	     $days_in_year = 366 ;
	 else
	     $days_in_year = 365 ;
	     
	 // the way this is done isn't great since it relies on the data not falling below the assumed minimum
	 // of 0 degrees (similar caveats on the minimum stats below) - if it does, the tables won't line up
    $ranges = array();
    for($i=0 ; $i <=40 ; $i+=5 )
    {
        $ranges[] = 0 ;
    }
    
    // the date ranges
    $start_date = $current_year . "-01-01" ;
    $end_date = $current_year . "-12-31" ;
    $query = "select * from temperature where date<='" . 
        $end_date . "' and date>='" . $start_date . " ';" ;
    $result = $mysqli->query($query) ;
    while ( $row = $result->fetch_assoc())
    {
        $temp_range = (int)($row['Max'] / 5) ;
        $ranges[$temp_range]++ ;
    }
    
    echo "<tr>\n";
    print("<td width=\"10%\">" . $current_year . "</td>\n") ;
    foreach($ranges as $value)
    {
    	  $percentage = round(($value/$days_in_year)*100);
        print("<td width=\"10%\" align=\"center\">" . $value . " (" . $percentage . "%)</td>\n") ;
    }
    echo "</tr>\n";
    $current_year-- ;
}
?>
</table>
<h2 span class="detailsubheading" align="center">Number of days where minimum temperature fell in the following temperature ranges</h2>
<table width="85%" border="1" align="center" span class="detailtext">
<?php
print("<tr>\n<td width=\"10%\">&nbsp;</td>\n");
for($i=-10 ; $i <=30 ; $i+=5 )
{
    print("<td width=\"10%\" span class=\"detailsubheading\" align=\"center\">" . $i . " - " .  ($i+4) . "&deg;C<br>" 
      . to_fahrenheit($i) . " - " . to_fahrenheit($i+4) . "&deg;F</td>\n") ;
}
print("</tr>\n");
$current_year = $date_info['year'];
while ( $current_year >= 2019 )
{
	 if ( is_leap_year($current_year))
	     $days_in_year = 366 ;
	 else
	     $days_in_year = 365 ;
	     
    $ranges = array();
    for($i=-10 ; $i <=30 ; $i+=5 )
    {
        $ranges[$i/5] = 0;
    }
    
    // the date ranges
    $start_date = $current_year . "-01-01" ;
    $end_date = $current_year . "-12-31" ;
    $query = "select * from temperature where date<='" . 
        $end_date . "' and date>='" . $start_date . " ';" ;
    $result = $mysqli->query($query) ;
    while ( $row = $result->fetch_assoc())
    {
        $temp_range = (int)($row['Min'] / 5) ;
    	  if ( $row['Min'] < 0 )
          $temp_range-=1 ;
        $ranges[$temp_range]++ ;
    }
    
    echo "<tr>\n";
    print("<td width=\"10%\">" . $current_year . "</td>\n") ;
    foreach($ranges as $value)
    {
    	  $percentage = round(($value/$days_in_year)*100);
        print("<td width=\"10%\" align=\"center\">" . $value . " (" . $percentage . "%)</td>\n") ;
    }
    echo "</tr>\n";
    $current_year-- ;
}
?>
</table>

</body>
</html>

