<?php
if (!$CURUSER) {
	begin_block(T_("LOGIN"));
?>
<form method="post" action="account-login.php">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr><td>
		<table border="0" cellpadding="1" align="center">
			<tr>
			<td align="center"><font face="verdana" size="1"><b><?php echo T_("USERNAME"); ?>:</b></font></td>
			</tr><tr>
			<td align="center"><input type="text" size="12" name="username" /></td>
			</tr><tr>
			<td align="center"><font face="verdana" size="1"><b><?php echo T_("PASSWORD"); ?>:</b></font></td>
			</tr><tr>
			<td align="center"><input type="password" size="12" name="password"  /></td>
			</tr><tr>
			<td align="center"><input type="submit" value="<?php echo T_("LOGIN"); ?>" /></td>
			</tr>
		</table>
		</td>
		</tr>
	<tr>
<td align="center">[<a href="account-signup.php"><?php echo T_("SIGNUP");?></a>]<br />[<a href="account-recover.php"><?php echo T_("RECOVER_ACCOUNT");?></a>]</td> </tr>
	</table>
    </form> 

<?php
end_block();

} else {

 begin_block('<a href="' . $site_config["SITEURL"] . '/account-details.php?id=' . $CURUSER["id"] . '">' . class_user($CURUSER["username"]) . '</a>');

	$avatar = htmlspecialchars($CURUSER["avatar"]);
	if (!$avatar)
		$avatar = $site_config["SITEURL"]."/images/default_avatar.png";

	$userdownloaded = mksize($CURUSER["downloaded"]);
	$useruploaded = mksize($CURUSER["uploaded"]);
	$privacylevel = T_($CURUSER["privacy"]);

	if ($CURUSER["uploaded"] > 0 && $CURUSER["downloaded"] == 0)
		$userratio = "Inf.";
	elseif ($CURUSER["downloaded"] > 0)
		$userratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 2);
	else
		$userratio = "---";
		
		//Porttest start

$query = SQL_Query_exec('SELECT `port` FROM `peers` WHERE `userid` = ' . $CURUSER['id'] . ' LIMIT 1');
	  $ports = mysqli_fetch_array($query);
if ($ports) {
	  $port = $ports['port'];
	  } else {
	  $port = "".T_('CONNECTION')."";
      }
//Porttest end
//////BROWSER DETECTION/////
$browser_id = (int) $CURUSER['browser'];
if ($browser_id > 0) {
        $res = SQL_Query_exec("SELECT name,browserpic FROM browsers WHERE id=$browser_id LIMIT 1");
        if (mysqli_num_rows($res) == 1) {
                $arr = mysqli_fetch_assoc($res);
                $browser = "<center>$arr[name]<br/><img src=images/browser/$arr[browserpic] height=45 width=45></center>";
        } else {
                $browser = "<center><img src=images/browser/unknown.png height=45 width=45></center>";
        }
}
///////END BROWSER DETECTION/////

////////OS DETECTION////////////
$shittyOSdisplay = "<center>$CURUSER[os]<br/><img src='".$site_config['SITEURL']."/images/oss/".$CURUSER["os"].".png' width=45 height=45></center>";
///////END OS DETECTION/////////

				//user country flags
$res = SQL_Query_exec("SELECT `name`, `flagpic` FROM `countries` WHERE id=$CURUSER[country] LIMIT 1") ;
if (mysqli_num_rows($res) == 1)    {
$arr = mysqli_fetch_assoc($res);
$country ="<center>$arr[name]<br/><img src= images/countries/$arr[flagpic] height=25 width=36 border=0></center>";
}
else{
$country = "<center>".T_('DUNNOCOUNTRY')."<br/><img src= 'images/countries/unknown.gif' width='50' /></center>"; ///change default image 
}

$loggedin = get_elapsed_time(strtotime($CURUSER['last_login']));
	print ("<center><img width='80' height='80' src='$avatar' alt='' /></center><br />" . T_("DOWNLOADED") . ": $userdownloaded<br />" . T_("UPLOADED") . ": $useruploaded<br />".T_("CLASS").": ".T_($CURUSER["level"])."<br />" . T_("ACCOUNT_PRIVACY_LVL") . ": $privacylevel<br />" . T_("LOGGED_IN_FOR") . ": $loggedin<br />". T_("RATIO") .": $userratio<br /><hr/>Port: <a href='testport.php' title='Porttest'>".$port."</a>");

	$connectable = get_row_count("peers", "WHERE connectable='yes' AND userid=$CURUSER[id]");
	$unconnectable = get_row_count("peers", "WHERE connectable='no' AND userid=$CURUSER[id]");
if ($unconnectable)
        print "<br>".T_('CONNECTABLE')."<b><font color='#FF0000' class='blink'>".T_('CONNECTABLENO')."</font></b>";
elseif ($connectable)
        print "<br>".T_('CONNECTABLE')."<b><font color='#00FF00'>".T_('CONNECTABLEYES')."</font></b>";
else
        print "<br>".T_('CONNECTABLE')."<b><font color='#FF9900'>".T_('CONNECTABLENA')."</font></b>";

//	$loggedin = get_elapsed_time(strtotime($CURUSER['last_login']));
//	$ipdisplay = $_SERVER['REMOTE_ADDR'];
//	echo "<br />IP Address: " , $ipdisplay , "";
$ip = getip();
$query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));
if($query && $query['status'] == 'success') {
$userlocation = "Signed in from: <b>" . $query['city'].'</b>, <b>'.$query['region'].'</b>. <b>'.$query['country'].'</b><div>IP: <b>'.getip()."</b></div>";
}
echo "<br />", $userlocation, "";
	

//	echo"<br /><font color='#FF9900'>(only you can see this IP)</font>";
		print ("<hr/>");
//        echo "<br />Logged in for: " , $loggedin , "";
		echo "<center>Country:</center>" , $country ; ?>
        
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr><td>
		<table border="0" cellpadding="1" align="center">
			<tr>
			<td align="center"> <?php echo "<br />" , $browser , "<br />"; ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td align="center"> <?php echo "<br />", $shittyOSdisplay, "<br />";?></td>
			</tr>
		</table>
		</td>
		</tr>
	</table>
<center><a href="account.php"><?php echo T_("ACCOUNT"); ?></a>
<?php if($CURUSER["level"]=="Super Moderator") {print ("<br/><a href=supermodcp.php>".T_("SUPERMOD_CP")."</a>");}?>
<?php if($CURUSER["level"]=="Moderator") {print("<br/><a href=modcp.php>".T_("MOD_CP")."</a>");}?>
<?php if ($CURUSER["control_panel"]=="yes") {print("<br/><a href=\"admincp.php\">".T_("STAFFCP")."</a>");}?>
</center>
<?php
end_block();
}

?>