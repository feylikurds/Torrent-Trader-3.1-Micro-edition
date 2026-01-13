<?php
require_once("backend/functions.php");
dbconn(false);
$seriesid = $_GET['id'];
$result = SQL_Query_exec("select name from series where tvdbid = $seriesid");
$row = mysqli_fetch_array($result);
$show = $row['name'];

stdhead("$show - View Season Shedule");
begin_frame("$show Season ".$_GET['season']." Schedule");

if(isset($_GET['season']))
	$query = "select tvdbid,number,name,firstaired from episodes where seriesid=$seriesid and season=".$_GET['season']." order by season, number";
else
	$query = "select tvdbid,number,name,firstaired from episodes where seriesid=$seriesid order by season, number";
//print $query;
print "<table class=\"ttable_headinner\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" width=\"95%\">";
print "<tr>";
print "<td class=\"ttable_head\">Episode Number</td>";
print "<td class=\"ttable_head\">Epsiode Name</td>";
print "<td class=\"ttable_head\">Originaly Aired</td>";
print "</tr>";
$result = SQL_Query_exec($query);
while($row=mysqli_fetch_array($result)) {
	print "<tr>";
	print "<td class=\"ttable_col2\">".$row['number']."</td><td class=\"ttable_col2\"><a href=\"series-viewepisode.php?id=".$row['tvdbid']."\">".$row['name']."</a></td><td class=\"ttable_col2\">".$row['firstaired']."</td>";
	print "</tr>";
}
print "</table>";
print "<br><br>";
print "<a href=\"series-view.php?id=$seriesid\">Back to $show</a>";
end_frame();
stdfoot();
?>