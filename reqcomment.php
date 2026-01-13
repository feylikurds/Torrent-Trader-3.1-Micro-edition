<?php
include("backend/functions.php");
include("backend/bbcode.php");
$action = $_GET["action"];
dbconn();
loggedinonly();

if ($action == "add")
{
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$reqid = 0 + $_POST["tid"];
if (!is_valid_id($reqid))
	show_error_msg("".T_("ERROR")."", "".T_("WRONG_ID")." $reqid.",1);

	$res = SQL_Query_exec("SELECT request FROM requests WHERE id = $reqid");
	$arr = mysqli_fetch_array($res);
if (!$arr)
	show_error_msg("".T_("ERROR")."", "".T_("WRONG_REQUEST_WITH_ID")." $reqid.",1);

	$text = trim($_POST["msg"]);
if (!$text)
	show_error_msg("".T_("ERROR")."", "".T_("NO_BLANK_FIELDS")."",1);

SQL_Query_exec("INSERT INTO comments (user, req, added, text, ori_text) VALUES (" . $CURUSER["id"] . ", $reqid, '" . utc_to_tz($arr["editedat"]) . "', " . sqlesc($text) . "," . sqlesc($text) . ")");

$newid = mysqli_insert_id($GLOBALS["DBconnector"]);

SQL_Query_exec("UPDATE requests SET comments = comments + 1 WHERE id = $reqid");
header("Refresh: 0; url=reqall.php?Section=Request_Details&id=$reqid&viewcomm=$newid#comm$newid");
exit();
}

	$reqid = 0 + $_GET["tid"];
if (!is_valid_id($reqid))
	show_error_msg("".T_("ERROR")."", "".T_("WRONG_ID")." $reqid.",1);

	$res = SQL_Query_exec("SELECT request FROM requests WHERE id = $reqid");
	$arr = mysqli_fetch_array($res);
if (!$arr)
	show_error_msg("".T_("ERROR")."", "".T_("WRONG_ID")." $reqid.",1);


stdhead("".T_("ADD_A_REQUEST_COMMENT")." \"" . $arr["request"] . "\"");

begin_frame("".T_("ADD_A_REQUEST_COMMENT")."");


$char1 = 40; //cut name length
	$smallname = htmlspecialchars(CutName($arr["request"], $char1));
	$dispname = "<b>".$smallname."</b>";


		print("<center><h1>".T_("ADD_COMMENT_TO")." \"" . htmlspecialchars(CutName($arr["request"], $char1)) . "\"</h1></center>\n");
		print("<p><form name=\"Form\" method=\"post\" action=\"reqcomment.php?action=add\">\n");
		print("<input type=\"hidden\" name=\"tid\" value=\"$reqid\"/>\n");
if ($site_config["BBCODE_WITH_PREVIEW"]) {
	$dossier = $CURUSER['bbcode'];
		print("".textbbcode("Form","msg",$dossier)."<br />");
		print("<center><p><input type=\"submit\" class=btn value=\"".T_("ADD")."!\" /></p></center></form><br />\n");
}else{
		print("".textbbcode("Form","msg", htmlspecialchars($arr["msg"]))."");
		print("<center><p><input type=\"submit\" class=btn value=\"".T_("ADD")."!\" /></p></center></form><br />\n");
}

end_frame();
stdfoot();
die;
}

elseif ($action == "edit")
{
	$commentid = 0 + $_GET["cid"];
if (!is_valid_id($commentid))
	show_error_msg("".T_("ERROR")."", "".T_("WRONG_ID")." $commentid.",1);
	$res = SQL_Query_exec("SELECT c.*, o.request FROM comments AS c JOIN requests AS o ON c.req = o.id WHERE c.id=$commentid");
	$arr = mysqli_fetch_array($res);

if (!$arr)
	show_error_msg("".T_("ERROR")."", "".T_("WRONG_ID")." $commentid.",1);

if ($arr["user"] != $CURUSER["id"] && get_user_class($CURUSER) < 5)
	show_error_msg("".T_("ERROR")."", "".T_("ACCESS_DENIED")."",1);

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$text = $_POST["text"];
	$returnto = $_POST["returnto"];

if ($text == "")
	show_error_msg("".T_("ERROR")."", "".T_("NO_BLANK_FIELDS")."",1);

	$text = sqlesc($text);

	$editedat = sqlesc(get_date_time(utc_to_tz_time()));

	SQL_Query_exec("UPDATE comments SET text=$text, editedat=$editedat, editedby=$CURUSER[id]  WHERE id=$commentid");

if ($returnto)
header("Location: $returnto");
else
   show_error_msg("".T_("SUCCESS")."", "".T_("EDITED_SUCCSESSFULLY")."",1);

die;
}

