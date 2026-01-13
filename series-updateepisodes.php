<?php
	if(isset($_GET['id']))
		$id = $_GET['id'];
	else
		die("No ID specified");
	

require_once("backend/functions.php");
dbconn(false);

$url = "http://thetvdb.com/api/1DAE7A9823E16F0D/series/";

	$xml = simplexml_load_file($url.$id."/all/en.xml");
	foreach($xml->Episode as $episode) {
		$name = mysqli_real_escape_string($episode->EpisodeName);
		$guests = mysqli_real_escape_string($episode->GuestStars);
		$overview = mysqli_real_escape_string($episode->Overview);
		$airdate = mysqli_real_escape_string($episode->FirstAired);
		$epid = $episode->id;
		$season = $episode->SeasonNumber;
		$seriesid = $episode->seriesid;
		$episode = $episode->EpisodeNumber;
		$result = SQL_Query_exec("select tvdbid from episodes where tvdbid = $epid");
		//print (string)mysqli_num_rows($result); 
		if(mysqli_num_rows($result)< 1)
			$query = "insert into episodes(tvdbid,seriesid,name,number,season,gueststars,overview,firstaired) values ($epid,$seriesid,'$name',".(string)$episode.",".(string)$season.",'$guests','$overview','$airdate')";
		else
			$query = "update episodes set name='$name',number=".(string)$episode.",season=".(string)$season.",gueststars='$guests',overview='$overview',firstaired='$airdate' where tvdbid=$epid";
		//print $query."<br>";
		$res = SQL_Query_exec($query);
}
?>