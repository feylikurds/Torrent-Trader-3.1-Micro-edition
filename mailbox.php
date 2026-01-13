<?php
//
//  	TorrentTrader v2.x
//      $LastChangedDate: 2011-12-18 13:54:53 +0000 (Sun, 18 Dec 2011) $
//      $LastChangedBy: dj-howarth1 $
//		Modified by UFFENO1
//
//      http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
require_once("mailbox-functions.php");
dbconn(false);
loggedinonly();

	$readme = add_get('read').'=';
	$unread = false;

if (isset($_REQUEST['compose'])); // This blocks everything until done...

if (isset($_GET['inbox'])) {
	$pagename = T_("INBOX");
	$tablefmt = "&nbsp;,Unread,Sender,Subject,Date";
	$where = "`receiver` = $CURUSER[id] AND `location` IN ('in','both')";
	$type = "Mail";
}
	elseif (isset($_GET['outbox']))
{
	$pagename = T_("OUTBOX");
	$tablefmt = "&nbsp;,Sent_to,Subject,Date";
	$where = "`sender` = $CURUSER[id] AND `location` IN ('out','both')";
	$type = "Mail";
}
	elseif (isset($_GET['draft']))
{
	$pagename = T_("DRAFT");
	$tablefmt = "&nbsp;,Sent_to,Subject,Date";
	$where = "`sender` = $CURUSER[id] AND `location` = 'draft'";
	$type = "Mail";
}
	elseif (isset($_GET['templates']))
{
	$pagename = T_("TEMPLATES");
	$tablefmt = "&nbsp;,Subject,Date";
	$where = "`sender` = $CURUSER[id] AND `location` = 'template'";
	$type = "Mail";
}
	else
{
	$pagename = T_("OVERVIEW");
	$type = "Overview";
}

//****** Send a message, or save after editing ******
if (isset($_POST['send']) || isset($_POST['draft']) || isset($_POST['template'])) {
if (!isset($_POST['template']) && !isset($_POST['change']) && (!isset($_POST['userid']) || !is_valid_id($_POST['userid']))) $error = "Unknown recipient";
	else
{
	$sendto = (@$_POST['template'] ? $CURUSER['id'] : @$_REQUEST['userid']);

   if (isset($_POST['usetemplate']) && is_valid_id($_POST['usetemplate'])) {
$res = SQL_Query_exec("SELECT * FROM messages WHERE `id` = $_POST[usetemplate] AND `location` = 'template' LIMIT 1");
	$arr = mysqli_fetch_array($res);
	$subject = $arr['subject'].(@$_POST['oldsubject'] ? " (was ".$_POST['oldsubject'].")" : "");
	$msg = sqlesc($arr['msg']);
} else {
	$subject = @$_POST['subject'];
	$msg = sqlesc(@$_POST['msg']);
}
   
if ($msg){
	$subject = sqlesc($subject);
if ((isset($_POST['draft']) || isset($_POST['template'])) && isset($_POST['msgid'])) SQL_Query_exec("UPDATE messages SET `subject` = $subject, `msg` = $msg WHERE `id` = $_POST[msgid] AND `sender` = $CURUSER[id]") or die("arghh");
	else
{
	$to = (@$_POST['draft'] ? 'draft' : (@$_POST['template'] ? 'template' : (@$_POST['save'] ? 'both' : 'in')));
	$status = (@$_POST['send'] ? 'yes' : 'no');
     
SQL_Query_exec("INSERT INTO `messages` (`sender`, `receiver`, `added`, `subject`, `msg`, `unread`, `location`) VALUES ('$CURUSER[id]', '$sendto', '".get_date_time()."', $subject, $msg, '$status', '$to')") or die("Aargh!");

   // email notif
	$res = SQL_Query_exec("SELECT id, acceptpms, notifs, email FROM users WHERE id='$sendto'");
	$user = mysqli_fetch_assoc($res);

if (strpos($user['notifs'], '[pm]') !== false) {
	$cusername = $CURUSER["username"];
	$body = "You have received a PM from ".$cusername."\n\nYou can use the URL below to view the message (you may have to login).\n\n    ".$site_config['SITEURL']."/mailbox.php\n\n".$site_config['SITENAME']."";
	sendmail($user["email"], "You have received a PM from $cusername", $body, "From: $site_config[SITEEMAIL]", "-f$site_config[SITEEMAIL]");
}
	//end email notif

if (isset($_POST['msgid'])) SQL_Query_exec("DELETE FROM messages WHERE `location` = 'draft' AND `sender` = $CURUSER[id] AND `id` = $_POST[msgid]") or die("arghh");
}

if (isset($_POST['send'])) $info = "Message sent successfully".(@$_POST['save'] ? ", a copy has been saved in your Outbox" : "");
	else $info = "Message saved successfully";
}
	else $error = "Unable to send message";
}
}

