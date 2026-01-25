<?php
//
//  TorrentTrader v2.x
//      $LastChangedDate: 2011-11-24 05:42:28 +0000 (Thu, 24 Nov 2011) $
//      $LastChangedBy: dj-howarth1 $
//
//      http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
require_once("backend/BDecode.php");
dbconn();

$torrent_dir = $site_config["torrent_dir"];	
$nfo_dir = $site_config["nfo_dir"];	

//check permissions
if ($site_config["MEMBERSONLY"]){
	loggedinonly();

	if($CURUSER["view_torrents"]=="no")
		show_error_msg(T_("ERROR"), T_("NO_TORRENT_VIEW"), 1);
}

// ************ DO SOME "GET" STUFF BEFORE PAGE LAYOUT ***************

$id = (int) $_GET["id"];
$scrape = (int)$_GET["scrape"];
if (!is_valid_id($id))
	show_error_msg("ERROR", T_("THATS_NOT_A_VALID_ID"), 1);

//GET ALL MYSQL VALUES FOR THIS TORRENT
$res = SQL_Query_exec("SELECT torrents.imdb, torrents.anon, torrents.trailers, torrents.seeders, torrents.tube, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, torrents.nfo, torrents.last_action, torrents.numratings, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.external, torrents.image1, torrents.image2, torrents.announce, torrents.numfiles, torrents.freeleech, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.numratings, categories.name AS cat_name, torrentlang.name AS lang_name, torrentlang.image AS lang_image, categories.parent_cat as cat_parent, users.username, users.privacy FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id");
$row = mysqli_fetch_assoc($res);

//DECIDE IF TORRENT EXISTS
if (!$row || ($row["banned"] == "yes" && $CURUSER["edit_torrents"] == "no"))
	show_error_msg(T_("ERROR"), T_("TORRENT_NOT_FOUND"), 1);

//torrent is availiable so do some stuff

if ($_GET["hit"]) {
	SQL_Query_exec("UPDATE torrents SET views = views + 1 WHERE id = $id");
	header("Location: torrents-details.php?id=$id");
	die;
	}

	stdhead(T_("DETAILS_FOR_TORRENT")." \"" . $row["name"] . "\"");

	if ($CURUSER["id"] == $row["owner"] || $CURUSER["edit_torrents"] == "yes")
		$owned = 1;
	else
		$owned = 0;

//take rating
if ($_GET["takerating"] == 'yes'){
	$rating = (int)$_POST['rating'];

	if ($rating <= 0 || $rating > 5)
		show_error_msg(T_("RATING_ERROR"), T_("INVAILD_RATING"), 1);

	$res = SQL_Query_exec("INSERT INTO ratings (torrent, user, rating, added) VALUES ($id, " . $CURUSER["id"] . ", $rating, '".get_date_time()."')");

	if (!$res) {
		if (mysqli_errno() == 1062)
			show_error_msg(T_("RATING_ERROR"), T_("YOU_ALREADY_RATED_TORRENT"), 1);
		else
			show_error_msg(T_("RATING_ERROR"), T_("A_UNKNOWN_ERROR_CONTACT_STAFF"), 1);
	}

	SQL_Query_exec("UPDATE torrents SET numratings = numratings + 1, ratingsum = ratingsum + $rating WHERE id = $id");
	show_error_msg(T_("RATING_ERROR"), T_("RATING_THANK")."<br /><br /><a href='torrents-details.php?id=$id'>" .T_("BACK_TO_TORRENT"). "</a>");
}

//take comment add
if ($_GET["takecomment"] == 'yes'){
    loggedinonly();
    $body = $_POST['body'];
    
    if (!$body)
    autolink("torrents-details.php?id=$id", T_("YOU_DID_NOT_ENTER_ANYTHING"));

    SQL_Query_exec("UPDATE torrents SET comments = comments + 1 WHERE id = $id");

    SQL_Query_exec("INSERT INTO comments (user, torrent, added, text) VALUES (".$CURUSER["id"].", ".$id.", '" .get_date_time(). "', " . sqlesc($body).")");

    if (mysqli_affected_rows($GLOBALS["DBconnector"]) == 1)
    autolink("torrents-details.php?id=$id", T_("COMMENT_ADDED"));
        else
    autolink("torrents-details.php?id=$id", T_("UNABLE_TO_ADD_COMMENT"));
}//end insert comment