stdhead();
begin_frame("Edit");

		print("<center><h1>Edit comment for \"" . htmlspecialchars($arr["request"]) . "\"</h1></center><p>\n");
		print("<form name=Form method=\"post\" action=\"?action=edit&amp;cid=$commentid\">\n");
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_SERVER["HTTP_REFERER"]) . "\" />\n");
		print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");

if ($site_config["BBCODE_WITH_PREVIEW"]) {
	$dossier = $CURUSER['bbcode'];
		print("".textbbcode("Form","text",$dossier, htmlspecialchars($arr["text"]))."<br />");
}else{
		print("".textbbcode("Form","text", htmlspecialchars($arr["text"]))."");
}
		print("<center><p><input type=\"submit\" class=btn value=\"".T_("EDIT")."!\" /></p></center></form>\n");

end_frame();
stdfoot();
die;
}

elseif ($action == "delete")
{
if (get_user_class($CURUSER) < 5)
	show_error_msg("".T_("ERROR")."", "".T_("ACCESS_DENIED").".",1);

	$commentid = 0 + $_GET["cid"];

if (!is_valid_id($commentid))
	show_error_msg("".T_("ERROR")."", "".T_("INVALID_ID")." $commentid.",1);

	$sure = $_GET["sure"];

if (!$sure) {
	$referer = $_SERVER["HTTP_REFERER"];
	show_error_msg("".T_("DELETE_COMMENT")."", "".T_("DELETE_THIS_COMMENT")." " . "<a href=?action=delete&cid=$commentid&sure=1" . ($referer ? "&returnto=" . urlencode($referer) : "") . ">".T_("HERE")."</a> ".T_("IF_YOU_ARE_SURE").".",1);
}

	$res = SQL_Query_exec("SELECT req FROM comments WHERE id=$commentid");
	$arr = mysqli_fetch_array($res);

if ($arr)
	$reqid = $arr["req"];

	SQL_Query_exec("DELETE FROM comments WHERE id=$commentid");
if (mysqli_affected_rows($GLOBALS["DBconnector"]) > 0)
	SQL_Query_exec("UPDATE requests SET comments = comments - 1 WHERE id = $reqid");

	$returnto = (int)$_GET["returnto"];

if ($returnto)
header("Location: $returnto");
else
header("Location: $site_config[SITEURL]/reqall.php?Section=Request_Details&id=$reqid");
die;
}

elseif ($action == "vieworiginal")
{
if (get_user_class() < 5)
	show_error_msg("".T_("ERROR")."", "".T_("ACCESS_DENIED")."",1);
	$commentid = 0 + $_GET["cid"];

if (!is_valid_id($commentid))
	show_error_msg("".T_("ERROR")."", "".T_("CP_INVALID_ID")." $commentid.",1);

	$res = SQL_Query_exec("SELECT c.*, t.request FROM comments AS c JOIN requests AS t ON c.req = t.id WHERE c.id=$commentid");
	$arr = mysqli_fetch_array($res);
if (!$arr)
	show_error_msg("".T_("ERROR")."", "".T_("CP_INVALID_ID")." $commentid.",1);

stdhead("Original");
begin_frame("".T_("VIEW_ORIGINAL_POST")."");
		print("<center><p><h1>".T_("ORIGINAL_CONTENT")." #$commentid</h1></p></center>\n");
		print("<table align=center width=500 border=1 cellspacing=0 cellpadding=5>");
		print("<tr><td class=comment>\n");
		echo htmlspecialchars($arr["ori_text"]);
		print("</td></tr></table>\n");

	$returnto = $_SERVER["HTTP_REFERER"];

if ($returnto)
		print("</br ><center><p><font size=small>(<a href=$returnto>".T_("BACK")."</a>)</font></p></center></br >\n");
end_frame();
stdfoot();
die;
}
else
	show_error_msg("".T_("ERROR")."", "".T_("UNKNOWN_ACTION")." $action",1);
die;
?>