//****** Delete a message ******
if (isset($_POST['remove']) && (isset($_POST['msgs']) || is_array($_POST['remove']))) {
if (is_array($_POST['remove'])) $tmp[] = key($_POST['remove']);
	else foreach($_POST['msgs'] as $key => $value) if (is_valid_id($key)) $tmp[] = $key;
	$msgs = implode(', ', $tmp);

if ($msgs) {

if (isset($_GET['inbox'])) {
SQL_Query_exec("DELETE FROM messages WHERE `location` = 'in' AND `receiver` = $CURUSER[id] AND `id` IN ($msgs)");
SQL_Query_exec("UPDATE messages SET `location` = 'out' WHERE `location` = 'both' AND `receiver` = $CURUSER[id] AND `id` IN ($msgs)");
} else {
if (isset($_GET['outbox'])) SQL_Query_exec("UPDATE messages SET `location` = 'in' WHERE `location` = 'both' AND `sender` = $CURUSER[id] AND `id` IN ($msgs)");
SQL_Query_exec("DELETE FROM messages WHERE `location` IN ('out', 'draft', 'template') AND `sender` = $CURUSER[id] AND `id` IN ($msgs)");
}
	$info = count($tmp)." ".P_("message", count($tmp))." deleted";
}
	else $error = "No messages to delete";
}

//****** Mark a message as read - only if you're the recipient ******
if (isset($_POST['mark']) && (isset($_POST['msgs']) || is_array($_POST['mark'])))
{
if (is_array($_POST['mark'])) $tmp[] = key($_POST['mark']);
	else foreach($_POST['msgs'] as $key => $value) if (is_valid_id($key)) $tmp[] = $key;
	$msgs = implode(', ', $tmp);

if ($msgs) {
SQL_Query_exec("UPDATE messages SET `unread` = 'no' WHERE `id` IN ($msgs) AND `receiver` = $CURUSER[id]");
	$info = count($tmp)." ".P_("message",  count($tmp))." marked as read";
}
	else $error = "No messages marked as read";
}


stdhead($pagename, false);

function navmenu(){
?>
	<br /><table class="f-border" align='center' cellpadding='0' cellspacing='3' width='100%'><tr><td>
		<table class="f-title" cellpadding='0' cellspacing='3' width='100%'>
		<tr>
		<td width='100%' height="32" align='center'>
        <?php print("<a href='account-delete.php'><b>".T_("DELETE_ACCOUNT")."</b></a>");?>
		&nbsp;|&nbsp;
		<?php print("<a href='account.php'><b>".T_("YOUR_PROFILE")."</b></a>");?>
		&nbsp;|&nbsp;
		<?php print("<a href='account.php?action=edit_settings&amp;do=edit'><b>".T_("YOUR_SETTINGS")."</b></a>");?>
		&nbsp;|&nbsp;
		<?php print("<a href='account.php?action=changepw'><b>".T_("CHANGE_PASS")."</b></a>");?>
		&nbsp;|&nbsp;
		<?php print("<a href='account.php?action=mytorrents'><b>".T_("YOUR_TORRENTS")."</b></a>");?>
		&nbsp;|&nbsp;
		<?php print("<a href='mailbox.php'><b>".T_("YOUR_MESSAGES")."</b></a>");?>
        &nbsp;|&nbsp;
		<?php print("<a href='snatched.php'><b>".T_("YOUR_SNATCHLIST")."</b></a>");?>
		</td></tr>
		</table>
	</td></tr></table>
	  </div>
    <!--<br />-->
	<?php
}//end func

