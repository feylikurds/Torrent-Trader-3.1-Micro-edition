<?php
//
//  TorrentTrader v2.x
//      $LastChangedDate: 2011-11-11 16:19:27 +0000 (Fri, 11 Nov 2011) $
//      $LastChangedBy: dj-howarth1 $
//
//      http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();

// check access and rights
if ($site_config["MEMBERSONLY"]){
	loggedinonly();

	if($CURUSER["can_upload"]=="no")
		show_error_msg(T_("ERROR"), T_("UPLOAD_NO_PERMISSION"), 1);
	if ($site_config["UPLOADERSONLY"] && $CURUSER["class"] < 4)
		show_error_msg(T_("ERROR"), T_("UPLOAD_ONLY_FOR_UPLOADERS"), 1);
}

$announce_urls = explode(",", strtolower($site_config["announce_list"]));  //generate announce_urls[] from config.php

if ($_POST["takeupload"] == "yes") { 
	require_once("backend/parse.php");

	//check form data
	foreach(explode(":","type:name") as $v) {
		if (!isset($_POST[$v]))
			$message = T_("MISSING_FORM_DATA");
	}

	if (!isset($_FILES["torrent"]))
	$message = T_("MISSING_FORM_DATA");
    
    if (($num = $_FILES['torrent']['error']))
//         show_error_msg('Error', T_("UPLOAD_ERR[$num]"), 1);
autolink("torrents-upload.php?action=style", T_("UPLOAD_ERR[4]"));
	$f = $_FILES["torrent"];
	$fname = $f["name"];

	if (empty($fname))
		$message = T_("EMPTY_FILENAME");
		$nfo = 'no';
	if ($_FILES['nfo']['size'] != 0) {
		$nfofile = $_FILES['nfo'];

		if ($nfofile['name'] == '')
			$message = T_("NO_NFO_UPLOADED");
			
		if (!preg_match('/^(.+)\.nfo$/si', $nfofile['name'], $fmatches))
			$message = T_("UPLOAD_NOT_NFO");

		if ($nfofile['size'] == 0)
			$message = T_("NO_NFO_SIZE");

		if ($nfofile['size'] > 65535)
			$message = T_("NFO_UPLOAD_SIZE");

		$nfofilename = $nfofile['tmp_name'];

        if (($num = $_FILES['nfo']['error']))
             $message = T_("UPLOAD_ERR[$num]");
        
		$nfo = 'yes';
	}

	$descr = $_POST["descr"];

	if (!$descr)
		$descr = T_("UPLOAD_NO_DESC");

	$langid = (int) $_POST["lang"];
	
	/*if (!is_valid_id($langid))
		$message = "Please be sure to select a torrent language";*/

	$catid = (int) $_POST["type"];

	if (!is_valid_id($catid))
		$message = T_("UPLOAD_NO_CAT");
		      if (!empty($_POST['tube']))
            $tube = unesc($_POST['tube']);

	if (!validfilename($fname))
		$message = T_("UPLOAD_INVALID_FILENAME");

	if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
		$message = T_("UPLOAD_INVALID_FILENAME_NOT_TORRENT");

		$shortfname = $torrent = $matches[1];

	if (!empty($_POST["name"]))
		$torrent = $_POST["name"];
        
    $tmpname = $f['tmp_name'];

	//end check form data

	if (!$message) {
	//parse torrent file
	$torrent_dir = $site_config["torrent_dir"];	
	$nfo_dir = $site_config["nfo_dir"];	

	//if(!copy($f, "$torrent_dir/$fname"))
	if(!move_uploaded_file($tmpname, "$torrent_dir/$fname"))
		show_error_msg(T_("ERROR"), T_("ERROR"). ": " . T_("UPLOAD_COULD_NOT_BE_COPIED")." $tmpname - $torrent_dir - $fname",1);

    $TorrentInfo = array();
    $TorrentInfo = ParseTorrent("$torrent_dir/$fname");


	$announce = $TorrentInfo[0];
	$infohash = $TorrentInfo[1];
	$creationdate = $TorrentInfo[2];
	$internalname = $TorrentInfo[3];
	$torrentsize = $TorrentInfo[4];
	$filecount = $TorrentInfo[5];
	$annlist = $TorrentInfo[6];
	$comment = $TorrentInfo[7];
	$filelist = $TorrentInfo[8];

/*
//for debug...
	print ("<br /><br />announce: ".$announce."");
	print ("<br /><br />infohash: ".$infohash."");
	print ("<br /><br />creationdate: ".$creationdate."");
	print ("<br /><br />internalname: ".$internalname."");
	print ("<br /><br />torrentsize: ".$torrentsize."");
	print ("<br /><br />filecount: ".$filecount."");
	print ("<br /><br />annlist: ".$annlist."");
	print ("<br /><br />comment: ".$comment."");
*/
	
	//check announce url is local or external
	if (!in_array($announce, $announce_urls, 1)){
		$external='yes';
    }else{
		$external='no';
	}

	//if externals is turned off
	if (!$site_config["ALLOWEXTERNAL"] && $external == 'yes')
		$message = T_("UPLOAD_NO_TRACKER_ANNOUNCE");
	}
	if ($message) {
		@unlink("$torrent_dir/$fname");
		@unlink($tmpname);
		@unlink("$nfo_dir/$nfofilename");
		show_error_msg(T_("UPLOAD_FAILED"), $message,1);
	}

	//release name check and adjust
	if ($name ==""){
		$name = $internalname;
	}
	$name = str_replace(".torrent","",$name);
	$name = str_replace("_", " ", $name);

	//upload images
	$allowed_types = &$site_config["allowed_image_types"];

	$inames = array();
	for ($x=0; $x < 2; $x++) {
		if (!($_FILES['image.$x']['name'] == "")) {
			$y = $x + 1;

			//if (!preg_match('/^(.+)\.(jpg|gif|png)$/si', $_FILES[image.$x]['name']))
			//	show_error_msg(T_("INVAILD_IMAGE"), T_("THIS_FILETYPE_NOT_IMAGE"), 1);

			if ($_FILES['image$x']['size'] > $site_config['image_max_filesize'])
				show_error_msg(T_("ERROR"), T_("INVAILD_FILE_SIZE_IMAGE"), 1);

			$uploaddir = $site_config["torrent_dir"]."/images/";

			$ifile = $_FILES[image.$x]['tmp_name'];

			$im = getimagesize($ifile);

			if (!$im[2])
				show_error_msg(T_("ERROR"), sprintf(T_("INVALID_IMAGE"), $y), 1);

			if (!array_key_exists($im['mime'], $allowed_types))
				show_error_msg(T_("ERROR"), T_("INVALID_FILETYPE_IMAGE"), 1);

			$ret = SQL_Query_exec("SHOW TABLE STATUS LIKE 'torrents'");
			$row = mysqli_fetch_array($ret);
			$next_id = $row['Auto_increment'];

			$ifilename = $next_id . $x . $allowed_types[$im['mime']];

			$copy = copy($ifile, $uploaddir.$ifilename);

			if (!$copy)
				show_error_msg(T_("ERROR"), sprintf(T_("IMAGE_UPLOAD_FAILED"), $y), 1);

			$inames[] = $ifilename;

		}

	}
	//end upload images
	
	// Make torrent private
   if ($external == "no") {
    require_once("backend/BDecode.php");
    require_once("backend/BEncode.php");
    $dict = BDecode(file_get_contents("$torrent_dir/$fname"));
    $dict["info"]["private"] = 1;
    $fs = fopen("$torrent_dir/$fname", "w");
    fwrite($fs, BEncode($dict));
    fclose($fs);
    $TorrentInfo = array();
    $TorrentInfo = ParseTorrent("$torrent_dir/$fname");
    $infohash = $TorrentInfo[1];
   }
   // End add make torrent private

	//anonymous upload
	$anonyupload = $_POST["anonycheck"]; 
	if ($anonyupload == "yes") {
		$anon = "yes";
	}else{
		$anon = "no";
	}
	    //requests
       $uplrequpload = $_POST["uplreqcheck"];
       if ($uplrequpload == "yes") {
          $uplreq = "yes";
       }else{
          $uplreq = "no";
       }

	$ret = SQL_Query_exec("INSERT INTO torrents (filename, owner, name, descr, image1, image2, category, tube, trailers, added, info_hash, size, numfiles, save_as, announce, external, nfo, torrentlang, anon, uplreq, last_action, imdb) VALUES (".sqlesc($fname).", '".$CURUSER['id']."', ".sqlesc($name).", ".sqlesc($descr).", '".$inames[0]."', '".$inames[1]."', '".$catid."', '".$tube."',".sqlesc($_POST['trailers']).", '" . get_date_time() . "', '".$infohash."', '".$torrentsize."', '".$filecount."', ".sqlesc($fname).", '".$announce."', '".$external."', '".$nfo."', '".$langid."','".$anon."', '".$uplreq."', '".get_date_time()."', ".sqlesc($_POST['imdb']).")");

	$id = mysqli_insert_id($GLOBALS["DBconnector"]);
	
	if (mysqli_errno($GLOBALS["DBconnector"]) == 1062)
		show_error_msg(T_("UPLOAD_FAILED"), T_("UPLOAD_ALREADY_UPLOADED"), 1);

	//Update the members uploaded torrent count
	/*if ($ret){
		SQL_Query_exec("UPDATE users SET torrents = torrents + 1 WHERE id = $userid");*/
        
	if($id == 0){
		unlink("$torrent_dir/$fname");
		$message = T_("UPLOAD_NO_ID");
		show_error_msg(T_("UPLOAD_FAILED"), $message, 1);
	}
    
    rename("$torrent_dir/$fname", "$torrent_dir/$id.torrent"); 
// EDIT TORRENT COMMENT ////////////////////////////
         require_once("backend/BDecode.php");
         require_once("backend/BEncode.php");
         $dict = BDecode(file_get_contents($site_config["torrent_dir"] . "/$id.torrent"));
         $dict['comment'] = "Downloaded & Seeded By ".$site_config['TORRENTCOMMENT'];
         $dict['creation date'] = gmtime();
         file_put_contents($site_config["torrent_dir"] . "/$id.torrent", BEncode($dict));
// EDIT TORRENT COMMENT ////////////////////////////

if (is_array($filelist) && count($filelist)) {
		foreach ($filelist as $file) {
			$dir = '';
			$size = $file["length"];
			$count = count($file["path"]);
			for ($i=0; $i<$count;$i++) {
				if (($i+1) == $count)
					$fname = $dir.$file["path"][$i];
				else
					$dir .= $file["path"][$i]."/";
			}
			SQL_Query_exec("INSERT INTO `files` (`torrent`, `path`, `filesize`) VALUES($id, ".sqlesc($fname).", $size)");
		}
	} else {
		SQL_Query_exec("INSERT INTO `files` (`torrent`, `path`, `filesize`) VALUES($id, ".sqlesc($TorrentInfo[3]).", $torrentsize)");
	}

	if (!is_array($annlist)) {
		$annlist = array(array($announce));
	}
	foreach ($annlist as $ann) {
		foreach ($ann as $val) {
			if (strtolower(substr($val, 0, 4)) != "udp:") {
				SQL_Query_exec("INSERT INTO `announce` (`torrent`, `url`) VALUES($id, ".sqlesc($val).")");
			}
		}
	}

	if ($nfo == 'yes') { 
            move_uploaded_file($nfofilename, "$nfo_dir/$id.nfo"); 
    } 

	//EXTERNAL SCRAPE
	if ($external=='yes' && $site_config['UPLOADSCRAPE']){
		$tracker=str_replace("/announce","/scrape",$announce);	
		$stats 			= torrent_scrape_url($tracker, $infohash);
		$seeders 		= strip_tags($stats['seeds']);
		$leechers 		= strip_tags($stats['peers']);
		$downloaded 	= strip_tags($stats['downloaded']);

		SQL_Query_exec("UPDATE torrents SET leechers='".$leechers."', seeders='".$seeders."',times_completed='".$downloaded."',last_action= '".get_date_time()."',visible='yes' WHERE id='".$id."'"); 
	}
	//END SCRAPE

	write_log("Torrent $id (".htmlspecialchars($name).") was Uploaded by $CURUSER[username]");
	
//	    If ($uplreq=='no') {
//            $msg_shout = sqlesc("[color=#800000]".T_("A_NEW_TORRENT")."[/color] : [url=".$site_config['SITEURL']."/torrents-details.php?id=".$id."]".$name."[/url] ".T_("HAS_BEEN_UPLOADED")." ".($anon == 'no' ? "".T_("BY")." [url=".$site_config['SITEURL']."/account-details.php?id=".$CURUSER['id']."]" .$CURUSER['username']. "[/url]" : "")."");
//        SQL_Query_exec("INSERT INTO shoutbox (userid, date, user, message) VALUES(0,'".get_date_time()."', 'System' ,".$msg_shout.")");
//		}else{
//            $msg_shout = sqlesc("[color=#0000FF]".T_("THE_REQUEST")."[/color] : [url=".$site_config['SITEURL']."/torrents-details.php?id=".$id."]".$name."[/url] ".T_("HAS_BEEN_FILLED")." ".($anon == 'no' ? "".T_("BY")." [url=".$site_config['SITEURL']."/account-details.php?id=".$CURUSER['id']."]" .$CURUSER['username']. "[/url]" : "")."");
//        SQL_Query_exec("INSERT INTO shoutbox (userid, date, user, message) VALUES(0,'".get_date_time()."', 'System' ,".$msg_shout.")");
//            }
	
	If ($uplreq=='yes') {
	$msg_shout = sqlesc("[color=#0000FF]".T_("THE_REQUEST")."[/color] : [url=".$site_config['SITEURL']."/torrents-details.php?id=".$id."]".$name."[/url] ".T_("HAS_BEEN_FILLED")." ".($anon == 'no' ? "".T_("BY")." [url=".$site_config['SITEURL']."/account-details.php?id=".$CURUSER['id']."]" .$CURUSER['username']. "[/url]" : "")."");
        SQL_Query_exec("INSERT INTO shoutbox (userid, date, user, message) VALUES(0,'".get_date_time()."', 'System' ,".$msg_shout.")");
		  }
	
	//shoutbox torrent announce shit////
//	$user = ( $anon == 'yes' || $CURUSER['privacy'] == 'strong' ) ? 'Anonymous' : $CURUSER['username'];
//$message = "New Torrent: [url=".$site_config['SITEURL']."/torrents-details.php?id=".$id."]".$name."[/url] has been uploaded by $CURUSER[username]";
//SQL_Query_exec("INSERT INTO `shoutbox` (`uid`, `name`, `message`, `date`) VALUES ('0', 'System', " . sqlesc($message) . ", '".get_date_time()."')");

	//insert email notif, irc, req notif, etc here
	


	    if ($CURUSER["edit_torrents"] == "yes")
	        if(get_user_class($CURUSER) > 4){
                if ($_POST["sticky"] == "yes"){
                $updateset[] = "sticky = 'yes'";
                 }else{
                $updateset[] = "sticky = 'no'";
        }
}
        $updateset[] = "freeleech = '".intval($_POST["freeleech"])."'";
    	SQL_Query_exec("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $id");

	//Uploaded ok message (update later)
	if ($external=='no')
		$message = sprintf( T_("TORRENT_UPLOAD_LOCAL"), $name, $id, $id );

	else
		$message = sprintf( T_("TORRENT_UPLOAD_EXTERNAL"), $name, $id );
	show_error_msg(T_("UPLOAD_COMPLETE"), $message, 1);
autoclean();

	die();
}//takeupload