//START OF PAGE LAYOUT HERE
$char1 = 50; //cut length
$shortname = CutName(htmlspecialchars($row["name"]), $char1);

begin_frame(T_("TORRENT_DETAILS_FOR"). " \"" . $shortname . "\"");

//echo "<div align='right'>[<a href='report.php?torrent=$id'><b>" .T_("REPORT_TORRENT"). "</b></a>]&nbsp;";
echo "<div align='right'><a href='report.php?torrent=$id'><input type='submit' value='" .T_("REPORT_TORRENT"). "'></a>&nbsp;";
if ($owned)
//	echo "[<a href='torrents-edit.php?id=$row[id]&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "'><b>" .T_("EDIT_TORRENT"). "</b></a>]&nbsp;";
	echo "<a href='torrents-edit.php?id=$row[id]&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "'><input type='submit' value='" .T_("EDIT_TORRENT"). "'></a>&nbsp;";
if ($owned)
//	echo "[<a href='snatchlist.php?tid=$row[id] '><b>" .T_("SNATCHLIST"). "</b></a>]";
	echo "&nbsp;<a href=snatched.php?tid=$row[id]><input type='submit' value='".T_("SNATCHLIST")."'></a>&nbsp;";
echo "</div>";

echo "<center><h1>" . $shortname . "</h1></center>";

/////openSSL torrent encryption
require_once("backend/URLencrypt.php");
/////end torrent encryption

// Calculate local torrent speed test
if ($row["leechers"] >= 1 && $row["seeders"] >= 1 && $row["external"]!='yes'){
	$speedQ = SQL_Query_exec("SELECT (SUM(p.downloaded)) / (UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(added)) AS totalspeed FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND p.torrent = '$id' GROUP BY t.id ORDER BY added ASC LIMIT 15");
	$a = mysqli_fetch_assoc($speedQ);
	$totalspeed = mksize($a["totalspeed"]) . "/s";
}else{
	$totalspeed = T_("NO_ACTIVITY"); 
}

