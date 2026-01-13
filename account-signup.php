<?php
//
//  TorrentTrader v2.x
//	$LastChangedDate: 2012-09-27 22:15:34 +0100 (Thu, 27 Sep 2012) $
//      $LastChangedBy: torrenttrader $
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();
session_start();
$site_config["LEFTNAV"] = $site_config["MIDDLENAV"] = $site_config["RIGHTNAV"] = false;
$username_length = 15; // Max username length. You shouldn't set this higher without editing the database first
$password_minlength = 6;
$password_maxlength = 40;

// Disable checks if we're signing up with an invite
if (!is_valid_id($_REQUEST["invite"]) || strlen($_REQUEST["secret"]) != 32) {
	//invite only check
	if ($site_config["INVITEONLY"]) {
		show_error_msg(T_("INVITE_ONLY"), "<br /><br /><center>".T_("INVITE_ONLY_MSG")."<br /><br /></center>",1);
	}

	//get max members, and check how many users there is
	$numsitemembers = get_row_count("users");
	if ($numsitemembers >= $site_config["maxusers"])
		show_error_msg(T_("SORRY")."...", T_("SITE_FULL_LIMIT_MSG") . number_format($site_config["maxusers"])." ".T_("SITE_FULL_LIMIT_REACHED_MSG")." ".number_format($numsitemembers)." members",1);
} else {
	$res = SQL_Query_exec("SELECT id FROM users WHERE id = $_REQUEST[invite] AND MD5(secret) = ".sqlesc($_REQUEST["secret"]));
	$invite_row = mysqli_fetch_assoc($res);
	if (!$invite_row) {
		show_error_msg(T_("ERROR"), T_("INVITE_ONLY_NOT_FOUND")." ".($site_config['signup_timeout']/86400)." days.", 1);
	}
}

