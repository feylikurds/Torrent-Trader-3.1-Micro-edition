<?php
//-----------------------------------------------//
// Inactive Users Management                                                             //
//-----------------------------------------------//
// Modified for TorrentTrader v2.xx by BigMax //
//-----------------------------------------------//

require_once("backend/functions.php");
dbconn(true);
loggedinonly();

if ($CURUSER["class"] < 6)
	show_error_msg("Acces Denied", "I smelled a rat here!", 1);

// Config
$sitename = $site_config['SITENAME']; // Sitename
$siteurl = $site_config['SITEURL']; // Default site url
$replyto = $site_config['SITEEMAIL']; // The Reply-to email
$record_mail = true; // Set this true or false . If you set this true every time whene you send a mail the time , userid , and the number of mail sent will be recorded
$days = 30; // Number of days of inactivite
// End config

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$action = $_POST["action"];
	$cday = 0 + $_POST["cday"];

	if (!is_numeric($cday))
		show_error_msg(T_("ERROR"), "Smell rat !", 1);

	if (empty($_POST["userid"]) && (($action == "deluser") || ($action == "mail")))
		show_error_msg(T_("ERROR"), "For this to work you must select at least a user !", 1);

	if ($action == "deluser" && (!empty($_POST["userid"]))) {
		SQL_Query_exec("DELETE FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ");
		show_error_msg("Successfully", "You have successfully deleted the selected accounts! <a href=" . $BASEURL . "/inactiveusers.php>Go back</a>", 1);
	}

	if ($action == "cday" && ($cday > $days))
		$days = $cday;

	if ($action == "disable" && (!empty($_POST["userid"]))) {
		$res = SQL_Query_exec("SELECT id, modcomment FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ORDER BY id DESC ");
		while ($arr = mysqli_fetch_array($res)) {
			$id = 0 + $arr["id"];
			$cname = $CURUSER["username"];
			$modcomment = $arr["modcomment"];
			$modcomment = gmdate("Y-m-d") . " - Disabled for inactivity by $cname.n" . $modcomment;

			SQL_Query_exec("UPDATE users SET modcomment=" . sqlesc($modcomment) . ", enabled='no' WHERE id=$id ");
		}
		show_error_msg("Successfully", "You have successfully disabled the selected accounts! <a href=" . $BASEURL . "/inactiveusers.php>Go back</a>", 1);
	}

	if ($action == "mail" && (!empty($_POST["userid"]))) {
		$res = SQL_Query_exec("SELECT id, email , username, added, last_access FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ORDER BY last_access DESC ");
		$count = mysqli_num_rows($res);
		while ($arr = mysqli_fetch_array($res)) {
			$id = $arr["id"];
			$username = htmlspecialchars($arr["username"]);
			$email = htmlspecialchars($arr["email"]);
			$added = $arr["added"];
			$last_access = $arr["last_access"];

			$subject = "Warning for inactive account on $sitename";
			$message = "
Your account on $sitename was marked as inactive and will be deleted soon.
If you want to remain member at $sitename you just have to log in and download something, or at least stop by and say Hi, we miss you. lol.

Your username it's:      $username
Account created on:      $added
Last visit on site:                      $last_access

You can login here:      $siteurl/account-login.php
Recovery password:       $siteurl/account-recover.php
";
			$headers = 'From: no-reply@' . $sitename . "rn" . 'Reply-To:' . $replyto . "rn" . 'X-Mailer: PHP/' . phpversion();

			$mail = @mail($email, $subject, $message, $headers);
		}

		if ($record_mail) {
			$date = time();
			$userid = 0 + $CURUSER["id"];
			if ($count > 0 && $mail)
				SQL_Query_exec("update avps set value_i='$date', value_u='$count', value_s='$userid' WHERE arg='inactivemail' ");
		}

		if ($mail)
			show_error_msg("Success", "Messages sent.", 1);
		else
			show_error_msg(T_("ERROR"), "Try again.", 1);
	}
}
stdhead(T_("USURSINACTIVE"));
begin_frame(T_("USURSINACTIVE"));

$dt = sqlesc(get_date_time(gmtime() - ($days * 86400)));