///////////////////// FORMAT PAGE ////////////////////////

stdhead(T_("UPLOAD"));

begin_frame(T_("UPLOAD_RULES"));
	echo "<b>".stripslashes($site_config["UPLOADRULES"])."</b>";
	echo "<br />";
end_frame();

begin_frame(T_("UPLOAD"));
?>

<table border="0" cellspacing="0" cellpadding="6" align="center">
<?php
echo "<br />";
print ("<tr><td align='right' valign='top'>" . T_("ANNOUNCE_URL") . ": </td><td align='left'>");

while (list($key,$value) = thisEach($announce_urls)) {

	
	
	echo ("<input type='text' id='myInput' size='30' value='" . $value . "' />");

}
?>

<!-- The button used to copy the announce -->
	<style>
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 140px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    bottom: 150%;
    left: 50%;
    margin-left: -75px;
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltip .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>
	
<div class="tooltip">
<button onclick="myFunction()" onmouseout="outFunc()">
  <span class="tooltiptext" id="myTooltip">Copy to clipboard</span>
  Copy Announce
  </button>
</div>
	<script>
function myFunction() {
  var copyText = document.getElementById("myInput");
  copyText.select();
  document.execCommand("Copy");
  
  var tooltip = document.getElementById("myTooltip");
  tooltip.innerHTML = "Copied: " + copyText.value;
}

