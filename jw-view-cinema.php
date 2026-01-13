<?php
//
//      TorrentTrader v2.x
//      JWcinema 
//      $CreatedDate: 2012-07-08 12:38:09 +0000 (Mon, 08 July 2012) $
//      $CreatedBy: Torrentor Tracker $
//      http://torrentor.try.hu
//
//      http://www.torrenttrader.org
//
require_once("backend/functions.php");
dbconn(false);
loggedinonly();

$id = (int) $_GET["id"];
if (!is_valid_id($id))
    show_error_msg("Error ","Invalid ID!",1);

$res = SQL_Query_exec("SELECT * FROM jwcinema WHERE id = $id ORDER BY id ASC");
$arr = mysqli_fetch_array($res);
stdhead(T_("WATCHING")." " . $arr["name"] . " ");
begin_frame(" ".$arr['name']." ".$edit." ", false, "10px");

$size = @getimagesize(''.$arr['poster'].''); 
$heightx = $size[1];
$widthx = intval($size[0]);
if ($widthx > 140){
$widthx = 140;
$percent = ($size[0] / $widthx);
$heightx = ($size[1] / $percent);
}
if ($heightx > 160){
$heightx = 160;
$percent = ($size[1] / $heightx);
$widthx = ($size[0] / $percent);
}


print ("<center><br>");
print ("<script type='text/javascript' src='mediaplayer/jwplayer-7.12.3/jwplayer.js'></script>");

print ("<div id='mediaspace'>The fucking player isn't loading, something is fucked...</div>");
print ("<script type='text/javascript'> jwplayer.key= '".$site_config['JWPLAYER']."' ;

  jwplayer('mediaspace').setup({
	logo: {
		file: '/mediaplayer/jwplayerlogo.png',
		link: 'http://yoursite.com'
	},
    'flashplayer': 'mediaplayer/jwplayer-7.12.3/player.swf',
    'file': '".$arr["aviurl"]."',
    'image': '".$arr['poster']."',
	'title': '".$arr['name']."',
	'height': 615,
    'width': 860,
	'skin': {
  name: 'six'
  }
	
  });

</script>");
/*///media elements player test
print ("<center><br>");
print ("<script src='mediaplayer/mediaelements/mediaelement-and-player.min.js'></script>");
print ("<link rel='stylesheet' href='mediaplayer/mediaelements/mediaelementplayer.css';
</script>");

print ("<video src='".$arr["aviurl"]."' width='860' height='615'class='mejs__player'data-mejsoptions='{'pluginPath': '/path/to/shims/', 'alwaysShowControls': 'true'}></video>");

/////End test*/
end_frame();

begin_frame("Navigation");
print ("<br><br><center><a href=jw-cinema.php><img src=images/more-videos.png title=Videos border=0 width=300 height=104></a>&nbsp;&nbsp;&nbsp;&nbsp;");
if ($CURUSER["control_panel"]=="yes"){
//if ($CURUSER["class"]=="7")

print ("<a href=jw-addcinema.php?action=edit_cinema&id=".$arr["id"].">
<img src=images/edit.png title=\"Edit\" border=0 width=140 height=94 /></a>
&nbsp;&nbsp;&nbsp;&nbsp;<a href=jw-addcinema.php?action=delete_cinema&id=".$arr["id"]." onclick=\"return confirm('Are you sure you want to delete this?')\";>
<img src=images/delete.png width=\"100px\" height=\"100px\" title=\"Delete\" border=0 />
&nbsp;&nbsp;&nbsp;&nbsp;<a href=jw-addcinema.php?action=create_cinema><img src=images/add.png width=\"150px\" height=\"150px\" title=\"Add\" border=\"0\"></a></center>");

}
end_frame();

begin_frame("Info");

$arr = @mysqli_fetch_array(@SQL_Query_exec("SELECT poster, description, info FROM jwcinema WHERE id= '$id' "));
$poster = htmlspecialchars($arr["poster"]);

print ('<p align="center">');
print ('<BR /><table class="ttable_headinner" align="center" border="0" cellpadding="0" cellspacing="0" width="70%">');
print ('<td align="left" width="90%">'.$arr["description"].'</td>');
print ('<td width="20%">');

if ($poster) {
$poster = htmlspecialchars($arr["poster"]);
echo '<img src="'.$arr["poster"].'" width="200px" height="160px" title="Poster" border="0" />'; } 
elseif  (!$poster) {
$poster = htmlspecialchars(0);
echo '<img src="images/no-image.png" width="140px" height="160px" title="Poster" border="0" />';
 }

print ('</td>');
print ('</tr></table><BR />');
print ('</p>');
end_frame();
stdfoot();
?>