#################################################-CSS-MAILBOX-###################################################
#                                                                                                               #
# If you have more the one theme, you can copy the CSS-Code into each of theme.css files and do the adjustment  #
#																												#
#################################################################################################################
?>
 <style type="text/css">  
<!--
 #mpbox {
	
	align: center;
	//#width: 100%;
	border:1px solid #000000;
	border-radius:8px;
	box-shadow:0 0 10px #000000;
    padding-top:10px;
	padding: 10px;
    //background: rgb(207,207,207);
    -webkit-box-shadow: 0 5px 8px 0 rgba(100,100,100, 0.8);
    -moz-box-shadow: 0 5px 8px 0 rgba(100,100,100, 0.8);
	-o-box-shadow: 0 5px 8px 0 rgba(100,100,100, 0.8);
	box-shadow: 0 5px 8px 0 rgba(100,100,100, 0.8);
}
	
#tablebox {
	
	align: center;
	width: 80%;
	border:1px solid #000000;
	border-radius:8px;
	//box-shadow:0 0 50px #000000;
	padding-top:10px;
	padding: 10px;
	-webkit-box-shadow: 0 5px 15px 0 rgba(16,16,16, 0.8);
	-moz-box-shadow: 0 5px 15px 0 rgba(16,16,16, 0.8);
	-o-box-shadow: 0 5px 15px 0 rgba(16,16,16, 0.8);
	box-shadow: 0 5px 15px 0 rgba(16,16,16, 0.8);
}	

.table_mb {
        
	//width: 100%;
	/*font-size: 12px;*/
	/*color: #FFF;*/
	//background-image: url(images/alt_2.png);
	border-collapse: collapse;
	-webkit-box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	-moz-box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	-o-box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	/*border-collapse: separate; color: 0 1px 1px 0 rgba(0,0,0, 0.8);*/
	margin-bottom: 2px;
}	

/*Background buttons*/
div.menu5 {

	width:80%;margin:0 auto;/*Uncomment this line to make the menu center-alignd.*/
	text-align:center;
	//background:#332E28;
	border-radius:8px;
	-webkit-box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	-moz-box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	-o-box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	box-shadow: 0 8px 10px 0 rgba(100,100,100, 0.8);
	border:1px solid black;
	font-size:0;
	padding:10px;
}


/*Buttons*/
div.menu5 a {

	display: inline-block;
	padding: 0 20px;
	//background:#3A332C;
	//background: url(themes/<?php echo $THEME; ?>/images/frame-top.gif);
	border:1px solid #5E544A;
	border-radius:8px;
	-webkit-box-shadow: 0 8px 5px 0 rgba(100,100,100, 0.6);
	-moz-box-shadow: 0 8px 5px 0 rgba(100,100,100, 0.6);
	-o-box-shadow: 0 8px 5px 0 rgba(100,100,100, 0.6);
	box-shadow: 0 8px 5px 0 rgba(100,100,100, 0.6);
	color:#21979a;
	text-decoration:none;
	font: bold 12px Arial;
	line-height: 27px;
	margin-right:5px;
}

/*Buttons Mouseover*/
div.menu5 a:hover, div.menu5 a.current {
	
//	background:#484037;
	background: url(themes/<?php echo $THEME; ?>/images/f-title.gif);
	color:#dd7717;
}
--->   
 </style>  
<?php
#################################################-CSS-MAILBOX-END-###############################################