function outFunc() {
  var tooltip = document.getElementById("myTooltip");
  tooltip.innerHTML = "Copy to clipboard";
}
</script>
	
	<!--END stupid copy button-->
	
	
<form name="upload" enctype="multipart/form-data" action="torrents-upload.php" method="post">
<input type="hidden" name="takeupload" value="yes" />

<?php
if ($site_config["ALLOWEXTERNAL"]){
	echo "<br /><b>".T_("THIS_SITE_ACCEPTS_EXTERNAL")."</b>";
}
print ("<tr><td colspan='2' align='center'><font color='red' size ='2'>".T_("REQUIRED_FIELDS")."</font></td></tr>");
print ("</td></tr>");
print ("<tr><td align='right'>". T_("TORRENT_FILE") . ":<font color='red' size ='3'> *</font> </td><td align='left'  class='table_col2'> <input type='file' name='torrent' size='50' value='" . $_FILES['torrent']['name'] . "' />\n</td></tr>");
$category = "<select name=\"type\">\n<option value=\"0\">" . T_("CHOOSE_ONE") . "</option>\n";

$cats = genrelist();
foreach ($cats as $row)
	$category .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["parent_cat"]) . ": " . htmlspecialchars($row["name"]) . "</option>\n";

$category .= "</select>\n";
print ("<tr><td align='right'>" . T_("CATEGORY") . ":<font color='red' size ='3'> *</font> </td><td align='left'  class='table_col2'>".$category."</td></tr>");
    #################### - REQUEST FILLED START - ####################

    //Request filled?
    if ($site_config["REQUESTSON"]){
            $sql_request = "SELECT `id`, `profilled`, `request` FROM requests WHERE filledby=0 AND profilled=$CURUSER[id] ORDER BY `request` ASC";
            $res = mysqli_query($GLOBALS["DBconnector"],$sql_request);
    if (mysqli_num_rows($res) > 0) {
            $request = "<select name=\"request\">\n<option value=\"0\">(".T_("REQUEST_UPL1").")</option>\n";
    while ($row = mysqli_fetch_array($res)) {
            $request .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["request"]) . "</option>\n";
    }
            $request .= "</select>\n";
    $uplreqcheck = (isset($uplreqcheck));
    if ($res["profilledby"]=="0" );
            print("<tr><td align=right><b><font color='red' size ='2'>".T_("REQUEST_FILL")." : </font></b></td><td colspan='2' class='table_col1' align=left>&nbsp;$request<br>&nbsp;<input name='uplreqcheck' value='yes' type='radio' " . ($uplreqcheck ? " checked='checked'" : "") . " />".T_("YES")." <input name='uplreqcheck' value='no' type='radio' " . (!$uplreqcheck ? " checked='checked'" : "") . " />".T_("NO")." &nbsp;".T_("REQUEST_UPL2")."</i></td></tr>");
    }
    }

    #################### - REQUEST FILLED END - ######################

