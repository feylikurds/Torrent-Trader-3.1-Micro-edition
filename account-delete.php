<?php

require_once("backend/functions.php");
dbconn();

if (get_user_class() >= 5)
        show_error_msg(T_("ERROR"), T_("NO_DELETED_USER"), 1);

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
$username = trim($_POST["username"]);
$password = trim($_POST["password"]);

if (!$username || !$password)
show_error_msg("Couldn't delete account", "Please fill out the form correctly.",1);

$password = passhash($_POST["password"]);
$res = SQL_Query_exec("SELECT id, status FROM users WHERE username=" . sqlesc($username) . " AND password =" . sqlesc($password));
if (mysqli_num_rows($res) != 1)
show_error_msg(T_("COULDN_T_DELETE"), T_("UNABLE_TO_DELETE_PASSWORLD_NAME"), 1);
$arr = mysqli_fetch_assoc($res);

// Uncomment this if you don't want to be able to delete confirmed accounts.
/*
if ($arr["status"] == "confirmed")
show_error_msg("Couldn't delete account", "Sorry, you can not delete a confirmed account.",1);
*/

deleteaccount($arr['id']);
header("Refresh: 5 ;url=index.php");
show_error_msg(T_("THE_ACCOUNT"), T_("WILL_REDIRECT"), 1);
}

stdhead("Delete account");
begin_frame("<font color=2fdceb>".T_("DELETE_ACCOUNT")."</font>");
?>
<?php echo T_("THANKS_FOR_USING"); ?> <b><?=$site_config['SITENAME']?></b>. <?php echo T_("DELETE_USER_INFO"); ?><br /><br />
<table border='0' cellspacing='0' cellpadding='5'><form method=post action=account-delete.php>
<tr><td><b><?php echo T_("USERNAME"); ?> :</b></td><td align='left'><input type=text size=40 name=username></td></tr>
<tr><td><b><?php echo T_("PASSWORD"); ?> :</b></td><td align='left'><input type=password size=40 name=password></td></tr>
<tr><td colspan='2' align='center'><input type='submit' value='Delete Account'></td></tr>
</table>
</form>

<?php
end_frame();
stdfoot();
?>