//download box
echo "<center><table border='0' width='100%'><tr><td><div id='downloadbox'>";
if ($row["banned"] == "yes"){
	print ("<center><b>" .T_("DOWNLOAD"). ": </b>BANNED!</center>");
}else{
/////this is the original download without AES encryption. There are 2 down below///// <a href=\"download.php?id=$id&amp;name=" . rawurlencode($row["filename"]) . "\">
	
/////Begin AES download encryption
	print ("<table border='0' cellpadding='0' width='100%'><tr><td align='center' valign='middle' width='54'><a href='/download.php?id=" . $row["id"] . "'><img src=\"".$site_config["SITEURL"]."/images/download_torrent.png\" border=\"0\" alt='' /></a></td>");
	print ("<td valign='top'><a href='/download.php?id=" . $row["id"] . "'>".T_("DOWNLOAD_TORRENT")."</a><br />");
/////End AES download encryption
	
/////magnet link. Uses no AES encryption
	if ($row["external"]!='yes'){
	print ("<a href=\"magnet:?xt=urn:btih:" . $row['info_hash'] . "&dn=" . rawurlencode($row['name']) . "&tr=" . $row['announce'] . "?passkey=". $CURUSER ['passkey']. "\">".T_("MAGNET")."</a><br>");
	}
/////end magnet link
	
	print ("<b>" .T_("HEALTH"). ": </b><img src='".$site_config["SITEURL"]."/images/health/health_".health($row["leechers"], $row["seeders"]).".gif' alt='' /><br />");
	print ("<b>" .T_("SEEDS"). ": </b><font color='green'>" . number_format($row["seeders"]) . "</font><br />");
	print ("<b>".T_("LEECHERS").": </b><font color='#ff0000'>" .  number_format($row["leechers"]) . "</font><br />");

	if ($row["external"]!='yes'){
		print ("<b>".T_("SPEED").": </b>" . $totalspeed . "<br />");
	}

	print ("<b>".T_("COMPLETED").":</b> " . number_format($row["times_completed"]) . "&nbsp;"); 

	if ($row["external"] != "yes" && $row["times_completed"] > 0) {
		echo("[<a href='torrents-completed.php?id=$id'>" .T_("WHOS_COMPLETED"). "</a>] ");
		if ($row["seeders"] <= 1) {
			echo("[<a href='torrents-reseed.php?id=$id'>" .T_("REQUEST_A_RE_SEED"). "</a>]");
		}
	}
	echo "<br />";

	if ($row["external"]!='yes' && $row["freeleech"]=='1'){
		print ("<b>".T_("FREE_LEECH").": </b><font color='#ff0000'>".T_("FREE_LEECH_MSG")."</font><br />");
	}

	print ("<b>".T_("LAST_CHECKED").": </b>" . date("m-d-Y g:i:s", utc_to_tz_time($row["last_action"])) . "<br /></td>");

	if ($row["external"]=='yes'){

		if ($scrape =='1'){
			print("<td valign='top' align='right'><b>Tracked: </b>EXTERNAL<br /><br />");
			$seeders1 = $leechers1 = $downloaded1 = null;

			$tres = SQL_Query_exec("SELECT url FROM announce WHERE torrent=$id");
			while ($trow = mysqli_fetch_array($tres)) {
				$ann = $trow["url"];
				$tracker = explode("/", $ann);
				$path = array_pop($tracker);
				$oldpath = $path;
				$path = preg_replace("/^announce/", "scrape", $path);
				$tracker = implode("/", $tracker)."/".$path;

				if ($oldpath == $path) {
					continue; // Scrape not supported, ignored
				}

				// TPB's tracker is dead. Use openbittorrent instead
				if (preg_match("/thepiratebay.org/i", $tracker) || preg_match("/prq.to/", $tracker)) {
					$tracker = "http://tracker.openbittorrent.com/scrape";
				}

				$stats = torrent_scrape_url($tracker, $row["info_hash"]);
				if ($stats['seeds'] != -1) {
					$seeders1 += $stats['seeds'];
					$leechers1 += $stats['peers'];
					$downloaded1 += $stats['downloaded'];
					SQL_Query_exec("UPDATE `announce` SET `online` = 'yes', `seeders` = $stats[seeds], `leechers` = $stats[peers], `times_completed` = $stats[downloaded] WHERE `url` = ".sqlesc($ann)." AND `torrent` = $id");
				} else {
					SQL_Query_exec("UPDATE `announce` SET `online` = 'no' WHERE `url` = ".sqlesc($ann)." AND `torrent` = $id");

				}
			}

			if ($seeders1 !== null){ //only update stats if data is received
				print ("<b>".T_("LIVE_STATS").": </b><br />");
				print ("Seeders: ".number_format($seeders1)."<br />");
				print ("Leechers: ".number_format($leechers1)."<br />");
				print (T_("COMPLETED").": ".number_format($downloaded1)."<br />");

				SQL_Query_exec("UPDATE torrents SET leechers='".$leechers1."', seeders='".$seeders1."', times_completed='".$downloaded1."',last_action= '".get_date_time()."',visible='yes' WHERE id='".$row['id']."'"); 
			}else{
				print ("<b>".T_("LIVE_STATS").": </b><br />");
				print ("<font color='#ff0000'>Tracker Timeout<br />Please retry later</font><br />");
			}

			print ("<form action='torrents-details.php?id=$id&amp;scrape=1' method='post'><input type=\"submit\" name=\"submit\" value=\"Update Stats\" /></form></td>");
		}else{
			print ("<td valign='top' align='right'><b>Tracked:</b> EXTERNAL<br /><br /><form action='torrents-details.php?id=$id&amp;scrape=1' method='post'><input type=\"submit\" name=\"submit\" value=\"Update Stats\" /></form></td>");
		}
	}

	echo "</tr></table>";
}
echo "</div></td></tr></table></center><br /><br />";
//end download box

	?>
	
	<!-----NEW DETAILS LAYOUT 4-8-2016 ----->
	
	<fieldset class="download">
	<legend><b><?php echo T_("DETAILS"); ?></b></legend>  
			 <?php $link_address = 'account-details.php?id='; ?> 
			 <?php $anon = 'Anonymous'; ?>
	
			 <?php $image = ("/images/youtube-trailer.png"); ?>                                      
	<table border="0" cellpadding="3" width="100%">
	<tr>
		  <td width="5"></td>
			 <td valign="top">
			 <b><?php echo T_("NAME"); ?></b><br /> <?php echo $shortname; ?></a><br /><br />
		
	<?php /*?>         <b><?php echo T_("IMDB_LINK"); ?></b><br /> <a href="<?php echo $row['imdb']; ?>" target="_blank"><?php echo htmlspecialchars($row['imdb']); ?></a><br /><br />
			 <b><?php echo T_("IMDB_ID"); ?></b><br /> <?php echo $_data->imdbID; ?><br /><br /><?php */?>
		
			 <b><?php echo T_("DESCRIPTION"); ?></b><br /> <?php echo format_comment( $row['descr'] ); ?><br /><br />
			 <b><?php echo T_("CATEGORY"); ?></b><br /> <?php echo  $row["cat_parent"] . " > " . $row["cat_name"]; ?><br /><br />
			 <b><?php echo T_("TOTAL_SIZE"); ?></b><br /> <?php echo mksize($row["size"]); ?><br /><br />
			 <b><?php echo T_("INFO_HASH"); ?></b><br /> <?php echo $row["info_hash"]; ?><br /><br />
			 
				<?php if ($row["username"]):{ ?>
			 <b><?php echo T_("ADDED_BY");?></b><br /><?php echo "<a href='" .$link_address." " .$row["owner"]. "'>".class_user ($row["username"])."</a>"; } ?><br /><br />
	
			 <b><?php echo T_("DATE_ADDED"); ?></b><br /> <?php echo date("F d, Y g:i:s a", utc_to_tz_time($row["added"])); ?><br /><br />
			 <b><?php echo T_("VIEWS"); ?></b><br /> <?php echo number_format($row["views"]); ?><br /><br />
			 <b><?php echo T_("HITS"); ?></b><br /> <?php echo number_format($row["hits"]); ?>
			 </td>
	
			<?php endif; ?> 
	</tr>
	
	</table>
	</fieldset><br />


