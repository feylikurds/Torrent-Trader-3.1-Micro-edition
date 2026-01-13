<?php
require_once("backend/functions.php");
dbconn();
loggedinonly();
stdhead("Live Sports");

/////////user location
SQL_Query_exec("UPDATE users SET page='Sports Page...' WHERE id ='$CURUSER[id]'");

	begin_frame("Live Sports");
    echo "<br /><center>";
	?>

<iframe src="http://ifirstrowus.eu/sport/american-football.html" width="100%" height="800" scrolling="auto" align="top" frameborder="0" sandbox="allow-same-origin allow-scripts"></iframe>
<?php
    echo "</center><br />";
	end_frame();
	stdfoot();
?>