if (isset($_REQUEST['compose'])) {

begin_frame(T_("COMPOSE"));
navmenu();
	$userid = @$_REQUEST['id'];
	$subject = ''; $msg = ''; $to = ''; $hidden = ''; $output = ''; $reply = false;
    $sreplay = T_("REPLY");//bugfix
if (is_array($_REQUEST['compose'])) { // In reply or followup to another msg
	$msgid = key($_REQUEST['compose']);

if (is_valid_id($msgid)) {
$res = SQL_Query_exec("SELECT * FROM `messages` WHERE `id` = $msgid AND '$CURUSER[id]' IN (`sender`,`receiver`) LIMIT 1");

if ($arr = mysqli_fetch_assoc($res)){
	$subject = htmlspecialchars($arr['subject']);
	$msg .= htmlspecialchars($arr['msg']);

//if (current($_REQUEST['compose']) == 'Reply') //bug
if (current($_REQUEST['compose']) == $sreplay) { //bugfix 
if ($arr['unread'] == 'yes' && $arr['receiver'] == $CURUSER['id']) SQL_Query_exec("UPDATE messages SET `unread` = 'no' WHERE `id` = $arr[id]");
	$reply = true;
	$userid = $arr['sender'];
if (substr($arr['subject'],0,4) != 'Re: ') $subject = "Re: $subject";
}
   else $userid = $arr['receiver'];
   $hidden .= "<input type=\"hidden\" name=\"msgid\" value=\"$msgid\" />";
}
}
}
if (isset($_GET['templates'])) $to = 'who cares';
elseif (is_valid_id($userid))
{
    $where = null;
if ($CURUSER["view_users"] == "no" && $userid != $CURUSER["id"])
	$where = "AND acceptpms = 'yes'";

    # Allow users to PM themself's, Privacy is determined on acceptpms - (From All or Staff Only).
    $res = SQL_Query_exec("SELECT username FROM users WHERE id = $userid AND status = 'confirmed' AND enabled = 'yes' $where");
    $row = mysqli_fetch_assoc($res);

    if ( !$row ) {
	print("You either do not have permission to pm this user, or they don't exist.");

end_frame();
stdfoot();
die;
}

    $to = $row["username"];
	$hidden .= "<input type=\"hidden\" name=\"userid\" value=\"$userid\" />";
if ($to == $CURUSER["username"])
	$to = "".T_("YOURSELF")."";
    $to = "&nbsp;&nbsp;<b>$to</b>";
}
	else
{
    $where = null;
if ($CURUSER["view_users"] == "no")
	$where = "AND acceptpms = 'yes'";

    # Don't display yourself, Privacy is determined on acceptpms - (From All or Staff Only).
    $res = SQL_Query_exec("SELECT id, username FROM users WHERE id != $CURUSER[id] AND enabled = 'yes' AND status = 'confirmed' $where ORDER BY username");

if (mysqli_num_rows($res)) {
	$to = "<select name=\"userid\">\n";
	while ($arr = mysqli_fetch_assoc($res)) $to .= "<option value=\"$arr[id]\">$arr[username]</option>\n";
	$to .= "</select>\n";
}

}
if (isset($_GET['id']) && !$to) print T_("INVALID_USER_ID");
elseif (!isset($_GET['id']) && !$to) print T_("NO_FRIENDS");
else
{
     /******** compose frame ********/

begin_form(rem_get('compose'),'name="compose"');
?>
<br /><br />
<div class="menu5">
	<a href="mailbox.php"><?php echo T_("OVERVIEW"); ?></a>
	<a href="mailbox.php?inbox"><?php echo T_("INBOX"); ?></a>
	<a href="mailbox.php?outbox"><?php echo T_("OUTBOX"); ?></a>
	<a href="mailbox.php?draft"><?php echo T_("DRAFT"); ?></a>
	<a href="mailbox.php?templates"><?php echo T_("TEMPLATES"); ?></a>
	<a href="mailbox.php?compose"><?php echo T_("COMPOSE"); ?></a>
</div>	
<?php
	/*-No bottons just links
	print ("<br /><br /><p align='center'><br />
	<a href=\"mailbox.php\">".T_("OVERVIEW")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?inbox\">".T_("INBOX")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?outbox\">".T_("OUTBOX")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?draft\">".T_("DRAFT")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?templates\">".T_("TEMPLATES")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?compose\">".T_("COMPOSE")."</a></p>");
	*/
	//echo ("<br /><br /><hr />");
	echo "<br /><br />";

if ($subject) $hidden .= "<input type=\"hidden\" name=\"oldsubject\" value=\"$subject\" />";
if ($hidden) print($hidden);
	
##########################################################################-Compose START-###########################################################################	
	echo "<center><div id='tablebox'><br /><table class='table_mb' width='591px' border='1' align='center' cellpadding='0' cellspacing='0'></center>";
if (!isset($_GET['templates'])){
	echo "<tr><td align='right'><b>" . T_("TO") . "&nbsp;:&nbsp;</b></td><td> $to</td></tr>";
$res = SQL_Query_exec("SELECT * FROM `messages` WHERE `sender` = $CURUSER[id] AND `location` = 'template' ORDER BY `subject`");

 if (mysqli_num_rows($res)) {
	$tmp = "<select name=\"usetemplate\" onchange=\"toggleTemplate(this);\">\n<option name=\"0\">---</option>\n";
	while ($arr = mysqli_fetch_assoc($res)) $tmp .= "<option value=\"$arr[id]\">$arr[subject]</option>\n";
	$tmp .= "</select><br />\n";
	echo "<tr><td align='right'><b>".T_("TEMPLATES")."&nbsp;:&nbsp;</b></td><td>$tmp</td></tr>";
}
}
	echo "<tr><td align='right'><b>".T_("SUBJECT")."&nbsp;:&nbsp;</b></td><td><input name=\"subject\" type=\"text\" size=\"40\" value=\"$subject\"></td></tr>";
	
require_once("backend/bbcode.php");
	echo "</table>";

#####-comment this row out if you have mode from (thib - bbcode with preview everywhere, users may change color of icon)-####
	
	print textbbcode("compose","msg","$msg");
	
#############################################################################################################################
	
#####-Uncomment this rows if you have mode from (thib - bbcode with preview everywhere, users may change color of icon)-#####

	//$dossier_pm = $CURUSER['bbcode']; 
	//print ("<center>");
	//print ("".textbbcode("compose","msg",$dossier_pm,$msg)."");
	//print ("</center>");

#############################################################################################################################
	
	echo "<table class='table_mb' width='591px' border='1' align='center' cellpadding='4' cellspacing='0'>";
if (!isset($_GET['templates'])) $output .= "<input type=\"submit\" name=\"send\" value=\"".T_("SEND")."\" />&nbsp;<label><sub><input type=\"checkbox\" name=\"save\" checked='checked' /></sub>".T_("SAVE_COPY")."</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" name=\"draft\" value=\"".T_("SAVE_DRAFT")."\" />&nbsp;";
	echo "<tr><td align='left'>$output<input type=\"submit\" name=\"template\" value=\"".T_("SAVE_TEMPLATE")."\" /></td></tr>";
	echo "</table><br /></div></center><br /><br />";

################################################################-Compose END-################################################

end_form();
end_frame();
stdfoot();
die;
}
end_frame();
}

