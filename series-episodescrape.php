<?php
require_once("backend/functions.php");
dbconn(false);

$url = "http://thetvdb.com/api/1DAE7A9823E16F0D/series/";

$result = SQL_Query_exec("select tvdbid from series");
while($row = mysqli_fetch_array($result)) {
	$xml = simplexml_load_file($url.$row['tvdbid']."/all/en.xml");
	foreach($xml->Episode as $episode) {
		$name = mysqli_real_escape_string($episode->EpisodeName);
		$guests = mysqli_real_escape_string($episode->GuestStars);
		$overview = mysqli_real_escape_string($episode->Overview);
		$airdate = mysqli_real_escape_string($episode->FirstAired);
		$id = $episode->id;
		$season = $episode->SeasonNumber;
		$seriesid = $episode->seriesid;
		$episode = $episode->EpisodeNumber;
		
		$query = "insert into episodes(tvdbid,seriesid,name,number,season,gueststars,overview,firstaired) values ($id,$seriesid,'$name',".(string)$episode.",".(string)$season.",'$guests','$overview','$airdate')";
		//print $query."<br>";
		$res = SQL_Query_exec($query);
	}
}
?>