<?php


/*
///// below is the original details layout

echo "<fieldset class='download'><legend><b>Details</b></legend>";
echo "<table cellpadding='3' border='0' width='100%'>";
print("<tr><td align='left'><b>".T_("NAME").":</b></td><td>" . $shortname . "</td></tr>\n");
print("<tr><td align='left' colspan='2'><b>" .T_("DESCRIPTION"). ":</b><br />" .  format_comment($row['descr']) . "</td></tr>\n");
print("<tr><td align='left'><b>" .T_("CATEGORY"). ":</b></td><td>" . $row["cat_parent"] . " > " . $row["cat_name"] . "</td></tr>\n");

if (empty($row["lang_name"])) $row["lang_name"] = "Unknown/NA";
print("<tr><td align='left'><b>" .T_("LANG"). ":</b></td><td>" . $row["lang_name"] . "\n");

if (isset($row["lang_image"]) && $row["lang_image"] != "")
			print("&nbsp;<img border=\"0\" src=\"" . $site_config['SITEURL'] . "/images/languages/" . $row["lang_image"] . "\" alt=\"" . $row["lang_name"] . "\" />");

print("</td></tr>");

print("<tr><td align='left'><b>" .T_("TOTAL_SIZE"). ":</b></td><td>" . mksize($row["size"]) . " </td></tr>\n");
print("<tr><td align='left'><b>" .T_("INFO_HASH"). ":</b></td><td>" . $row["info_hash"] . "</td></tr>\n");
print("");
if ($row["anon"] == "yes" && !$owned)
	print("<tr><td align='left'><b>" .T_("ADDED_BY"). ":</b></td><td>Anonymous</td></tr>");
elseif ($row["username"])
	print("<tr><td align='left'><b>" .T_("ADDED_BY"). ":</b></td><td><a href='account-details.php?id=" . $row["owner"] . "'>".class_user($row['username'])."</a></td></tr>");
else
	print("<tr><td align='left'><b>" .T_("ADDED_BY"). ":</b></td><td>Unknown</td></tr>");

print("<tr><td align='left'><b>" .T_("DATE_ADDED"). ":</b></td><td>" . date("F d, Y g:i:s a", utc_to_tz_time($row["added"])) . "</td></tr>\n");
print("<tr><td align='left'><b>" .T_("VIEWS"). ":</b></td><td>" . number_format($row["views"]) . "</td></tr>\n");
print("<tr><td align='left'><b>".T_("HITS").":</b></td><td>" . number_format($row["hits"]) . "</td></tr>\n");
if (!empty($row["tube"]))
print ("<tr><td align='left'><b>" .T_("VIDEOTUBE"). ": </b></td><td align='left'><embed src='". str_replace("watch?v=", "v/", htmlspecialchars($row["tube"])) ."' type=\"application/x-shockwave-flash\" width=\"400\" height=\"310\"></embed></td></tr>");

else
print ("");


echo "</table></fieldset><br /><br />";


/////end original details layout
*/