if ($_GET["takesignup"] == "1") {

$message == "";

function validusername($username) {
		$allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		for ($i = 0; $i < strlen($username); ++$i)
			if (strpos($allowedchars, $username[$i]) === false)
			return false;
		return true;
}

	$wantusername = $_POST["wantusername"];
	$email = $_POST["email"];
	$wantpassword = $_POST["wantpassword"];
	$passagain = $_POST["passagain"];
	$country = $_POST["country"];
	$gender = $_POST["gender"];
	$client = $_POST["client"];
	$age = (int) $_POST["age"];

  if (empty($wantpassword) || (empty($email) && !$invite_row) || empty($wantusername))
	$message = T_("DONT_LEAVE_ANY_FIELD_BLANK");
  elseif (strlen($wantusername) > $username_length)
	$message = sprintf(T_("USERNAME_TOO_LONG"), $username_length);
  elseif ($wantpassword != $passagain)
	$message = T_("PASSWORDS_NOT_MATCH");
  elseif (strlen($wantpassword) < $password_minlength)
	$message = sprintf(T_("PASS_TOO_SHORT"), $password_minlength);
  elseif (strlen($wantpassword) > $password_maxlength)
	$message = sprintf(T_("PASS_TOO_LONG"), $password_maxlength);
  elseif ($wantpassword == $wantusername)
 	$message = T_("PASS_CANT_MATCH_USERNAME");
  elseif (!validusername($wantusername))
	$message = "Invalid username.";
  elseif (!$invite_row && !validemail($email))
		$message = "That doesn't look like a valid email address.";
	
///captcha shit. Delete if not working////////
$QaptChaInput = $_SESSION['qaptcha_key'];
    if (!isset($_POST[$QaptChaInput]))
        $message = 'Captcha failure.';
        unset($_SESSION['qaptcha_key']);
///end captcha shit//////

	if ($message == "") {
		// Certain checks must be skipped for invites
		if (!$invite_row) {
			//check email isnt banned
			$maildomain = (substr($email, strpos($email, "@") + 1));
			$a = (@mysqli_fetch_row(@SQL_Query_exec("select count(*) from email_bans where mail_domain='$email'")));
			if ($a[0] != 0)
				$message = sprintf(T_("EMAIL_ADDRESS_BANNED_S"), $email);

			$a = (@mysqli_fetch_row(@SQL_Query_exec("select count(*) from email_bans where mail_domain LIKE '%$maildomain%'")));
			if ($a[0] != 0)
				$message = sprintf(T_("EMAIL_ADDRESS_BANNED_S"), $email);

		  // check if email addy is already in use
		  $a = (@mysqli_fetch_row(@SQL_Query_exec("select count(*) from users where email='$email'")));
		  if ($a[0] != 0)
			$message = sprintf(T_("EMAIL_ADDRESS_INUSE_S"), $email);
		}

	   //check username isnt in use
	  $a = (@mysqli_fetch_row(@SQL_Query_exec("select count(*) from users where username='$wantusername'")));
	  if ($a[0] != 0)
		$message = sprintf(T_("USERNAME_INUSE_S"), $wantusername); 

//	  $secret = mksecret(); //generate secret field //commented due to IP check also using same vars below

//	  $wantpassword = passhash($wantpassword);// hash the password //commented due to IP check also using same vars below
	}

	if ($message != "")
		show_error_msg(T_("SIGNUP_FAILED"), $message, 1);
	
			//check if IP is already a peer
            if ($site_config["ipcheck"] && $site_config["accountmax"] >= "1") {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ipq = get_row_count("users", "WHERE ip = '$ip'");
            if ($ipq >= $site_config["accountmax"])
            $message = $site_config["SITENAME"]." only allows " . $site_config["accountmax"] . " user account per IP address. <br> If you want to create a new account, please contact an administrator.<br /><br /><font color=red> The limit for $ip has been reached.</font><br /><br />Registration failed. <br /><br /><a style='cursor: pointer;cursor:hand;' href='account-login.php'><font color=red>Click here to return to the login page.</font></a>";
    }
            $secret = mksecret(); //generate secret field

            $wantpassword = passhash($wantpassword);// hash the password
    

    if ($message != "")
        show_error_msg(T_("SIGNUP_FAILED"), $message, 1);

  if ($message == "") {
		if ($invite_row) {
			SQL_Query_exec("UPDATE users SET username=".sqlesc($wantusername).", password=".sqlesc($wantpassword).", secret=".sqlesc($secret).", status='confirmed', added='".get_date_time()."' WHERE id=$invite_row[id]");
			//send pm to new user
			if ($site_config["WELCOMEPMON"]){
				$dt = sqlesc(get_date_time());
				$msg = sqlesc($site_config["WELCOMEPMMSG"]);
				SQL_Query_exec("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, $invite_row[id], $dt, $msg, 0)");
			}
			header("Refresh: 0; url=account-confirm-ok.php?type=confirm");
			die;
		}

	if ($site_config["CONFIRMEMAIL"]) { //req confirm email true/false
		$status = "pending";
	}else{
		$status = "confirmed";
	}

	//make first member admin
	if ($numsitemembers == '0')
		$signupclass = '8';
	else
		$signupclass = '1';

     SQL_Query_exec("INSERT INTO users (username, password, secret, email, status, added, last_access, age, country, gender, client, stylesheet, uploaded, downloaded, language, invites, class, ip) VALUES (" .
     implode(",", array_map("sqlesc", array($wantusername, $wantpassword, $secret, $email, $status, get_date_time(), get_date_time(), $age, $country, $gender, $client, $site_config["default_theme"], $site_config["new_member_upload_ratio"], 0, $site_config["default_language"], $site_config["new_member_invites"], $signupclass, getip()))).")");

    $id = mysqli_insert_id($GLOBALS["DBconnector"]);

    $psecret = md5($secret);
    $thishost = $_SERVER["HTTP_HOST"];
    $thisdomain = preg_replace('/^www\./is', "", $thishost);

	//ADMIN CONFIRM
	if ($site_config["ACONFIRM"]) {
		$body = T_("YOUR_ACCOUNT_AT")." ".$site_config['SITENAME']." ".T_("HAS_BEEN_CREATED_YOU_WILL_HAVE_TO_WAIT")."\n\n".$site_config['SITENAME']." ".T_("ADMIN");
	}else{//NO ADMIN CONFIRM, BUT EMAIL CONFIRM
		$body = T_("YOUR_ACCOUNT_AT")." ".$site_config['SITENAME']." ".T_("HAS_BEEN_APPROVED_EMAIL")."\n\n	".$site_config['SITEURL']."/account-confirm.php?id=$id&secret=$psecret\n\n".T_("HAS_BEEN_APPROVED_EMAIL_AFTER")."\n\n	".T_("HAS_BEEN_APPROVED_EMAIL_DELETED")."\n\n".$site_config['SITENAME']." ".T_("ADMIN");
	}

	if ($site_config["CONFIRMEMAIL"]){ //email confirmation is on
		sendmail($email, "Your $site_config[SITENAME] User Account", $body, "", "-f$site_config[SITEEMAIL]");
		header("Refresh: 0; url=account-confirm-ok.php?type=signup&email=" . urlencode($email));
	}else{ //email confirmation is off
		header("Refresh: 0; url=account-confirm-ok.php?type=noconf");
	}
	//send pm to new user
	if ($site_config["WELCOMEPMON"]){
		$dt = sqlesc(get_date_time());
		$msg = sqlesc($site_config["WELCOMEPMMSG"]);
		SQL_Query_exec("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, $id, $dt, $msg, 0)");
	}

    die;
  }

}//end takesignup