$res = SQL_Query_exec("SELECT id,username,class,email,uploaded,downloaded,last_access,ip,added FROM users WHERE last_access<$dt AND status='confirmed' AND enabled='yes' ORDER BY last_access DESC ");
$count = mysqli_num_rows($res);
if ($count > 0) {
	?>
<script type="text/javascript" LANGUAGE="JavaScript">

<!-- Begin
var checkflag = "false";
function check(field) {
if (checkflag == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = "true";
return "Uncheck All"; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = "false";
return "Check All"; }
}
// End -->
</script>
	<?php
	print("<form action='inactiveusers.php' method='post'>");
	print("<br><table class=table_table align=center border=1 cellspacing=0 cellpadding=5><tr>\n");
	print("<td class=table_head>" . T_("NUMBER0FDAYS") . "</td><td class=table_head><input type='text' name='cday' size='10' value='" . ($cday > $days ? $cday : $days) . "' maxlength='3' /></td>");
	print("<td class='table_head'><input type='submit' value='Change' /><input type='hidden' name='action' value='cday' />");
	print("</td></tr></table></form><br/>");

	print("<h2 align=center>" . $count . " accounts inactive for longer than " . $days . " days.</h2>");
	print("<form action='inactiveusers.php' method='post'>");
	print("<table class=table_table align=center width=800 border=1 cellspacing=0 cellpadding=5><tr>\n");
	print("<td class=table_head>" . T_("USERNAME") . "</td>");
	print("<td class=table_head>" . T_("CLASS") . "</td>");
	print("<td class=table_head>" . T_("IP") . "</td>");
	print("<td class=table_head>" . T_("RATIO") . "</td>");
	print("<td class=table_head>" . T_("JOINDATE") . "</td>");
	print("<td class=table_head>" . T_("LAST_VISIT") . "</td>");
	print("<td class=table_head align='center'>x</td>");

	while ($arr = mysqli_fetch_assoc($res)) {
		$ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"], 3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));
		$ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
		$downloaded = mksize($arr["downloaded"]);
		$uploaded = mksize($arr["uploaded"]);
		$last_seen = (($arr["last_access"] == "0000-00-00 00:00:00") ? "never" : "" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["last_access"])) . "&nbsp;ago");
		$class = get_user_class_name($arr["class"]);
		$joindate = substr($arr['added'], 0, strpos($arr['added'], " "));
		print("<tr>");

		// Make a choise here:
		print("<td><a href='account-details.php?id=" . $arr["id"] . "'>" . htmlspecialchars($arr["username"]) . "</a></td>"); //=== Use this line if you did not have the function class_user ===//

		//print("<td><a href="account-details.php?id=".$arr["id"].""><b>".class_user($arr["username"])."</b></a></td>");         //=== Use this line if you have the function class_user ===//

		print("<td>" . $class . "</td>");
		print("<td>" . ($arr["ip"] == "" ? "----" : $arr["ip"]) . "</td>");
		print("<td><b>" . $ratio . "</b><font class='small'> | Dl:<font color=red><b>" . $downloaded . "</b></font> | Up:<font color=lime><b>" . $uploaded . "</b></font></font></td>");
		print("<td>" . $joindate . "</td>");
		print("<td>" . $last_seen . "</td>");

		print("<td align='center' bgcolor='#FF0000'><input type='checkbox' name='userid[]' value='" . $arr["id"] . "' /></td>");
		print("</tr>");
	}
	print("<tr><td colspan=7 class='table_head' align='center'>
	<select name='action'>
	<option value=mail>" . T_("SENDMAIL") . "</option>
	<option value='deluser'>" . T_("DELETEUSERS") . "</option>
	<option value='disable'>" . T_("DISABLED_ACCOUNTS") . "</option>
	</select>&nbsp;&nbsp;<input type='submit' name='submit' value='Apply Changes'/>&nbsp;&nbsp;<input type='button' value='Check all' onClick='this.value=check(form)' /></td></tr>");

	if ($record_mail) {
		$ress = SQL_Query_exec("SELECT avps.value_s AS userid, avps.value_i AS last_mail, avps.value_u AS mails, users.username FROM avps LEFT JOIN users ON avps.value_s=users.id WHERE avps.arg='inactivemail' LIMIT 1");
		$date = mysqli_fetch_assoc($ress);
		if ($date["last_mail"] > 0)
			print("<tr><td colspan='7' class='table_head' align='center' style='color:red;'>" . T_("LASTMAILSENTBY") . " <a href='account-details.php?id=" . $date["userid"] . "'>" . $date["username"] . "</a> " . T_("ON") . " <b>" . gmdate("d M Y", $date["last_mail"]) . "</b> " . T_("_AND_") . " <b>" . $date["mails"] . "</b> mail" . ($date["mails"] > 1 ? "s" : "") . " " . T_("WASSENT") . "</td></tr>");
	}

	print("</table></form>");
} else {
	print("<h2 align=center>" . T_("NOACOUNTINATIVE") . " " . $days . " " . T_("DAYS") . ".</h2>");
}
end_frame();
stdfoot();
die;
?>