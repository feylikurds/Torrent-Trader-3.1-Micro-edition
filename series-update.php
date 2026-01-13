<?php
require_once("backend/functions.php");
dbconn(false);

$result = SQL_Query_exec("select tvdbid from series");
while($row = mysqli_fetch_array($result)) {
	$tvdbid=$row['tvdbid'];
	$url2 = "http://thetvdb.com/api/1DAE7A9823E16F0D/series/";
	$xml = simplexml_load_file($url2.$tvdbid."/all/en.xml");
	$series = $xml->Series;
	$dayofweek = $series->Airs_DayOfWeek;
	$airtime = $series->Airs_Time;
	$genre = $series->Genre;
	$status = $series->Status;
	$rating = $series->Rating;
	$res = SQL_Query_exec("update series set dayofweek='$dayofweek',airtime='$airtime',genre='$genre',status='$status',rating='$rating' where tvdbid=$tvdbid");
}
?>