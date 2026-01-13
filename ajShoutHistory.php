<?php
require_once("backend/functions.php");

dbconn(false);

global $CURUSER;

//GET CURRENT USERS THEME AND LANGUAGE
if ($CURUSER){
   $ss_a = @mysqli_fetch_array(SQL_Query_exec("select uri from stylesheets where id=" . $CURUSER["stylesheet"]));
   if ($ss_a)
      $THEME = $ss_a[uri];
      $lng_a = @mysqli_fetch_array(SQL_Query_exec("select uri from languages where id=" . $CURUSER["language"]));
   if ($lng_a)
      $LANGUAGE =$lng_a[uri];
}else{//not logged in so get default theme/language
   $ss_a = mysqli_fetch_array(SQL_Query_exec("select uri from stylesheets where id='" . $site_config['default_theme'] . "'"));
   if ($ss_a)
      $THEME = $ss_a[uri];
   $lng_a = mysqli_fetch_array(SQL_Query_exec("select uri from languages where id='" . $site_config['default_language'] . "'"));
   if ($lng_a)
      $LANGUAGE = $lng_a[uri];
}
@mysqli_free_result($lng_a);
@mysqli_free_result($ss_a);
if ($CURUSER){
if(!isset($_GET['history'])){
?>
<HTML>
<HEAD>
<TITLE><?php echo $site_config['SITENAME']; ?>talky typy box</TITLE>
<META HTTP-EQUIV="refresh" content="100">
<link rel="stylesheet" type="text/css" href="<?php echo $site_config['SITEURL']; ?>/themes/<?php echo $THEME; ?>/theme.css" />
</HEAD>
<body class="shoutbox_body">
<?php
   echo '<div class="shoutbox_contain"><table border="1" background="#ffffff" style="width: 99%; table-layout:fixed">';
}else{
?>
<HTML>
<HEAD>
<TITLE><?php echo $site_config['SITENAME']; ?>Shoutbox History</TITLE>
<META HTTP-EQUIV="refresh" content="100">
<link rel="stylesheet" type="text/css" href="<?php echo $site_config['SITEURL']; ?>/themes/<?php echo $THEME; ?>/theme.css" />
</HEAD>
<body class="shoutbox_body">
<?php
   //stdhead("Shoutbox History",0);
   //begin_frame("Shoutbox History");
   echo '<div class="shoutbox_history">';

   $query = 'SELECT COUNT(ajshoutbox.id) FROM ajshoutbox';
   $result = SQL_Query_exec($query);
   $row = mysqli_fetch_row($result);
   echo '<div align="middle">Pages: ';
   $count = $row[0];
   $perpage = 10;
   //$i = 1;
   list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] ."?history=1&");
      
      echo $pagertop;
   

   echo '</div></br><center><table cellpadding="5" cellspacing="0" border="1" background="#ffffff" style="width: 99%; table-layout:fixed">';
}
@mysqli_free_result($result);

   if (isset($_GET['del'])){

        if (is_numeric($_GET['del'])){
                $query = "SELECT * FROM ajshoutbox WHERE id=".$_GET['del'];
                $result = SQL_Query_exec($query);
        }else{
                echo "invalid msg id STOP TRYING TO INJECT SQL";
                exit;
        }

        $row = mysqli_fetch_row($result);
               
        if ((get_user_class() >= 4) || ($CURUSER['username'] == $row[1]) ){   
                $query = "DELETE FROM ajshoutbox WHERE id=".$_GET['del'];
                //write_log("<B>".SHOUTBOX_DEL."   ".$CURUSER['username']."</b>");
                SQL_Query_exec($query);   
        }
}   
      
   $query = 'SELECT s.*, u.avatar FROM ajshoutbox s left join users u on s.uid=u.id left join groups g on g.group_id=u.class ORDER BY id DESC '.$limit;

$result = SQL_Query_exec($query);
$alt = false;

