<?php
##################################################
#          Originally Coded for TBDev            #
#             Modified By Dalibor                #
#            Last Modified 28-6-2008             #
#         Modified to 2.08 By ArcticWolf         #
##################################################

/* Connect To Database, And ONLY Allow Members */
require_once("backend/functions.php");
dbconn();
loggedinonly();
$action = $_REQUEST["action"];

if ($action == "taketransfer") {
    $username = $_POST["username"];
   
if (!$username)
    show_error_msg("Error", "You are not logged in.", 1);
   
    $credit = $_POST["credit"];
   
if ($credit < 1 || (!$CURUSER))
    show_error_msg("Error", "Transfer is too small.", 1);
   
    $kb = 1024;
    $mb = 1024 * 1024;
    $gb = 1024 * 1024 * 1024;
    $tb = 1024 * 1024 * 1024 * 1024;
   
if ($_POST["unit"] == 'mb')
    $credit = $credit * $mb;
elseif ($_POST["unit"] == 'gb')
    $credit = $credit * $gb;
elseif ($_POST["unit"] == 'tb')
    $credit = $credit * $tb;
   
if ($CURUSER["uploaded"] < $credit)
    show_error_msg("Error", "Vous tentez de transférer des dons upload alors que vous avez actuellement.", 1);
   
    $query = SQL_Query_exec("SELECT id,uploaded,modcomment FROM users WHERE username = '$username'");
    $res = mysqli_fetch_assoc($query);
    $receiver = $res["id"];
    $sender = $CURUSER["id"];
    $modcomment2 = $CURUSER["modcomment"];
    $modcomment1 = $res["modcomment"];
    $modcomment1 = gmdate("Y-m-d") . " - Credit upload: $username Obtained " . mksize($credit) . " from $site_config[SITEURL]/account-details.php?id=$sender " . $CURUSER['username'] . ".\n" . $modcomment1;
    $modcomment2 = gmdate("Y-m-d") . " - Credit upload: $site_config[SITEURL]/account-details.php?id=$sender " . $CURUSER['username'] . " a donner " . mksize($credit) . " a $username.\n" . $modcomment2;
   
if (!$receiver) autolink("uptransfer.php", T_("USER_NOT_FOUND"));
    SQL_Query_exec("UPDATE users SET uploaded = uploaded + $credit, modcomment = " . sqlesc($modcomment1) . " WHERE id = '$receiver'");
    SQL_Query_exec("UPDATE users SET uploaded = uploaded - $credit, modcomment = " . sqlesc($modcomment2) . " WHERE id = '$sender'");
   
   $subject = "You got an upload credit!";
   
if ($_POST["anonym"] != 'anonym') {
    $msg = sqlesc("Your account has been credited " . mksize($credit) . " compliments of " . $CURUSER['username'] . "");
   
   SQL_Query_exec("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES($sender, $receiver, NOW(), $msg, 0," . sqlesc($subject) . ")");
} else {
    $msg = sqlesc("Your account has been credited " . mksize($credit) . " compliments of an anonymous user.");
   
   SQL_Query_exec("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES(0, $receiver, NOW(), $msg, 0, " . sqlesc($subject) . ")");
}
    write_log("<font color=lime size=2>Credit Upload:</font><a href=account-details.php?id=$sender target=_blank>" . $CURUSER['username'] . "</a> a offert " . mksize($credit) . " a <a href=account-details.php?id=$receiver target=_blank>$username</a>");
   
    autolink($site_config[SITEURL] . "/index.php", "You are giving " . mksize($credit) . " in upload credit to " . $username);
    // header("Refresh: 0; url=index.php");  // I guess this would work under 2.08... No clue LOL
die();
}

/*  Start Page Output  */
   stdhead("Donate Uploads");

begin_frame( T_('Q&A') );
?>
<b><?php echo T_("WHY_SHOULD_DONATE"); ?></b><br />
<?php echo T_("CREDIT_UP_TEXT1"); ?>
<br /><br />
<b><?php echo T_("WHY_SHOULD_DONATE_UP"); ?></b><br />
<?php echo T_("CREDIT_UP_TEXT2"); ?>
<?php

end_frame();

begin_frame( T_('TRANSFER_UPLOAD') );
?>
<center>
   <form name=transfer method=post action=uptransfer.php?action=taketransfer>
   <table width=500 cellpadding=5><tr><td width=100><b><?php echo T_("DONATE_TO"); ?>: </b></td><td><input type=text name=username size=20> <input type=checkbox name=anonym value=anonym> <?php echo T_("ANONYMOUS_TRANSFER"); ?></td></tr>&nbsp;
<tr><td width=100><b><?php echo T_("AMOUNT_CREDIT"); ?>:</b></td><td><input type=text name=credit size=20 value=1>
<select name=unit><br />
<option value=false>Selection</option>
<option value=mb>KB</option>
<option value=mb>MB</option>
<option value=gb>GB</option>
<option value=tb>TB</option>
</select></td></tr>
<tr><td colspan=2><center><input name=submit type=submit value=Transfer!></center></td>
</tr></table></form></center>
<?php

end_frame();
stdfoot();
?>