<?php
#================================#
#       TorrentTrader 2.08       #
#  http://www.torrenttrader.org  #
#--------------------------------#
#       Modified by BigMax       #
#================================#

require_once("backend/functions.php");
require_once("backend/config.php");
dbconn(false);

$site_config["LEFTNAV"] = $site_config["MIDDLENAV"] = $site_config["RIGHTNAV"] = false; # Hide all blocks!

stdhead(T_("WEBMASTER"));
begin_frame(T_("WEBMASTER"));

//*/===| For all parameters -> 1 = yes ; 0 = no |===/*//

$sujets = array('Help!','Support','Technical','Suggestion','Other');	# Possible topics for messages (for example add to take the first 5)
$choix_urgent = 0;	# You can choose to enable or disable the "urgent", and the member may indicate that his email is urgent or not
$choix_nom = 1;		# Name required?
$votre_mail = 0;	# View your email directly?
$contact_email = $site_config['SITEEMAIL']; # Email address you will receive messages at

//*/===| Don't change anything below! |===/*//

if (isset($_POST['envoyer']) && $_POST['envoyer'] == 'ok')
    {
    $reponse = '<br/>';
    $mail = htmlentities($_POST['mail']);
    $nom = htmlentities($_POST['nom']);
    $sujet = htmlentities($_POST['sujet']);
    $message = nl2br(htmlentities($_POST['message']));
    $urgent = htmlentities($_POST['urgent']);
    
    if ($choix_nom == 1)
        {
        if (!empty($nom))
            {
            $Snom = 1;
        } elseif (empty($nom))
            {
            $Snom = 0;
        }
    } else
        {
        $Snom = 1;
    }

    if (!empty($mail) && !empty($message) && $sujet != '' && $Snom == 1)
        {
        $entete = "MIME-Version: 1.0\r\n";
        $entete .= "Content-type: text/html; charset=utf-8\r\n";
        $entete .= "From: <$mail>\r\n";
        $entete .= "Reply-To: $mail\r\n";
        $email = '';

        if ($urgent == 1)
            $email .= '<div style=\'padding-top:3px\'><font size=2 color=red><b>Urgent message!</b></font></div>';

        if (empty($nom))
            $nom = '<font size=2 color=red><b>Not specified</b></font>';
			
  $QaptChaInput = $_SESSION['qaptcha_key'];
  
                if (!isset($_POST[$QaptChaInput]))
                        $message = 'Captcha failure.';
                else if (!$row)
                        $message = T_("USERNAME_INCORRECT");

        $email .= '<div style=\'padding-top:3px\'><font size=2>You received a message from your site <b>' . $site_config['SITENAME'] . '</b></font></div>
		<div style=\'padding-top:3px\'><font size=2>Reason: <b>' . $sujets[$sujet] . '</b></font></div>
		<div style=\'padding-top:3px\'><font size=2>Email: ' . $mail . '</font></div>';
        $email .= '<div style=\'padding-top:3px\'><font size=2>Sender: <b>' . $nom . '</b></font></div>
		<div style=\'padding-top:7px\'><font size=2>Message:</font></div>
		-------------------------------------------------------------------------<br />';
        $email .= $message;
        $email = stripslashes($email);
     //   sendmail($contact_email, '' . $sujets[$sujet], $email, $entete);
		sendmail('' , $sujets[$sujet], $email, $entete, "Content-type: text/html; charset=utf-8", "-f$site_config[SITEEMAIL]"); 
        $reponse .= '<font size=2>' . T_("TRANSFERRED_TO_WEBMASTER") . '</font><br />';
    } else {
        $reponse .= '<font size=2 color=red>' . T_("ALL_FIELDS") . '</font><br />';
    }
}
        unset($_SESSION['qaptcha_key']);
?>

<!--<style type="text/css">

.buttonInput {
    width: 180px;
	height: 20px;
background-color: #706D6D;
border-color: #3E3C32;
border-width: 1;
color: #FFFFFF;
font-size: 8pt;
}
.buttonSubmit {
    width: 70px;
	height: 20px;
background-color: #706D6D;
border-color: #3E3C32;
border-width: 1;
color: #FFFFFF;
font-size: 8pt;
cursor: pointer;
}