stdhead(T_("SIGNUP"));
begin_frame(T_("SIGNUP"));
?>
<?php echo T_("COOKIES"); ?>

<form method="post" action="account-signup.php?takesignup=1">
	<?php if ($invite_row) { ?>
	<input type="hidden" name="invite" value="<?php echo $_GET["invite"]; ?>" />
	<input type="hidden" name="secret" value="<?php echo htmlspecialchars($_GET["secret"]); ?>" />
	<?php } ?>
	<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo T_("USERNAME"); ?>: <font class="required">*</font></td>
				<td><input type="text" size="40" name="wantusername" /></td>
			</tr>
			<tr>
				<td><?php echo T_("PASSWORD"); ?>: <font class="required">*</font></td>
				<td><input type="password" size="40" name="wantpassword" /></td>
			</tr>
			<tr>
				<td><?php echo T_("CONFIRM"); ?>: <font class="required">*</font></td>
				<td><input type="password" size="40" name="passagain" /></td>
			</tr>
			<?php if (!$invite_row) {?>
			<tr>
				<td><?php echo T_("EMAIL"); ?>: <font class="required">*</font></td>
				<td><input type="text" size="40" name="email" /></td>
			</tr>
			<?php } ?>
			<tr>
				<td><?php echo T_("AGE"); ?>:</td>
				<td><input type="text" size="40" name="age" maxlength="3" /></td>
			</tr>
			<tr>
				<td><?php echo T_("COUNTRY"); ?>:</td>
				<td>
					<select name="country" size="1">
						<?php
						$countries = "<option value=\"0\">---- ".T_("NONE_SELECTED")." ----</option>\n";
						$ct_r = SQL_Query_exec("SELECT id,name,domain from countries ORDER BY name");
						while ($ct_a = mysqli_fetch_assoc($ct_r)) {
							$countries .= "<option value=\"$ct_a[id]\">$ct_a[name]</option>\n";
						}
						?>
						<?php echo $countries; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php echo T_("GENDER"); ?>:</td>
				<td>
					<input type="radio" name="gender" value="Male" /><?php echo T_("MALE"); ?>
					&nbsp;&nbsp;
					<input type="radio" name="gender" value="Female" /><?php echo T_("FEMALE"); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo T_("PREF_BITTORRENT_CLIENT"); ?>:</td>
				<td><input type="text" size="40" name="client"  maxlength="20" /></td>
			</tr>
		<!--captcha shit below. Delete if it sucks -->
            <tr><td colspan="2"><div class="QapTcha"></div></td></tr>
		<!-- End captcha shit -->
			<tr>
				<td align="center" colspan="2">
                <input type="submit" value="<?php echo T_("SIGNUP"); ?>" />
              </td>
			</tr>
	</table>
</form>

<!--captcha shit. Delete if not working-->
<link rel="stylesheet" href="jquery/QapTcha.jquery.css" type="text/css" />
<script type="text/javascript" src="jquery/jquery.js"></script>
<script type="text/javascript" src="jquery/jquery-ui.js"></script>
<script type="text/javascript" src="jquery/jquery.ui.touch.js"></script>
<script type="text/javascript" src="jquery/QapTcha.jquery.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('.QapTcha').QapTcha({disabledSubmit:true,autoRevert:true});
	});
</script>
<!--end captcha shit -->

<?php
end_frame();
?>
<p align="center"><a href="account-signup.php"><?php echo T_("SIGNUP"); ?></a> | <a href="account-recover.php"><?php echo T_("RECOVER_ACCOUNT"); ?></a> | <a href="contact.php"><?php echo T_("CONTACT_WEBMASTER"); ?></a></p>

<center>
      <style>a.chacro{color:#FFF;font:bold 10px arial,sans-serif;text-decoration:none;}</style><table cellspacing="0"cellpadding="0"border="0"style="background:#999;width:230px;"><tr><td valign="top"style="padding: 1px 2px 5px 4px;border-right:solid 1px #CCC;"><span style="font:bold 30px arial,sans-serif;color:#666;top:0px;position:relative;">@</span></td><td valign="top" align="left" style="padding:3px 0 0 4px;"><a href="http://www.projecthoneypot.org/" target="_blank" class="chacro">MEMBER OF PROJECT HONEY POT</a><br/><a href="http://www.unspam.com"class="chacro">Spam Harvester Protection Network<br/>provided by Unspam</a></td></tr></table>
      </center>
<?php
stdfoot();
?>