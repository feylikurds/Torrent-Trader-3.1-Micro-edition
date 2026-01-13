<?php
require_once("backend/functions.php");
dbconn(false);
stdhead("Series");
begin_frame("Series");
$result = SQL_Query_exec("select name, tvdbid, firstaired,status,rating from series order by name");
if (get_user_class() >= 6)
print "<a href=\"series-add.php\">Add New Series</a><br><br>";

print "<table class=\"ttable_headinner\" cellspacing=\"0\" cellpadding=\"6\" align=\"center\" width=\"85%\"><tr><th class=\"ttable_head\">Show</th><th class=\"ttable_head\">First Aired</th><th class=\"ttable_head\">Status</th><th class=\"ttable_head\">Rating</th></tr>";
while($row=mysqli_fetch_array($result)) {
	print "<tr>";
	print "<td class=\"ttable_col1\"><a href=\"series-view.php?id=".$row['tvdbid']."\">".$row['name']."</a></td><td class=\"ttable_col2\">".$row['firstaired']."</td>";
	if($row['status'] == 'Ended')
		print "<td class=\"ttable_col1\"><span style=\"color:red;\">".$row['status']."</span></td>";
	else
		print "<td class=\"ttable_col1\"><span style=\"color:green;\">".$row['status']."</span></td>";
	print "<td class=\"ttable_col2\">".$row['rating']."</td>";
	print "</tr>";
}
print "</table>";
end_frame();
stdfoot();
?>