while ($row = mysqli_fetch_assoc($result)) {

     $i = 0; $i < $num; ++$i;
   if ($alt){   
      echo '<tr class="shoutbox_noalt">';
      $alt = false;
   }else{
      echo '<tr class="shoutbox_alt">';
      $alt = true;
   }

   echo '<td align="center" style="font-size: 12px; width: 118px;">';
   //echo "<div align='left' style='float: left'>";
if ($CURUSER){
   //echo date('jS M, g:ia', utc_to_tz_time($row['date']));
   
   
$a = SQL_Query_exec("SELECT class FROM `users` WHERE id = ".$row[uid]." ");
$ab = @mysqli_fetch_row($a);
switch ($ab[0]) {
case 8: // Site owner:
$name = class_user($row[name]);
break;
case 7: // Administrator:
$name = class_user($row[name]);
break;
case 6: // Super Moderator
$name = class_user($row[name]);
break;
case 5: // Moderator
$name = class_user($row[name]);
break;
case 4: // Uploader
$name = class_user($row[name]);
break;
case 3: // VIP
$name = class_user($row[name]);
break;
case 2: // Power User
$name = class_user($row[name]);
break;
case 1: // User
$name = class_user($row[name]);
break;
case 0: // System
$name = "<font color=red>".$row[name]."</font>";
}
@mysqli_free_result($a);

$ol3 = @mysqli_fetch_array(SQL_Query_exec("SELECT u.warned FROM users u WHERE u.id=".$row["uid"]));
   $warn=$ol3['warned'];
if($warn=="yes"){
   $warn="<img src=images/warned.png alt=warned title=warned border=0>";
}else{
   $warn="";
}
@mysqli_free_result($ol3);
$ol3 = @mysqli_fetch_array(SQL_Query_exec("SELECT u.donated FROM users u WHERE u.id=".$row["uid"]));
   $don=$ol3['donated'];
if($don>0){
   $don="<img src=images/star.png alt=donor title=donor border=0>";
}else{
   $don="";
}
@mysqli_free_result($ol3);
	$uid  = $row[uid];
      echo "<span class='name'><a href='account-details.php?id=".$uid."'>".$name."</a>&nbsp;".$don."".$warn."</span>";


if ( ($CURUSER["edit_users"]=="yes") || ($CURUSER['username'] == $row['user']) ){
      echo "</td><td align='left' style='font-size: 12px; padding-left: 1px'>&nbsp;".date('M jS Y, H:i:s', utc_to_tz_time($row['date']))."<div style='float: right;'><a href='".$site_config['SITEURL']."/chatedit.php?action=edit&msgid=".$row['id']."' style='font-size: 8px'><img id='EditSwap' src='images/ajshoutbox/shout_edit_gray.gif' title='edit' border='0'></a><a href='".$site_config['SITEURL']."/ajShoutHistory.php?del=".$row['id']."' style='font-size: 8px'><img id='DeleteSwap' src='images/ajshoutbox/shout_delete_gray.png' border='0' title='".T_("_DEL_")."' border=0 title=del></a></td></tr>";   
}else{
      echo "</td><td align='left' style='font-size: 12px; padding-left: 1px'>".date('M jS Y, H:i:s', utc_to_tz_time($row['date']))."</td></tr>";   
}
   
   
   
   
   
   $avatar=$row['avatar'];
//$AgetHeaders = @get_headers($avatar);
if ($avatar) {
$avatar="<img src=".stripslashes($row['avatar'])." border='0' width='50' height=''>";
}
elseif($row["userid"]=="0"){
$avatar="<img src='HD.png' border='0' width='50' height=''>";
}
else if(!$avatar || $avatar=="") {
$avatar="<img src='images/default_avatar.png' border='0' width='50' height=''>";
}
echo "<tr><td align='center'>$avatar<br />";
}
   //echo "</div>";


   


if($row["userid"]=="2438"){
$user="<font size=\"4\">
<script>

// ********** MAKE YOUR CHANGES HERE

var text=\"kickass\"     //   YOUR TEXT
var speed=80    //   SPEED OF FADE - Higher=faster/Lower=slower

// ********** LEAVE THE NEXT BIT ALONE!

// **** Do Not Alter Code Below ****
if (document.all||document.getElementById){
document.write('<span id=\"highlight\">' + text + '</span>')
var storetext=document.getElementById? document.getElementById(\"highlight\") : document.all.highlight
}
else
document.write(text)
var hex=new Array(\"00\",\"14\",\"28\",\"3C\",\"50\",\"64\",\"78\",\"8C\",\"A0\",\"B4\",\"C8\",\"DC\",\"F0\")
var r=1
var g=1
var b=1
var seq=1
function changetext(){
rainbow=\"#\"+hex[r]+hex[g]+hex[b]
storetext.style.color=rainbow
}
function change(){
if (seq==6){
b--
if (b==0)
seq=1
}
if (seq==5){
r++
if (r==12)
seq=6
}
if (seq==4){
g--
if (g==0)
seq=5
}
if (seq==3){
b++
if (b==12)
seq=4
}
if (seq==2){
r--
if (r==0)
seq=3
}
if (seq==1){
g++
if (g==12)
seq=2
}
changetext()
}
function starteffect(){
if (document.all||document.getElementById)
flash=setInterval(\"change()\",speed)
}
starteffect()
</script>
</font></b>
";
}


// end online status

//strong privacy we will gide status

if($row["privacy"]=="strong"){
$status="";
}


if ($CURUSER){
   echo   '</td><td align="left" style="font-size: 12px; padding-left: 1px">&nbsp;&nbsp;'.nl2br(format_comment($row['text']));
   echo   '</td></tr>';
}
}
?>
<script type="text/javascript">
function SmileIT(smile){
    document.forms['shoutboxform'].elements['message'].value = document.forms['shoutboxform'].elements['message'].value+" "+smile+" ";  //this non standard attribute prevents firefox' autofill function to clash with this script
    document.forms['shoutboxform'].elements['message'].focus();
}
function PopMoreSmiles(form,name) {
         link='moresmiles.php?form='+form+'&text='+name
         newWin=window.open(link,'moresmile','height=500,width=350,resizable=yes,scrollbars=yes');
         if (window.focus) {newWin.focus()}
}
function Pophistory() {
         link='shoutbox.php?history=1&page=0'
         newWin=window.open(link,'moresmile','height=500,width=500,resizable=yes,scrollbars=yes');
         if (window.focus) {newWin.focus()}
}
function windowunder(link)
{
  window.opener.document.location=link;
  window.close();
}
</script>

</table></center><br />
<div valign=bottom style="margin-bottom:10px;"><center><a href='javascript:window.close();'><?php echo T_("CLOSE"); ?></a></center></div>
<?php echo $pagerbottom;?>
</div>
<br>
<script language=javascript>

function GiveMsgBoxFocus()
{
document.shoutboxform.message.focus();
}
</script>
<?php
}
?>