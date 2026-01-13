<?php
require_once("backend/functions.php");
dbconn(false);
stdhead("Add a new series");
begin_frame("Add a new series to the database");
?>
<form action="series-test.php" method="post">
<table>
	<tr>
		<td>Series Name</td>
		<td><input type="text" name="show"/></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="Save"></td>
	</tr>
</table>
<?php
end_frame();
stdfoot();
?>