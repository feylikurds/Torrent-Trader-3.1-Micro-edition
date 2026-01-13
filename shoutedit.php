<?php
//
//	edit and delete functions added/modified by MicroMonkey
//	Torrentrader 2.8
//
require_once("backend/functions.php");
$action = $_GET["action"]; 
dbconn();

if ($action == "edit") {
	$msgid = $_GET["msgid"];
	if (!is_valid_id($msgid))
		die;
    $res = SQL_Query_exec("SELECT * FROM shoutbox WHERE msgid=".$msgid);
	if (mysqli_num_rows($res) != 1)
		show_error_msg("Error", "No message with ID $msgid.");
	$arr = mysqli_fetch_assoc($res);

    if ($CURUSER["id"] != $arr["userid"] && get_user_class() < 6)
		show_error_msg("Error", "Denied!", 1);
    $save = (int)$_GET["save"];
    if ($save) {
		$message = $_POST['message'];
			if ($message == "")
			
				show_error_msg("Error", "Message cannot be empty!",1); 
		$message = sqlesc($message);
		SQL_Query_exec("UPDATE shoutbox SET message=$message WHERE msgid=$msgid");
		header("Refresh: 0; url=shoutbox.php");

               // autolink("shoutbox.php", "Edit complete....");
	}
    print("<center><font size=3 color=red><b>Edit Message</b></font></center>\n");
    print("<form name=Form method=post action=shoutedit.php?action=edit&save=1&msgid=$msgid>\n");
    print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
    print("<tr><td>\n");
    print("</td><td style='padding: 0px'><textarea name=message cols=50 rows=4 >" . stripslashes(htmlspecialchars($arr["message"])) . "</textarea></td></tr>\n");
    print("<tr><td align=center colspan=2><input type=submit value='Submit Changes' class=btn></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
	print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
	print("<form name=Form method=post action=shoutbox.php\n");
    print("<tr><td align=center colspan=2><input type=submit value='Cancel' class=btn></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
}

if ($action == "delete") {
	$msgid = $_GET["msgid"];
	if (!is_valid_id($msgid))
		die;
    $res = SQL_Query_exec("SELECT * FROM shoutbox WHERE msgid=".$msgid);
	if (mysqli_num_rows($res) != 1)
		show_error_msg("Error", "No message with ID $msgid.");
	$arr = mysqli_fetch_assoc($res);
    if ($CURUSER["id"] != $arr["userid"] && get_user_class() < 6)
		show_error_msg("Error", "Denied!", 1);
    $save = (int)$_GET["save"];
    if ($save) {

		SQL_Query_exec("DELETE FROM shoutbox WHERE msgid=$msgid");
		write_log("<b>Shout<font color='orange'> ".$arr['message']."</font> Deleted by: ".$CURUSER['username']."</b>");
		header("Refresh: 0; url=shoutbox.php");

               // autolink("shoutbox.php", "Edit complete....");
	}
    print("<center><font size=3 color=red><b>Are you sure you want to delete this shoutbox message?</b></font></center>\n");
    print("<form name=Form method=post action=shoutedit.php?action=delete&save=1&msgid=$msgid>\n");
    print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
    print("<tr><td>\n");
	print("<tr><td>\n");
    print("</td><td style='padding: 0px'><textarea name=message cols=50 rows=4 >" . stripslashes(htmlspecialchars($arr["message"])) . "</textarea></td></tr>\n");
    print("<tr><td align=center colspan=2><input type=submit value='Delete Message' class=btn></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
    print("<center><table border=0 cellspacing=0 cellpadding=5>\n");
	print("<form name=Form method=post action=shoutbox.php\n");
    print("<tr><td align=center colspan=2><input type=submit value='Cancel' class=btn></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
}

?>
