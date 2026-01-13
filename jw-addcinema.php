<?php
//
//      TorrentTrader v2.x
//      JWcinema 
//      $CreatedDate: 2012-07-08 12:38:09 +0000 (Mon, 08 July 2012) $
//      $CreatedBy: Torrentor Tracker $
//      http://torrentor.try.hu
//
//      http://www.torrenttrader.org
//	Modified for TT.2.08 - Feb 20, 2018 By MicroMonkey cuz I was bored and shit. Added Youtube metadata API v3
//	Get your API keys at https://support.google.com/googleapi/answer/6158862?hl=en

require_once("backend/functions.php");
dbconn(false);
loggedinonly();

if (get_user_class() < 7)
show_error_msg("Error!!!", "You do not have the rights to access this page!!");

stdhead("Add movies");
$action = $_GET["action"];

if(empty($action)) {

$get_cinema = SQL_Query_exec("SELECT * FROM jwcinema ORDER BY id DESC");

begin_frame("Movies","true","20%");

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableinborder">
<tr><tr class="tabletitle" width="100%">
<td width="100%"><a href="jw-addcinema.php?action=create_cinema"><img src="images/add.png" width="140px" height="140px" title="Add" border="0"></a>&nbsp;&nbsp;&nbsp;&nbsp;
Total movies: <?php echo mysqli_num_rows($get_cinema); ?></td>
</tr></tr><tr><td width="100%" class="tablea">
</table>

<br />

<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<td class="tableb"><center>ID</center></td>
<td class="tableb">Title</td>
<td class="tableb">Active</td>
<td class="tableb" colspan="2">Options</td>
</tr>
<?php
while($data_cinema = mysqli_fetch_array($get_cinema)) {

if ($data_cinema["cinemaonline"] == 'yes') {
$aktiv = "Yes";
} else {
$aktiv = "No";
}

?>
<tr>
<td class="tablea"><center><?php echo $data_cinema["id"]; ?></center></td>
<td class="tablea"><a title=View target=_blank href="jw-view-cinema.php?id=<?php echo $data_cinema["id"]; ?>">
<?php echo $data_cinema["name"]; ?></a></td>
<td class="tablea"><?php echo $aktiv; ?></td>
<td class="tablea"><a href="jw-addcinema.php?action=edit_cinema&id=<?php echo $data_cinema["id"]; ?>">
<img src="images/edit.png" width="40px" height="40px" title="Edit" border="0"></a>&nbsp;&nbsp;
<a href="jw-addcinema.php?action=delete_cinema&id=<?php echo $data_cinema["id"]; ?>" onclick="return confirm('Are you sure you want to delete this?')";>
<img src="images/delete.png" width="40px" height="40px" title="Delete" border="0"></a></td>
</tr>
<?php } ?>
</table>

<?php
end_frame();
}