</style>-->

<form name="form1" method="post" action="">
<p><strong><?php echo $reponse;?></strong></p>
<br /><fieldset><legend><font size='2' color='red'><b><i><?php echo T_('CONTACT_SHEET');?></i></b></font></legend>
	<div style="margin-top:15px" align="center"><font size="2" color="#0080FF">If you have a problem and you cannot login or recover account, use this form to contact us!</font></div>
	<table align="center" cellpadding="5" border="0">
		<?php if ($votre_mail == 1) {
			echo '<tr><td style=\'padding-top:20px\'>' . T_('EMAIL_WEBMASTER') . ': </td><td style=\'padding-top:20px\'>' . $contact_email . '</td></tr>';
		} ?>
		<tr><td><font color='red'>*</font> <label><?php echo T_('YOUR_EMAIL');?>: </td><td><input type="text" class="buttonInput" name="mail" value="<?php print("" . $CURUSER["email"] . "\n");?>"></label></td></tr>
		<tr><td><font color='red'>*</font> <label><?php echo T_('YOUR_ACCOUNT_NAME');?>: </td><td><input type="text" class="buttonInput" name="nom" value="<?php print("" . $CURUSER["username"] . "\n");?>"></label></td></tr>
		<tr><td><font color='red'>*</font> <?php echo T_('SUBJECT_YOU_MESSAGE');?>: </td><td><select name="sujet">
		<?php for ($i = 0; $i < count($sujets); $i++) {
			echo '<option value="' . $i . '">' . $sujets[$i] . '</option>';
		} ?>
		</select>
		</td></tr>
		<?php
		if ($choix_urgent == 1) {
			echo '<tr><td>' . T_('YOUR_MESSAGE_IS_IT_URGENT') . ' </td><td>';
			echo '<label><input type="radio" name="urgent" value="1"> ' . T_('YES') . '</label> &nbsp;&nbsp;';
			echo '<label><input type="radio" name="urgent" value="0" checked> ' . T_('NO') . '</label>&nbsp;&nbsp;';
			echo '<font color=\'red\'>' . T_('PLEASE_ON_NOT_ABUSE_MESSAGE') . '</font>
		</td></tr>';
		}
		?>
		<tr><td><font color='red'>*</font> <?php echo T_('YOUR_MESSAGE');?>: </td><td><textarea name="message" cols="55" rows="5"></textarea></td></tr>
        <td colspan="2"><div class="QapTcha"></div></td>
		<tr><td colspan='2' align='center'>

			<input class="buttonSubmit" type="hidden" name="envoyer" value="ok">
			<input class="buttonSubmit" type="submit" name="Submit" value="Submit" />
			<input class="buttonSubmit" type="reset" name="Submit2" value="Reset" />
		</td></tr>
		<tr><td colspan='2'><font size ='2' color='red'><b><i><?php echo T_('REQUIRED_FIELDS2');?></i></b></font></td></tr>
	</table>
</fieldset>
</form>


	<div style="margin-top:20px; margin-bottom:10px" align="center">
		<a href="account-login.php"><?php echo T_("LOGIN"); ?></a> | 
		<a href="account-signup.php"><?php echo T_("SIGNUP"); ?></a> | 
		<a href="account-recover.php"><?php echo T_("RECOVER_ACCOUNT"); ?></a><br>
<center>
      <style>a.chacro{color:#FFF;font:bold 10px arial,sans-serif;text-decoration:none;}</style><table cellspacing="0"cellpadding="0"border="0"style="background:#999;width:230px;"><tr><td valign="top"style="padding: 1px 2px 5px 4px;border-right:solid 1px #CCC;"><span style="font:bold 30px arial,sans-serif;color:#666;top:0px;position:relative;">@</span></td><td valign="top" align="left" style="padding:3px 0 0 4px;"><a href="http://www.projecthoneypot.org/" target="_blank" class="chacro">MEMBER OF PROJECT HONEY POT</a><br/><a href="http://www.unspam.com"class="chacro">Spam Harvester Protection Network<br/>provided by Unspam</a></td></tr></table>
      </center>
	</div>
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
<?php
end_frame();
stdfoot();
?>