// $srating IS RATING VARIABLE
		$srating = "";
		$srating .= "<table class='f-border' cellspacing=\"1\" cellpadding=\"4\" width='100%'><tr><td class='f-title' width='60'><b>".T_("RATINGS").":</b></td><td class='f-title' valign='middle'>";
		if (!isset($row["rating"])) {
				$srating .= "Not Yet Rated";
		}else{
			$rpic = ratingpic($row["rating"]);
			if (!isset($rpic))
				$srating .= "invalid?";
			else
				$srating .= "$rpic (" . $row["rating"] . " ".T_("OUT_OF")." 5) " . $row["numratings"] . " ".T_("USERS_HAVE_RATED");
		}
		$srating .= "\n";
		if (!isset($CURUSER))
			$srating .= "(<a href=\"account-login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">Log in</a> to rate it)";
		else {
			$ratings = array(
					5 => T_("COOL"),
					4 => T_("PRETTY_GOOD"),
					3 => T_("DECENT"),
					2 => T_("PRETTY_BAD"),
					1 => T_("SUCKS")
			);
			//if (!$owned || $moderator) {
				$xres = SQL_Query_exec("SELECT rating, added FROM ratings WHERE torrent = $id AND user = " . $CURUSER["id"]);
				$xrow = mysqli_fetch_assoc($xres);
				if ($xrow)
					$srating .= "<br /><i>(".T_("YOU_RATED")." \"" . $xrow["rating"] . " - " . $ratings[$xrow["rating"]] . "\")</i>";
				else {
					$srating .= "<form style=\"display:inline;\" method=\"post\" action=\"torrents-details.php?id=$id&amp;takerating=yes\"><input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
					$srating .= "<select name=\"rating\">\n";
					$srating .= "<option value=\"0\">(".T_("ADD_RATING").")</option>\n";
					foreach ($ratings as $k => $v) {
						$srating .= "<option value=\"$k\">$k - $v</option>\n";
					}
					$srating .= "</select>\n";
					$srating .= "<input type=\"submit\" value=\"".T_("VOTE")."\" />";
					$srating .= "</form>\n";
				}
			//}
		}
		$srating .= "</td></tr></table>";

//print("<center>". $srating . "</center>");// rating

//END DEFINE RATING VARIABLE



echo "<br />";
     
//	/////////////new images display/////////////// 
//echo "<fieldset class='download'><legend><b>Screens</b></legend>";
//print("<center><b>Click to view</b></center><br />");
//if ($row["image1"] != "" OR $row["image2"] != "") {
//  if ($row["image1"] != "")
//    $img1 = "<div id='posteris'><a href='".$site_config["SITEURL"]."/uploads/images/$row[image1]' rel='prettyPhoto'><img src='".$site_config["SITEURL"]."/uploads/images/$row[image1]' width='150' border='0' alt='' /></a>";
//  if ($row["image2"] != "")
//    $img2 = "<a href='".$site_config["SITEURL"]."/uploads/images/$row[image2]' rel='prettyPhoto'><img src='".$site_config["SITEURL"]."/uploads/images/$row[image2]' width='150' border='0' alt='' /></a></div>";
//  print("<center>". $img1 . "&nbsp;&nbsp;" . $img2."</center><br />");
//}
//else
//{
//  if ($row["image1"] == "")
//   $img1 = "<div id='posteris'><a href='".$site_config["SITEURL"]."/images/nocover.png' rel='prettyPhoto'><img src='".$site_config["SITEURL"]."/images/nocover.png' width='150' border='0' alt='' /></a>";
//  if ($row["image2"] == "")
//   $img2 = "<a href='".$site_config["SITEURL"]."/images/nocover.png' rel='prettyPhoto'><img src='".$site_config["SITEURL"]."/images/nocover.png' width='150' border='0' alt='' /></a></div>";
//  print("<center>". $img1 . "&nbsp;&nbsp;" . $img2."</center><br />");
//}
//echo "</fieldset><br />";
//////////////////////////////end///////////////////////////
                                            