print ("<tr><td align='right'>" .T_("NFO"). ": </td><td align='left'> <input type='file' name='nfo' size='50' value='" . $_FILES['nfo']['name'] . "' /><br />\n</td></tr>");
print ("<tr><td align='right'>" . T_("TORRENT_NAME") . ": </td><td align='left'><input type='text' name='name' size='60' value='" . $_POST['name'] . "' /><br />".T_("THIS_WILL_BE_TAKEN_TORRENT")." \n</td></tr>");
print ("<tr><td align='right'>" .T_("IMDB")."</td><td align='left'><input type='text' name='imdb' size='50' value='" . $_POST['imdb'] . "' />&nbsp;<a href='http://www.imdb.com' target='_blank'><img border='0' src='images/imdb_upload.png' width='80' height='80' title='Click here to go to IMDb'></a><br />".T_("IMDB_INFO")."</td></tr>");
print ("<tr><td></td></tr>");
print ("<tr><td></td></tr>");
print ("<tr><td colspan='2' align='center'><hr/></td></tr>");

print ("<tr><td colspan='2' align='center'><font color='green' size ='5'>".T_("ADD_TRAILER")."</font></td></tr>");
print ("<tr><td colspan='2' align='center'><font color='green' size ='2'>".T_("TUBE_OR_IMDB")."</font></td></tr>");
print ("<tr><td align='right'>" .T_("IMDB_TRAILER").": <br/><span style='color:green'id='second'>Enabled</span></td><td align='left' class='table_col2'><input id='imdb' type='text' name='trailers' size='50' value='" . $_POST['trailers'] . "' />&nbsp;<a href='http://www.imdb.com' target='_blank'><img border='0' src='images/movietrailers.png' width='50' height='50' title='Click here to go to IMDb'></a><br />".T_("IMDB_TRAILER_INFO")."</td></tr>");
print ("<tr><td align=right>".T_("VIDEOTUBE").": <br/><span style='color:green'id='first'>Enabled</span></td><td align='left' class='table_col2'><input id='youtube' type='text' name='tube' size='50' />&nbsp;<a href=\"https://www.youtube.com\" target='_blank'><img border='0' src='images/youtube.png' width='50' height='50' title='Click here to go to Youtube'></a><br/><i>".T_("FORMAT").": </i> <span style='color:#FF0000'><b> ".T_("YOUTUBEFORMATINFO")."</b></SPAN></td></tr>");
//print ("<tr><td align='right'>".T_("LANGUAGE").": </td><td align='left'>".$language."</td></tr>");
//print ("<TR><TD align=right>YouTube Video Link: </td><td align=left><input type=\"text\" name=\"tube\" size=\"60\" /><br />Links should be in this format<br><font color=red><b>http://www.youtube.com/watch?v=Jc9KR3tOP</b></font></td></tr>");