if($action == "create_cinema") {
begin_frame("Add Movies","true","20%");

?>
<form method="post" action="jw-addcinema.php?action=create_cinema_do">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableinborder">
<tr>
<td width="100%" align="center"><b>HTML code enabled! 
Long lines wrapped to use &lt;br&gt; code!<br>Supported formats: .avi .flv .mp4 .mp3<br>
YouTube videos. Example: https://www.youtube.com/watch?v=Iey4pbqfDlk<b></td>
</tr></table>
<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<tr>
<td class="tableb">Title:</td>
<td class="tablea"><input type="text" name="name" size="80" placeholder="Youtube will automatically add this" /></td>
</tr>
<tr>
<td class="tableb">Poster - URL:</td>
<td class="tablea"><input type="text" name="poster" size="80" placeholder="Youtube will automatically add this" /></td>
</tr>
<tr>
<td class="tableb">File - URL: <font color='red' size ='3'> *</font></td>
<td class="tablea"><input type="text" name="aviurl" size="80" /></td>
</tr>
<tr>
<td class="tableb">Description:</td>
<td class="tablea">
<textarea name="description" id="textarea-WYSIWYG" style="height: 150px; width: 500px;"><?php echo T_("MOVIES_DESCRIPTION");?></textarea>
				<script language="javascript1.2">
					if(navigator.userAgent.toLowerCase().indexOf('chrome/1') == -1)
					{
					// Overwrite WYSIWYG Width and Height
					wysiwygWidth = '100%';
					wysiwygHeight = 150;
					generate_wysiwyg('textarea-WYSIWYG');
					}
				</script>
				<script type="text/javascript">
					//	Editor not loaded?
					if ($("#wysiwygtextarea-WYSIWYG").length == 0)
					{
						// restore textarea
						$("#textarea-WYSIWYG").show();
					}
				</script>
</td>
</tr>
<tr>
<td class="tableb">Info:</td>
<td class="tablea">
<textarea name="info" style="height: 100px; width: 500px;"><?php echo T_("MOVIES_DETAILS");?></textarea></td>
</tr>
<tr>
<td class="tablea"><input name="submit" type="submit" value="Save" /> <input name="reset" type="reset" /></td>
<td><a href="jw-addcinema.php">Cancel</a></td>
</tr>
</table>
</form>
<?php
end_frame();
}
if($action == "edit_cinema") {
begin_frame("Edit Movie","true","20%");

$id = $_GET["id"];
$data_cinema = mysqli_fetch_array(SQL_Query_exec("SELECT * FROM jwcinema WHERE id = '$id'"));
?>
<form method="post" action="jw-addcinema.php?action=edit_cinema_do&id=<?=$id?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableinborder">
<tr>
<td width="100%" align="center"><b>HTML code enabled! 
Long lines wrapped to use &lt;br&gt; code!<br>Supported formats: .avi .flv .mp4 .mp3<br>
YouTube videos. Example: https://www.youtube.com/watch?v=Iey4pbqfDlk<b></td>
</tr></table>
<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<tr>
<td class="tableb">Title:</td>
<td class="tablea"><input type="text" name="name" size="80" value="<?php echo $data_cinema["name"]; ?>" /></td>
</tr>
<tr>
<td class="tableb">Poster - URL:</td>
<td class="tablea"><input type="text" name="poster" size="80" value=" <?php echo $data_cinema["poster"]; ?>"  /></td>
</tr>
<tr>
<td class="tableb">File - URL:</td>
<td class="tablea"><input type="text" name="aviurl" size="80" value="<?php echo $data_cinema["aviurl"]; ?>" /></td>
</tr>
<tr>
<td class="tableb">Description:</td>
<td class="tablea">
<textarea name="description" id="textarea-WYSIWYG" style="height: 150px; width: 500px;"><?php echo $data_cinema["description"]; ?></textarea>
				<script language="javascript1.2">
					if(navigator.userAgent.toLowerCase().indexOf('chrome/1') == -1)
					{
					// Overwrite WYSIWYG Width and Height
					wysiwygWidth = '100%';
					wysiwygHeight = 150;
					generate_wysiwyg('textarea-WYSIWYG');
					}
				</script>
				<script type="text/javascript">
					//	Editor not loaded?
					if ($("#wysiwygtextarea-WYSIWYG").length == 0)
					{
						// restore textarea
						$("#textarea-WYSIWYG").show();
					}
				</script>
</td>
</tr>
<tr>
<td class="tableb">Info:</td>
<td class="tablea">
<textarea name="info" style="height: 100px; width: 500px;"><?php echo $data_cinema["info"]; ?></textarea>
</td>
</tr>

<?php
tr("Active:", "<input type=\"checkbox\" name=\" cinemaonline\"" . ($data_cinema["cinemaonline"] == "yes" ? " checked=\"checked\"" : "") . "> Yes, active",1);

?>
<tr>
<td class="tablea"><input name="submit" type="submit" value="Save" /> <input name="reset" type="reset" /></td>
<td><a href="jw-addcinema.php">Cancel</a></td>
</tr>
</table>
</form>
<?php
end_frame();
}
if($action == "create_cinema_do") {
	
$videoURL = $_POST["aviurl"];
$urlArr = explode("/",$videoURL);
$urlArrNum = count($urlArr);
//$youtubeVideoId = $urlArr[$urlArrNum - 1];
$youtubeVideoId = substr($videoURL, 32);
$thumbURL = 'https://i.ytimg.com/vi/'.$youtubeVideoId.'/hqdefault.jpg';
function getYouTubeVideoID($videoURL) {
$queryString = parse_url($videoURL, PHP_URL_QUERY);
parse_str($queryString, $params);
if (isset($params['v']) && strlen($params['v']) > 0) {
   return $params['v'];
} else {
   return "";
    }
}
$apikey = $site_config['YOUTUBEAPIKEY']; 
$api_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics&id='.getYouTubeVideoID($videoURL).'&key='.$apikey;     
$data = json_decode(file_get_contents($api_url));
$error = 0;
$name = sqlesc($data->items[0]->snippet->title);
//$name = $_POST["name"];
$poster = sqlesc($thumbURL);
//$poster = sqlesc($_POST["poster"]);
$aviurl = sqlesc($_POST["aviurl"]);
//$description = sqlesc($_POST["description"]);
$description = sqlesc($data->items[0]->snippet->description);
$info = sqlesc($data->items[0]->snippet->description);
//$info = sqlesc($_POST["info"]);
$width = sqlesc($_POST["width"]);
$height = sqlesc($_POST["height"]);


if(empty($name) OR empty($description) OR empty($poster ) OR empty($aviurl) OR empty($width) OR empty($height)) { $error = 1; }

begin_frame("Add Movie", false, "350px");
if($error == 1) {
?>
<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<td class="tableb">Empty field(s)..... [<a href="jw-addcinema.php?action=create_cinema">Back</a>]</td>
</tr>
</table>
<?php
} else {

SQL_Query_exec("INSERT INTO jwcinema (name, poster, aviurl, description, info, width, height) VALUES ($name, $poster, $aviurl, $description, $info, $width, $height)");

?>
<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<td class="tableb"> <?php autolink ("jw-addcinema.php", "<b><font color='#ff0000'>Updated OK</font></b>")?></td>
</tr>
</table>
<?php
}
end_frame();
}
if($action == "edit_cinema_do") {
$videoURL = $_POST["aviurl"];
$urlArr = explode("/",$videoURL);
$urlArrNum = count($urlArr);
$youtubeVideoId = substr($videoURL, 32);
$thumbURL = 'https://i.ytimg.com/vi/'.$youtubeVideoId.'/hqdefault.jpg';	
function getYouTubeVideoID($videoURL) {
$queryString = parse_url($videoURL, PHP_URL_QUERY);
parse_str($queryString, $params);
if (isset($params['v']) && strlen($params['v']) > 0) {
    return $params['v'];
} else {
    return "";
    }
}
$apikey = $site_config['YOUTUBEAPIKEY']; 
$api_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics&id='.getYouTubeVideoID($videoURL).'&key='.$apikey;     
$data = json_decode(file_get_contents($api_url));
$error = 0;
$id = $_GET["id"];
$cinemaonline = ($_POST["cinemaonline"]!= "" ? "yes" : "no");
	//The comments below are for the do-edit. If you like, you can have youtube override
$name = mysqli_real_escape_string($_POST["name"]);
//$name = mysqli_real_escape_string($data->items[0]->snippet->title); 
$info = mysqli_real_escape_string($_POST["info"]); 
//$info = mysqli_real_escape_string($data->items[0]->snippet->description); 
$description = mysqli_real_escape_string($_POST["description"]); 
//$description = mysqli_real_escape_string($data->items[0]->snippet->description); 
$poster = $thumbURL;
$aviurl = $_POST["aviurl"];
$width = $_POST["width"];
$height = $_POST["height"];

if(empty($name) OR empty($cinemaonline) OR empty($aviurl)) { $error = 1; }

begin_frame("Edit Movie", false, "350px");
if($error == 1) {
?>
<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<td class="tableb">Empty field(s)..... [<a href="jw-addcinema.php?action=edit_cinema&id=<?=$id?>">Back</a>]</td>
</tr>
</table>
<?php
} else {
SQL_Query_exec("UPDATE jwcinema SET name='$name', info='$info', description='$description', poster='$poster', aviurl='$aviurl', width='$width', height='$height', cinemaonline='$cinemaonline' WHERE id = '$id'");
?>
<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<td class="tableb"> <?php autolink ("jw-addcinema.php", "<b><font color='#ff0000'>Updated OK</font></b>")?></td>
</tr>
</table>
<?php
}
end_frame();
}
if($action == "delete_cinema") {

$id = $_GET["id"];
SQL_Query_exec("DELETE FROM jwcinema WHERE id = '$id'");
begin_frame("Delete Movie", false, "350px");
?>
<table width="100%" border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr>
<td class="tableb"> <?php autolink ("jw-addcinema.php", "<b><font color='#ff0000'>Video Deleted</font></b>")?></td></tr>
</table>
<?php
end_frame();
}
stdfoot();
?>