//this is the image code commented out

//if ($row["image1"] != "" OR $row["image2"] != "") {
//  if ($row["image1"] != "")
//    $img1 = "<img src='".$site_config["SITEURL"]."/uploads/images/$row[image1]' width='150' border='0' alt='' />";
//  if ($row["image2"] != "")
//    $img2 = "<img src='".$site_config["SITEURL"]."/uploads/images/$row[image2]' width='150' border='0' alt='' />";
//  print("<center>". $img1 . "&nbsp;&nbsp;" . $img2."</center><br />");
//}

if ($row["external"]=='yes'){
	print ("<br /><b>Tracker:</b><br /> ".htmlspecialchars($row['announce'])."<br />");
}

$tres = SQL_Query_exec("SELECT * FROM `announce` WHERE `torrent` = $id");
if (mysqli_num_rows($tres) > 1){
	echo "<br /><b>".T_("THIS_TORRENT_HAS_BACKUP_TRACKERS")."</b><br />";
	echo '<table cellpadding="1" cellspacing="2" class="table_table"><tr>';
	echo '<th class="table_head">URL</th><th class="table_head">'.T_("SEEDERS").'</th><th class="table_head">'.T_("LEECHERS").'</th><th class="table_head">'.T_("COMPLETED").'</th></tr>';
	$x = 1;
	while ($trow = mysqli_fetch_assoc($tres)) {
		$colour = $trow["online"] == "yes" ? "green" : "red";
		echo "<tr class=\"table_col$x\"><td><font color=\"$colour\"><b>".htmlspecialchars($trow['url'])."</b></font></td><td align=\"center\">".number_format($trow["seeders"])."</td><td align=\"center\">".number_format($trow["leechers"])."</td><td align=\"center\">".number_format($trow["times_completed"])."</td></tr>";
		$x = $x == 1 ? 2 : 1;
	}
	echo '</table>';
}

echo "<br /><br /><b>".T_("FILE_LIST").":</b>&nbsp;<img src='images/plus.gif' id='pic1' onclick='klappe_torrent(1)' alt='' /><div id='k1' style='display: none;'><table align='center' cellpadding='0' cellspacing='0' class='table_table' border='1' width='100%'><tr><th class='table_head' align='left'>&nbsp;".T_("FILE")."</th><th width='50' class='table_head'>&nbsp;".T_("SIZE")."</th></tr>";
$fres = SQL_Query_exec("SELECT * FROM `files` WHERE `torrent` = $id ORDER BY `path` ASC");
if (mysqli_num_rows($fres)) {
    while ($frow = mysqli_fetch_assoc($fres)) {
        echo "<tr><td class='table_col1'>".htmlspecialchars($frow['path'])."</td><td class='table_col2'>".mksize($frow['filesize'])."</td></tr>";
    }
}else{
    echo "<tr><td class='table_col1'>".htmlspecialchars($row["name"])."</td><td class='table_col2'>".mksize($row["size"])."</td></tr>";
}
echo "</table></div>";