begin_frame($pagename);
navmenu();
	
?>
<br /><br />
<div class="menu5">
	<a href="mailbox.php"><?php echo T_("OVERVIEW"); ?></a>
	<a href="mailbox.php?inbox"><?php echo T_("INBOX"); ?></a>
	<a href="mailbox.php?outbox"><?php echo T_("OUTBOX"); ?></a>
	<a href="mailbox.php?draft"><?php echo T_("DRAFT"); ?></a>
	<a href="mailbox.php?templates"><?php echo T_("TEMPLATES"); ?></a>
	<a href="mailbox.php?compose"><?php echo T_("COMPOSE"); ?></a>
</div>	
<?php
	
	/*-No bottons just links
	print ("<br /><br /><p align='center'><br />
	<a href=\"mailbox.php\">".T_("OVERVIEW")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?inbox\">".T_("INBOX")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?outbox\">".T_("OUTBOX")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?draft\">".T_("DRAFT")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?templates\">".T_("TEMPLATES")."</a> &nbsp; | &nbsp;
	<a href=\"mailbox.php?compose\">".T_("COMPOSE")."</a></p>");
	*/
	//echo ("<br /><br /><hr />");
	echo "<br />";


if ($type == "Overview")
{

	$res = SQL_Query_exec("SELECT COUNT(*), COUNT(`unread` = 'yes') FROM messages WHERE `receiver` = $CURUSER[id] AND `location` IN ('in','both')");
	$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND `location` IN ('in','both')");
	$inbox = mysqli_result($res, 0);
	$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `receiver` = " . $CURUSER["id"] . " AND `location` IN ('in','both') AND `unread` = 'yes'");
	$unread = mysqli_result($res, 0);
	$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `sender` = " . $CURUSER["id"] . " AND `location` IN ('out','both')");
	$outbox = mysqli_result($res, 0);
	$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `sender` = " . $CURUSER["id"] . " AND `location` = 'draft'");
	$draft = mysqli_result($res, 0);
	$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE `sender` = " . $CURUSER["id"] . " AND `location` = 'template'");
	$template = mysqli_result($res, 0);

	echo"<br />";

	echo("<center><div id='tablebox'><table class='table_mb' align='center' border='1' width='40%' cellspacing='5' cellpadding='5'><br /></center>");

	echo('<tr><td class="table_head" align="center" colspan="2"><b><i>'.T_("OVERVIEW_INFO").'</i></b></td></tr>');

	echo('<tr><td align="right" width="25%"><!--<a href="mailbox.php?inbox">-->'.T_("INBOX").' :</a></td><td align="center" "width="25%" >'. " [<font color=green> $inbox </font>] ".P_("", $inbox)." (<font color=red>$unread ".T_("UNREAD")."</font>)</td></tr>");

	echo('<tr><td align="right" width="25%"><!--<a href="mailbox.php?outbox">-->'.T_("OUTBOX").' :</a></td><td align="center" width="25%">'. " [ $outbox ] ".P_("", $outbox)."</td></tr>");

	echo('<tr><td align="right" width="25%"><!--<a href="mailbox.php?draft">-->'.T_("DRAFT").' :</a></td><td align="center" width="25%">'. " [ $draft ] ".P_("", $draft)."</td></tr>");

	echo('<tr><td align="right" width="25%"><!--<a href="mailbox.php?templates">-->'.T_("TEMPLATES").' :</a></td><td align="center" width="25%">'. " [ $template ] ".P_("", $template)."</td></tr>");

	echo('</table><br /></div>');

	echo"<br /><br />";
}
elseif ($type == "Mail")
{
	

begin_form();
	echo("<br /><center><div id='tablebox'>");
	$order = order("added,unread,sender,sendto,subject", "added", true);
$res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE $where");
$count = mysqli_result($res, 0);
	list($pagertop, $pagerbottom, $limit) = pager2(10, $count);
	print($pagertop);
	
	echo("<table class='table_mb' align='center' border='1' width='97%' cellspacing='5' cellpadding='5'><br /></center>\n");
	$table['&nbsp;']  = th_center("<input type=\"checkbox\" onclick=\"toggleChecked(this.checked);this.form.remove.disabled=true;\" />", 1);
	$table['Unread']  = th_center("".T_("READ")."",'unread');
	$table['Sender']  = th_left("".T_("SENDER")."",'sender');
	$table['Sent_to'] = th_left("".T_("SENT_TO")."",'receiver');
	$table['Subject'] = th_left("".T_("SUBJECT")."",'subject');
	$table['Date']    = th_center("".T_("DATE")."",'added');
	table($table, $tablefmt);

$res = SQL_Query_exec("SELECT * FROM messages WHERE $where $order $limit");
while ($arr = mysqli_fetch_assoc($res)) {
	unset($table);
	$userid = 0;
	$format = '';
	$reading = false;

if ($arr["sender"] == $CURUSER['id']) $sender = "".T_("YOURSELF")."";
   elseif (is_valid_id($arr["sender"])) {
$res2 = SQL_Query_exec("SELECT username FROM users WHERE `id` = $arr[sender]");
$arr2 = mysqli_fetch_assoc($res2);
	$sender = "<a href=\"account-details.php?id=$arr[sender]\">".($arr2["username"] ? $arr2["username"] : "[Deleted]")."</a>";
}
	else $sender = "".T_("SYSTEM")."";


if ($arr["receiver"] == $CURUSER['id']) $sentto = "".T_("YOURSELF")."";
	elseif (is_valid_id($arr["receiver"])) {
	$res2 = SQL_Query_exec("SELECT username FROM users WHERE `id` = $arr[receiver]");
	$arr2 = mysqli_fetch_assoc($res2);
	$sentto = "<a href=\"account-details.php?id=$arr[receiver]\">".($arr2["username"] ? $arr2["username"] : "[Deleted]")."</a>";
}
	else $sentto = "".T_("SYSTEM")."";
	$subject = ($arr['subject'] ? htmlspecialchars($arr['subject']) : "".T_("NO_SUBJECT")."");

if (@$_GET['read'] == $arr['id']) {
	$reading = true;
     
if (isset($_GET['inbox']) && $arr["unread"] == "yes") SQL_Query_exec("UPDATE messages SET `unread` = 'no' WHERE `id` = $arr[id] AND `receiver` = $CURUSER[id]");
}

if ($arr["unread"] == "yes") {
	$format = "font-weight:bold;";
	$unread = true;
	$unread = "<font color=red>".T_("NO")."</font>";
}
    else
	$unread = "<font color=green>".T_("YES")."</font>";
   
   
   
	$table['&nbsp;']  = th_center("<input type=\"checkbox\" name=\"msgs[$arr[id]]\" ".($reading ? "checked='checked'" : "")." onclick=\"this.form.remove.disabled=true;\" />", 1);
	$table['Unread']  = th_center("$unread");
	$table['Sender']  = th_left("$sender", 1, $format);
	$table['Sent_to'] = th_left("$sentto", 1, $format);
	$table['Subject'] = th_left("<a href=\"javascript:read($arr[id]);\"><img src=\"".$site_config["SITEURL"]."/images/plus.gif\" id=\"img_$arr[id]\" class=\"read\" border=\"0\" alt='' /></a>&nbsp;<a href=\"javascript:read($arr[id]);\">&nbsp;$subject</a>", 1, $format);
	$table['Date']    = th_center(utc_to_tz($arr['added']), 1, $format);
	table($table, $tablefmt);
 
   
	$display = "<div id='mpbox'>".format_comment($arr['msg'])."<br /><br />";
	$display .= "</div><br />";

if (isset($_GET['inbox']) && is_valid_id($arr["sender"]))   $display .= "<input type=\"submit\" name=\"compose[$arr[id]]\" value=\"".T_("REPLY")."\" />&nbsp;\n";
elseif (isset($_GET['draft']) || isset($_GET['templates'])) $display .= "<input type=\"submit\" name=\"compose[$arr[id]]\" value=\"".T_("EDIT_PM")."\" />&nbsp;\n";
if (isset($_GET['inbox']) && $arr['unread'] == 'yes') $display .= "<input type=\"submit\" name=\"mark[$arr[id]]\" value=\"".T_("MARK_AS_READ")."\" />&nbsp;\n";
	$display .= "<input type=\"submit\" name=\"remove[$arr[id]]\" value=\"".T_("DELETE_PM")."\" />&nbsp;\n";
	table(td_left($display, 1, "padding:6px 6px 6px 6px"), $tablefmt, "id=\"msg_$arr[id]\" style=\"display:none;\"");
}
	print("</table><br />");
	print($pagerbottom);
	print("</div><br /><br />");

	print("<center><div id='tablebox'><table align='center' border='0' width='98%' cellspacing='1' cellpadding='1'></center>\n");
	$buttons = "<input type=\"button\" value=\"".T_("DELETE_SELECTED")."\" onclick=\"this.form.remove.disabled=!this.form.remove.disabled;\" />";
	$buttons .= "<input type=\"submit\" name=\"remove\" value=\"".T_("DEL_CONFIRM")."\" disabled=\"disabled\" />";
	if (isset($_GET['inbox']) && $unread) $buttons .= "&nbsp;<input type=\"button\" value=\"".T_("MARK_SELECTED_AS_READ")."\" onclick=\"this.form.mark.disabled=!this.form.mark.disabled;\" /><input type=\"submit\" name=\"mark\" value=\"".T_("DEL_CONFIRM")."\" disabled=\"disabled\" />";
	if (isset($_GET['templates'])) $buttons .= "&nbsp;<input type=\"submit\" name=\"compose\" value=\"".T_("CREATE_NEW_TEMPLATE")."\" />";
	table(td_left($buttons, 1, "border:0"), $tablefmt);
	print("</table></div>");

	print("<br />");
   
	print("<br />");


}
end_frame();

stdfoot();
?>