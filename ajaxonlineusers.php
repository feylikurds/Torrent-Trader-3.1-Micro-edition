<?php
require_once("backend/functions.php");
dbconn(true);
global $CURUSER;
if(isset($CURUSER)){
//USERS ONLINE

$file = "".$site_config["cache_dir"]."/cache_usersonlineblock.txt";
$expire = 10; // time in seconds
if (file_exists($file) &&
    filemtime($file) > (time() - $expire)) {
    $usersonlinerecords = unserialize(file_get_contents($file));
}else{ 
	$usersonlinequery = SQL_Query_exec("SELECT class, id, username, warned, donated FROM users WHERE privacy!='strong' AND UNIX_TIMESTAMP('" . get_date_time() . "') - UNIX_TIMESTAMP(users.last_access) < 900");
	
	while ($usersonlinerecord = mysqli_fetch_array($usersonlinequery) ) {
        $usersonlinerecords[] = $usersonlinerecord;
    }
    $OUTPUT = serialize($usersonlinerecords);
    $fp = fopen($file,"w");
    fputs($fp, $OUTPUT);
    fclose($fp);
} // end else 
if ($usersonlinerecords == ""){
	echo "No Users Online";
}else{
	foreach ($usersonlinerecords as $id=>$row) {


switch ($row[0]) {
case 8: // Site owner:
$user = class_user($row['username']);
break;
case 7: // Administrator:
$user = class_user($row['username']);
break;
case 6: // Super Moderator
$user = class_user($row['username']);
break;
case 5: // Moderator
$user = class_user($row['username']);
break;
case 4: // Uploader
$user = class_user($row['username']);
break;
case 3: // VIP
$user = class_user($row['username']);
break;
case 2: // Power User
$user = class_user($row['username']);
break;
case 1: // User
$user = class_user($row['username']);
break;
//case 0: // System
//$user = "<font color=red>".$row[username]."</font>";
}

$warn=$row['warned'];
if($warn=="yes"){
$warn="&nbsp;<img src=images/warn.gif alt=warned title=warned border=0>";
}else{
$warn="";
}
$don=$row['donated'];
if($don>0){
$don="&nbsp;<img src=images/star.gif alt=donor title=donor border=0>";
}else{
$don="";
}
	echo "&#8362; <a href='account-details.php?id=$row[id]'>".$user."".$warn."".$don."</A> \n";
	}
}
}
?>