if ($row["external"]!='yes'){
	echo "<br /><br /><b>".T_("PEERS_LIST").":</b><br />";
	$query = SQL_Query_exec("SELECT * FROM peers WHERE torrent = $id ORDER BY seeder DESC");

	$result = mysqli_num_rows($query);
		if($result == 0) {
			echo T_("NO_ACTIVE_PEERS")."\n";
		}else{
			?>

<table border="0" cellpadding="3" cellspacing="0" width="100%" class="table_table">
			<tr>
                <th class="table_head"><?php echo T_("PORT"); ?></th>
			    <th class="table_head"><?php echo T_("UPLOADED"); ?></th>
			    <th class="table_head"><?php echo T_("DOWNLOADED"); ?></th>
			    <th class="table_head"><?php echo T_("RATIO"); ?></th>
			    <th class="table_head"><?php echo T_("LEFT"); ?></th>
			    <th class="table_head"><?php echo T_("FINISHED_SHORT"). "%"; ?></th>
			    <th class="table_head"><?php echo T_("SEED"); ?></th>
			    <th class="table_head"><?php echo T_("CONNECTED_SHORT"); ?></th>
			    <th class="table_head"><?php echo T_("CLIENT"); ?></th>
			    <th class="table_head"><?php echo T_("USER_SHORT"); ?></th>
			</tr>

			<?php
			while($row1 = mysqli_fetch_array($query))	{
				
				if ($row1["downloaded"] > 0){
					$ratio = $row1["uploaded"] / $row1["downloaded"];
					$ratio = number_format($ratio, 3);
				}else{
					$ratio = "---";
				}

				$percentcomp = sprintf("%.2f", 100 * (1 - ($row1["to_go"] / $row["size"])));    

				if ($site_config["MEMBERSONLY"]) {
					$res = SQL_Query_exec("SELECT id, username, privacy FROM users WHERE id=".$row1["userid"]."");
					$arr = mysqli_fetch_array($res);
                    
                    $arr["username"] = "<a href='account-details.php?id=$arr[id]'>$arr[username]</a>";
				}
                
                # With $site_config["MEMBERSONLY"] off this will be shown.
                if ( !$arr["username"] ) $arr["username"] = "Unknown User";
        
				if ($arr["privacy"] != "strong" || ($CURUSER["control_panel"] == "yes")) {
					print("<tr><td class='table_col2'>".$row1["port"]."</td><td class='table_col1'>".mksize($row1["uploaded"])."</td><td class='table_col2'>".mksize($row1["downloaded"])."</td><td class='table_col1'>".$ratio."</td><td class='table_col2'>".mksize($row1["to_go"])."</td><td class='table_col1'>".$percentcomp."%</td><td class='table_col2'>$row1[seeder]</td><td class='table_col1'>$row1[connectable]</td><td class='table_col2'>".htmlspecialchars($row1["client"])."</td><td class='table_col1'>$arr[username]</td></tr>");
				}else{
					print("<tr><td class='table_col2'>".$row1["port"]."</td><td class='table_col1'>".mksize($row1["uploaded"])."</td><td class='table_col2'>".mksize($row1["downloaded"])."</td><td class='table_col1'>".$ratio."</td><td class='table_col2'>".mksize($row1["to_go"])."</td><td class='table_col1'>".$percentcomp."%</td><td class='table_col2'>$row1[seeder]</td><td class='table_col1'>$row1[connectable]</td><td class='table_col2'>".htmlspecialchars($row1["client"])."</td><td class='table_col1'>Private</td></tr>");
				}

			}
			echo "</table>";
	}
}

echo "<br /><br />";
	 end_frame();
///NEW NFO DISPLAY
begin_frame("NFO");
?>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.collapsible {
  background-color: inherit;
  color: inherit;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  border: none;
  text-align: left;
  outline: none;
  font-size: 15px;
}

.active, .collapsible:hover {
  background-color: inherit;
}

.content {
  padding: 0 18px;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.2s ease-out;
  background-color: inherit;

}
</style>
</head>
<body>
<button class="collapsible">Click to view NFO</button>
<div class="content">
  <p><?php echo "<br />";
	  if ($row["nfo"] == "yes") {
     echo('<img src="nfo-view.php?id=' . $id . '">');
            } 
        else {
             print(T_("NFO_NOT_FOUND"));
              }
	  echo "<br /><br />";
	  ?></p>
	</div></div>
<script>
var coll = document.getElementsByClassName("collapsible");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.maxHeight){
      content.style.maxHeight = null;
    } else {
      content.style.maxHeight = content.scrollHeight + "px";
    } 
  });
}
</script>

</body>
				<?php
	end_frame();
				echo "<br /><br />";
///END NEW NFO DISPLAY
	
///DISPLAY NFO BLOCK
//begin_frame("NFO");
//    if ($row["nfo"] == "yes") {
//     echo('<img src="nfo-view.php?id=' . $id . '">');
//            }
//                else {
//                    print(T_("NFO_NOT_FOUND"));
//                }       
//end_frame();
/// END NFO

///TESTING SIMIALER TORRENT



