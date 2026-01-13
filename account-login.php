<?php
//
//  TorrentTrader v2.x
//      $LastChangedDate: 2012-09-19 19:13:35 +0100 (Wed, 19 Sep 2012) $
//      $LastChangedBy: torrenttrader $
//
//      http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();
session_start();
$site_config["LEFTNAV"] = $site_config["MIDDLENAV"] = $site_config["RIGHTNAV"] = false;
//if (!empty($_REQUEST["returnto"])) {
//	if (!$_GET["nowarn"]) {    
//		 $nowarn = T_("MEMBERS_ONLY");
//	}
//}

if ($_POST["username"] && $_POST["password"]) {
	$password = passhash($_POST["password"]);

	if (!empty($_POST["username"]) && !empty($_POST["password"])) {
		$res = SQL_Query_exec("SELECT id, password, secret, status, enabled FROM users WHERE username = " . sqlesc($_POST["username"]) . "");
		$row = mysqli_fetch_assoc($res);
  $QaptChaInput = $_SESSION['qaptcha_key'];
        if (!isset($_POST[$QaptChaInput]))
             $message = 'Captcha failure.';
		elseif ( ! $row || $row["password"] != $password )
			$message = T_("LOGIN_INCORRECT");
		elseif ($row["status"] == "pending")
			$message = T_("ACCOUNT_PENDING");
		elseif ($row["enabled"] == "no")
			$message = T_("ACCOUNT_DISABLED");
	} else
		$message = T_("NO_EMPTY_FIELDS");
    unset($_SESSION['qaptcha_key']);
	if (!$message){
		logincookie($row["id"], $row["password"], $row["secret"]);
		if (!empty($_POST["returnto"])) {
			header("Refresh: 0; url=" . $_POST["returnto"]);
			die();
		}
		else {
			header("Refresh: 0; url=index.php");
			die();
		}
	}else{
		show_error_msg(T_("ACCESS_DENIED"), $message, 1);
	}
}

logoutcookie();

stdhead(T_("LOGIN"));
 
 if ($nowarn)
      show_error_msg(T_("ERROR"), $nowarn, 0);
      
begin_frame(T_("LOGIN"));

?>

<form method="post" action="account-login.php">
  <table border="0" cellpadding="2" align="center">
		<tr><td align="center"><b><?php echo T_("USERNAME"); ?>:</b> <input type="text" size="40" name="username" /></td></tr>
		<tr><td align="center"><b><?php echo T_("PASSWORD"); ?>: </b> <input type="password" size="40" name="password" /></td></tr>
        <tr><td><div class="QapTcha"></div></td></tr>
		<tr><td colspan="2" align="center"><input type="submit" value="<?php echo T_("LOGIN"); ?>" /><br /><br /><i><?php echo T_("COOKIES");?></i></td></tr>
	</table>
<?php

if ( ! empty($_REQUEST["returnto"]) )
{ 
      print("<input type=\"hidden\" name=\"returnto\" value=\"" . cleanstr($_REQUEST["returnto"]) . "\" />\n");
}
?>

</form>
<p align="center"><a href="account-signup.php"><?php echo T_("SIGNUP"); ?></a> | <a href="account-recover.php"><?php echo T_("RECOVER_ACCOUNT"); ?></a></p>
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
<center>
      <style>a.chacro{color:#FFF;font:bold 10px arial,sans-serif;text-decoration:none;}</style><table cellspacing="0"cellpadding="0"border="0"style="background:#999;width:230px;"><tr><td valign="top"style="padding: 1px 2px 5px 4px;border-right:solid 1px #CCC;"><span style="font:bold 30px arial,sans-serif;color:#666;top:0px;position:relative;">@</span></td><td valign="top" align="left" style="padding:3px 0 0 4px;"><a href="http://www.projecthoneypot.org/" target="_blank" class="chacro">MEMBER OF PROJECT HONEY POT</a><br/><a href="http://www.unspam.com"class="chacro">Spam Harvester Protection Network<br/>provided by Unspam</a></td></tr></table>
      </center>
<?php

end_frame();
stdfoot();
?>