print ("<tr><td></td></tr>");
print ("<tr><td></td></tr>");
print ("<tr><td colspan='2' align='center'><hr/></td></tr>");
print ("<tr><td colspan='2' align='center'>".T_("MAX_FILE_SIZE").": ".mksize($site_config['image_max_filesize'])."<br />".T_("ACCEPTED_FORMATS").": ".implode(", ", array_unique($site_config["allowed_image_types"]))."<br /></td></tr><tr><td align='right'>".T_("IMAGE")." 1:&nbsp;&nbsp;</td><td><input type='file' name='image0' size='50' /></td></tr><tr><td align='right'>".T_("IMAGE")." 2:&nbsp;&nbsp;</td><td><input type='file' name='image1' size='50' /></td></tr>");
if ($row["external"] != "yes" && $CURUSER["edit_torrents"] == "yes"){
    echo "<tr><td align='right'>".T_("FREE_LEECH").": </td><td><input type=\"checkbox\" name=\"freeleech\"" . (($row["freeleech"] == "1") ? " checked=\"checked\"" : "" ) . " value=\"1\" />".T_("FREE_LEECH_MSG")."<br /></td></tr>";
	echo "<tr><td align=right>".T_("STICKY")."</td><td><input type='checkbox' name='sticky'" .
(($row["sticky"] == "yes") ? " checked='checked'" : "" ) . " value='yes' /></td></tr>";
}
?>
<script type="text/javascript">
document.getElementById("youtube").onblur = function () {
    if (this.value.length > 0) {
        document.getElementById("imdb").disabled=true;
  document.getElementById("second").innerHTML = "<span style='color:#FF0000'>Disabled</span>";
    }else {
        document.getElementById("imdb").disabled = false;
        document.getElementById("second").innerHTML = "Enabled";
    }
}
    
    document.getElementById("imdb").onblur = function () {
    if (this.value.length > 0) {
        document.getElementById("youtube").disabled=true;
  document.getElementById("first").innerHTML = "<span style='color:#FF0000'>Disabled</span>";      
    }else {
        document.getElementById("youtube").disabled = false;
        document.getElementById("first").innerHTML = "Enabled";
    }
    
}
</script>
<?php



