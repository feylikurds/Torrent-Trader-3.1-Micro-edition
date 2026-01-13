<?
begin_block("Latest Requests");

	$file = "".$site_config["cache_dir"]."/cache_latestrequestsblock.txt";
	$expire = 600; // time in seconds
if (file_exists($file) &&
	filemtime($file) > (time() - $expire)) {
	$latestrequestsrecords = unserialize(file_get_contents($file));
}else{
	$latestrequestsquery = SQL_Query_exec("SELECT requests.id, requests.request, categories.name AS cat, categories.id AS catid,
		categories.parent_cat AS parent_cat FROM requests INNER JOIN categories ON requests.cat = categories.id ORDER BY
		requests.id DESC LIMIT 5");

while ($latestrequestsrecord = mysqli_fetch_array($latestrequestsquery) ) {
	$latestrequestsrecords[] = $latestrequestsrecord;
}
	$OUTPUT = serialize($latestrequestsrecords);
	$fp = fopen($file,"w");
	fputs($fp, $OUTPUT);
	fclose($fp);
} // end else

if ($latestrequestsrecords){
	foreach ($latestrequestsrecords as $row) {
	$smallname = htmlspecialchars(CutName($row["request"], 12));
	$smallnamereq = htmlspecialchars(CutName($row["cat"], 4));
echo "<table cellspacing='0' cellpadding='3' width='100%' border='0'><tr><td width='55%'><small><a style='text-decoration: none;' title='".$row["parent_cat"]." : ".$row["cat"]."'>".$row["parent_cat"]." : $smallnamereq </a></small></td><td width='45%'><a style='text-decoration: none;' title='".$row["request"]."' href='reqall.php?Section=Request_Details&id=$row[id]'>$smallname</a></td></tr></table> \n";
}
}else{
		print("<center>No requests</center> \n");
}
end_block();
?>