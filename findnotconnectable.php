<?php
require_once("backend/functions.php");
dbconn();
loggedinonly();

if (!$CURUSER || $CURUSER["control_panel"]!="yes") {
 show_error_msg("Error","Sorry you do not have the rights to access this page!",1);
}

if ($_GET['action'] == "list") {
	$res2 = SQL_Query_exec("SELECT userid, seeder, torrent, client FROM peers WHERE connectable='no' ORDER BY userid DESC") or sqlerr();

	stdhead("Peers that are unconnectable");
	
begin_frame("Unconnectable");

	print("<br/><a href=findnotconnectable.php?action=sendpm>Send all not connectable users a PM</a>");
	print("<br/><a href=findnotconnectable.php?action=viewlogs>View the Log (Check this before PMing users)</a>");
	print("<br/>Peers that are Not Connectable");
	print("<br/>This is only users that are active on the torrents right now.");
	print("<br/><font color=red>*</font> means the user is seeding.<p>");
	$result = SQL_Query_exec("SELECT DISTINCT userid FROM peers WHERE connectable = 'no'");
	$count = mysqli_num_rows($result);
	print ("$count unique users that are not connectable ");
	$result2 = SQL_Query_exec("SELECT DISTINCT torrent FROM peers WHERE connectable = 'no'");
	$count2 = mysqli_num_rows($result2);
	print ("on $count2  torrents.");
	@mysqli_free_result($result);

if (mysqli_num_rows($res2) == 0)
	print("<p align=center><b>All Peers Are Connectable! </b></p>\n");
else {
	print("<table border=1 cellspacing=0 cellpadding=5>\n");
	print("<tr><td class=colhead>UserName</td><td class=colhead>Torrent</td><td class=colhead>Client</td></tr>\n");

while($arr2 = mysqli_fetch_assoc($res2)){
	$r2 = SQL_Query_exec("SELECT username FROM users WHERE id=$arr2[userid]") or sqlerr();
	$a2 = mysqli_fetch_assoc($r2);
	print("<tr><td><a href=account-details.php?id=$arr2[userid]>$a2[username]</a></td><td align=left><a href=torrents-details.php?id=$arr2[torrent]&dllist=1#seeders>$arr2[torrent]");

if ($arr2['seeder'] == 'yes')
	print("<font color=red>*</font>");
	print("</a></td><td align=left>$arr2[client]</td></tr>\n");
}
	print("</table>\n");
}
end_frame();
}

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST") {
	$dt = sqlesc(get_date_time());
	$msg = $_POST['msg'];


if (!$msg)
	show_error_msg("Error", "Please Type In Some Text.", 1);
	$query = SQL_Query_exec("SELECT distinct userid FROM peers WHERE connectable='no'");

while($dat=mysqli_fetch_assoc($query)){
	SQL_Query_exec("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES (0,$dat[userid] , '" . get_date_time() . "', " . sqlesc($msg) .", '0')") or sqlerr(__FILE__,__LINE__); }
	SQL_Query_exec("INSERT INTO notconnectablepmlog ( user , date ) VALUES ( $CURUSER[id], $dt)") or sqlerr(__FILE__,__LINE__);
	write_log("A PM was sent to unconnectable users by ($CURUSER[username])");
	
	die;
}

if ($_GET['action'] == "sendpm") {
	stdhead("Peers that are unconnectable");
begin_frame("Send PM");
?>

	<table class="main" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class="embedded"><div align="center"><h1>Mass Message to All Non Connectable Users</a></h1>
	<form method="post" action="findnotconnectable.php">

<?php
if (isset($_GET["returnto"]) || isset($_SERVER["HTTP_REFERER"])) {
?>
	<input type="hidden" name="returnto" value=<?php echo $_GET["returnto"] ? $_GET["returnto"] : $_SERVER["HTTP_REFERER"] ?>  />


<?php
}
//default message
	$body = "(If you are using a laptop not on your local network, ignore this message). The tracker has determined that you are firewalled or NATed and cannot accept incoming connections. This means that other peers in the swarm will be unable to connect to you, only you to them. Even worse, if two peers are both in this state they will not be able to connect at all. This has obviously a detrimental effect on the overall speed. The way to solve the problem involves opening the ports used for incoming connections (the same range you defined in your client) on the firewall and/or configuring your NAT server to use a basic form of NAT for that range instead of NAPT (the actual process differs widely between different router models. Check your router documentation and/or support forum. You will also find lots of information on the subject at [url=http://portforward.com/english/routers/port_forwarding/routerindex.htm]PortForward[/url]). Also if you need help please post in the forums or shoutbox your problems and we will try and figure it out. Thank You";
?>

<table cellspacing="0" cellpadding="5">
<tr>
<td>Send Mass Message To All Non Connectable Users<br>
<table border="0" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td border="0">&nbsp;</td>
<td border="0">&nbsp;</td>
</tr>
</table>
</td>
</tr>
<tr>
<td><textarea name="msg" cols="120" rows="15"><?php echo $body?></textarea></td>
</tr>
<tr>
<tr>
<td colspan="2" align="center"><input type="submit" value="Send the fucking PM!" class="btn"></td>
</tr>
</table>
<input type="hidden" name="receiver" value=<?php echo $receiver?>  />
</form>
</div>
</td>
</tr>
</table>
<br>NOTE: Blow me
<?php
end_frame();
}

//if ($site_config["WELCOMEPMON"]) {
    //$dt = sqlesc(get_date_time());
    //$msg = sqlesc($site_config["WELCOMEPMMSG"]);
    //SQL_Query_exec("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, $id, $dt, $msg, 0)");
//}

if ($_GET['action'] == "viewlogs") {
stdhead("Unconnectable Peers Mass PM Log");
begin_frame("PM Logs");
$getlog = SQL_Query_exec("SELECT * FROM notconnectablepmlog LIMIT 10");
print("Unconnectable Peers Mass PM Log");
print("<br/><br/><a href=findnotconnectable.php?action=sendpm>&nbsp;&nbsp;<b>* Send all not connectable users a PM</b></a>");
print("<br/><a href=findnotconnectable.php?action=list>&nbsp;&nbsp;<b>* List Unconnectable Users</b></a>");
print("<br/><br/>Please dont use the mass PM too often. We dont want to spam the users, just let them know they are unconnectable.<br/>");
print("Every week would be ok.<br/><br/>");
print("<table border=1 cellspacing=0 cellpadding=5>\n");
print("<br/><tr><td class=colhead>By User</td><td class=colhead>Date</td><td class=colhead>elapsed</td></tr>");
while($arr2 = mysqli_fetch_assoc($getlog)){
$r2 = SQL_Query_exec("SELECT username FROM users WHERE id=$arr2[user]") or sqlerr();
$a2 = mysqli_fetch_assoc($r2);
$elapsed = get_elapsed_time(sql_timestamp_to_unix_timestamp($arr2[date]));
print("<tr><td class=colhead><a href=account-details.php?id=$arr2[user]>$a2[username]</a></td><td class=colhead>$arr2[date]</td><td>$elapsed ago</td></tr>");
}
print("</table>");
}
end_frame();
stdfoot();
?>