$language = "<select name=\"lang\">\n<option value=\"0\">".T_("UNKNOWN_NA")."</option>\n";

$langs = langlist();
foreach ($langs as $row)
	$language .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";

$language .= "</select>\n";


if ($site_config['ANONYMOUSUPLOAD'] && $site_config["MEMBERSONLY"] ){ ?>
	<tr><td align="right"><?php echo T_("UPLOAD_ANONY");?>: </td><td><?php printf("<input name='anonycheck' value='yes' type='radio' " . ($anonycheck ? " checked='checked'" : "") . " />Yes <input name='anonycheck' value='no' type='radio' " . (!$anonycheck ? " checked='checked'" : "") . " />No"); ?> &nbsp;<i><?php echo T_("UPLOAD_ANONY_MSG");?></i>
	</td></tr>
	<?php
}




print ("<tr><td align='center' colspan='2'>" . T_("DESCRIPTION") . "</td></tr></table>");

require_once("backend/bbcode.php");
print textbbcode("upload","descr","$descr");
?>

<br /><br /><center><input type="submit" value="<?php echo T_("UPLOAD_TORRENT"); ?>" /><br />
<i><?php echo T_("CLICK_ONCE_IMAGE");?></i>
</center>
</form>


    

<?php
end_frame();
stdfoot();
?>
