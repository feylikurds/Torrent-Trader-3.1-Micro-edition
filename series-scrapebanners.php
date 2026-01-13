<?php
	require_once("backend/functions.php");
	dbconn(false);
	
	$result = SQL_Query_exec("select banner from series where banner <> ''");
	while($row=mysqli_fetch_array($result)) {
		$cont = file_get_contents("http://thetvdb.com/banners/".$row['banner']);
		$filename = "/series/banners/".$row['banner'];
		$file = fopen($filename, 'w'); 
		fwrite($file, $cont);
		fclose($file);
	}
?>