////////////// Similar Torrents mod /////////////////////
begin_frame(T_("SIMILAR_TORRENTS"));
            $char1 = 50; //cut length
            $shortname = CutName(htmlspecialchars($a["name"]), $char1);
            $searchname = substr($row['name'], 0, 8);
            $query1 = str_replace(" ", ".", sqlesc("%" . $searchname . "%"));
            $query2 = str_replace(".", " ", sqlesc("%" . $searchname . "%"));
$r = SQL_Query_exec("SELECT torrents.id,  torrents.name,  torrents.size,  torrents.added,  torrents.seeders,  torrents.leechers,  torrents.category, categories.image FROM torrents LEFT JOIN categories ON (categories.id = torrents.category) WHERE (torrents.name LIKE {$query1} AND seeders > '0' AND torrents.id <> '$id') OR (torrents.name LIKE {$query2} AND seeders > '0' AND torrents.id <> '$id') ORDER BY seeders DESC LIMIT 10") or die(mysqli_error());
  
    if (mysqli_num_rows($r) > 0) {
            echo "<table width=100% class=main border=1 cellspacing=0 cellpadding=0 class=table_table>\n" . "
            <tr>
                    <td class='table_head' width='20'>Type</td>
                    <td class='table_head'>Name</td>
                    <td class='table_head' align='center'>Size</td>
                    <td class='table_head' align='center'>Added</td>
                    <td class='table_head' align='center'>S</td>
                    <td class='table_head' align='center'>L</td></tr>\n";
  
    while ($a = mysqli_fetch_assoc($r)) {
  //          $r2 = SQL_Query_exec("SELECT name, image FROM categories WHERE id=$a[category]");
  //          $a2 = mysqli_fetch_assoc($r2);
            $cat = "<img class=glossy src=\"images/categories/$a[image]\" alt=\"$a[name]\" title=\"$row[cat_parent] : $row[cat_name]\"\>";
            $name = $a["name"];
  
            echo"<tr><td class='table_col1' style='padding: 1px' align='center'>$cat</td>
                     <td class='table_col1'><a title=".$a["name"]." href=torrents-details.php?id=" . $a["id"] . "&hit=1><b>" . CutName(htmlspecialchars($a["name"]), $char1) . "</b><br/></a></td>
                     <td class='table_col1' style='padding: 1px' align='center'>" . mksize($a['size']) . "</td>
                     <td class='table_col1' style='padding: 1px' align='center'>$a[added]</td>
                     <td class='table_col1' style='padding: 1px' align='center'><span style='color:Chartreuse'>$a[seeders]</span></td>
                     <td class='table_col1' style='padding: 1px' align=center><span style='color:red'>$a[leechers]</span></td></tr>\n";
    }
      
    echo "</table>";
      
    }
                else {
                    print(T_("NO_SIMILAR_TORRENT_FOUND"));
                }
end_frame();
////////////// End Similar Torrents mod /////////////////////

begin_frame(T_("COMMENTS"));
	//echo "<p align=center><a class=index href=torrents-comment.php?id=$id>" .T_("ADDCOMMENT"). "</a></p>\n";
	require_once("backend/bbcode.php");

	$subres = SQL_Query_exec("SELECT COUNT(*) FROM comments WHERE torrent = $id");
	$subrow = mysqli_fetch_array($subres);
	$commcount = $subrow[0];

	if ($commcount) {
		list($pagertop, $pagerbottom, $limit) = pager(10, $commcount, "torrents-details.php?id=$id&amp;");
		$commquery = "SELECT comments.id, text, user, comments.added, avatar, signature, username, title, class, uploaded, downloaded, privacy, donated FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id $limit";
		$commres = SQL_Query_exec($commquery);
	}else{
		unset($commres);
	}

	if ($commcount) {
		print($pagertop);
		commenttable($commres, 'torrent');
		print($pagerbottom);
	}else {
		print("<br /><b>" .T_("NO_COMMENTS_YET"). "</b><br />\n");
	}


	if ($CURUSER) {
		echo "<center>";
		echo "<form name=\"comment\" method=\"post\" action=\"torrents-details.php?id=$row[id]&amp;takecomment=yes\">";
		echo textbbcode("comment","body")."<br />";
		echo "<input type=\"submit\"  value=\"".T_("ADDCOMMENT")."\" />";
		echo "</form></center>";
	}

	end_frame();

stdfoot();
?>
