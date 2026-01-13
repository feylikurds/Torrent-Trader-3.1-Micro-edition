<?php
/**
 * @file
 * @author TorrentTrader <http://www.TorrentTrader.org>
 *
 * Main functions file.
 * @section Modified
 * - $LastChangedDate$
 * - $LastChangedBy$

 */
error_reporting(E_ALL ^ E_NOTICE);

// Prefer unescaped. Data will be escaped as needed.
if (ini_get("magic_quotes_gpc")) {
	$_POST = array_map_recursive("unesc", $_POST);
	$_GET = array_map_recursive("unesc", $_GET);
	$_REQUEST = array_map_recursive("unesc", $_REQUEST);
	$_COOKIE = array_map_recursive("unesc", $_COOKIE);
}

if (function_exists("date_default_timezone_set"))
	//date_default_timezone_set("Europe/London"); // Do NOT change this. All times are converted to user's chosen timezone.
	date_default_timezone_set("UTC"); // Do NOT change this. All times are converted to user's chosen timezone.
	

define("BASEPATH", str_replace("backend", "", dirname(__FILE__)));
$BASEPATH = BASEPATH;
define("BACKEND", dirname(__FILE__));
$BACKEND = BACKEND;

require_once(BACKEND."/mysql.php"); //Get MYSQL Connection Info
require_once(BACKEND."/config.php");  //Get Site Settings and Vars ($site_config)
require(BACKEND."/tzs.php"); // Get Timezones
require_once(BACKEND."/cache.php"); // Caching
require_once(BACKEND."/mail.php"); // Mail functions
require_once(BACKEND."/mysql.class.php");
require_once(BACKEND."/languages.php");
/// each() replacement for php 7+. Change all instances of each() to thisEach() in all TT files. each() deprecated as of 7.2
function thisEach(&$arr) {
    $key = key($arr);
    $result = ($key === null) ? false : [$key, current($arr), 'key' => $key, 'value' => current($arr)];
    next($arr);
    return $result;
}
///end each() replacement

///mysql_result replacement for php 7+. Change all mysql_result to mysqli_result in all TT files. mysql_result deprercated/removed, so emulate it. Should switch to data_seek 
function mysqli_result($res,$row=0,$col=0){
    $numrows = mysqli_num_rows($res);
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}
///end mysql_result replacement	
$GLOBALS['tstart'] = array_sum(explode(" ", microtime()));

//start class colors function
function class_user($name) {
global $site_config;
$a ="SELECT class, donated, warned, enabled FROM `users` WHERE username ='".$name."'";
$res = SQL_Query_exec($a);
$classy = @mysqli_fetch_row($res);
switch ($classy[0]) {
         case 8: // Site Owner
         $col = "".$site_config["siteowner_color"]."";
         break;
         case 7: // Administrator
         $col = "".$site_config["administrator_color"]."";
         break;
         case 6: // Super Moderator
         $col = "".$site_config["super_moderator_color"]."";
         break;
         case 5: // Moderator
         $col = "".$site_config["moderator_color"]."";
         break;
         case 4: // Uploader
         $col = "".$site_config["uploader_color"]."";
         break;
         case 3: // VIP
         $col = "".$site_config["vip_color"]."";
         break;
         case 2: // Power User
         $col = "".$site_config["power_user_color"]."";
         break;
         case 1: // User
         $col = "".$site_config["user_color"]."";

}if($classy[1]>0){
$star="<img src='".$site_config[SITEURL]."/images/star.png' alt='donated' border='0'>";
}else{ $star="";}
if($classy[2]=="yes"){
$warn="<img src='".$site_config[SITEURL]."/images/warned.png' alt='warned' border=0>";
}else{ $warn=""; }
if($classy[3]=="no"){
$disabled="<img src='".$site_config[SITEURL]."/images/disabled.png' alt='disabled' border=0>";
}else{ $disabled=""; }
return unesc("<font color='".$col."'>".$name."".$star."".$warn."".$disabled."</font>");
}
//end class colors function


/**
 * Connect to the database and load user details
 *
 * @param $autoclean
 *   (optional) boolean - Check whether or not to run cleanup (default: false)
 */
function dbconn($autoclean = false) {
	global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $THEME, $LANGUAGE, $LANG, $site_config;
	$THEME = $LANGUAGE = null;

	if (!ob_get_level()) {
		if (extension_loaded('zlib') && !ini_get('zlib.output_compression'))
			ob_start('ob_gzhandler');
		else
			ob_start();
	}

	header("Content-Type: text/html;charset=$site_config[CHARSET]");

	function_exists("mysqli_connect") or die("MySQL support not available.");

	$GLOBALS["DBconnector"] = mysqli_connect($mysql_host, $mysql_user, $mysql_pass) or die('DATABASE: mysqli_connect: ' . mysqli_error($GLOBALS["DBconnector"]));
	mysqli_select_db($GLOBALS["DBconnector"],$mysql_db) or die('DATABASE: mysqli_select_db: ' . mysqli_error($GLOBALS["DBconnector"]));
	unset($mysql_pass); //security

	userlogin(); //Get user info

	//Get language and theme
	$CURUSER = $GLOBALS["CURUSER"];

    $ss_a = mysqli_fetch_assoc(SQL_Query_exec("select uri from stylesheets where id='" . ( $CURUSER ? $CURUSER['stylesheet'] : $site_config['default_theme'] )  . "'"));
	$THEME = $ss_a["uri"];

	$lng_a = mysqli_fetch_assoc(SQL_Query_exec("select uri from languages where id='" .  ( $CURUSER ? $CURUSER['language'] : $site_config['default_language'] )  . "'"));
	$LANGUAGE = $lng_a["uri"];
	
	require_once("languages/$LANGUAGE");

	if ($autoclean)
		autoclean();
}


// Main Cleanup
function autoclean() {
	global $site_config;
    require_once("cleanup.php");

    $now = gmtime();

    $res = SQL_Query_exec("SELECT last_time FROM tasks WHERE task='cleanup'");
    $row = mysqli_fetch_row($res);
    if (!$row) {
        SQL_Query_exec("INSERT INTO tasks (task, last_time) VALUES ('cleanup',$now)");
        return;
    }
    $ts = $row[0];
    if ($ts + $site_config["autoclean_interval"] > $now)
        return;
    SQL_Query_exec("UPDATE tasks SET last_time=$now WHERE task='cleanup' AND last_time = $ts");
    if (!mysqli_affected_rows($GLOBALS["DBconnector"]))
        return;

    do_cleanup();
}

// IP Validation
function validip($ip) {
	if (extension_loaded("filter")) {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}

	if (preg_match('![:a-f0-9]!i', $ip))
		return true;

	if (!empty($ip) && $ip == long2ip(ip2long($ip))) {
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r) {
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
		return false;
}

function getip() {
	return $_SERVER["REMOTE_ADDR"];
}

// cloudflare Server use addon 10-8-2014
// Updated getip with validation
//function getip(){    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];        return $ip;    }    foreach (array(        'HTTP_CLIENT_IP',        'HTTP_X_FORWARDED_FOR',        'HTTP_X_FORWARDED',        'HTTP_X_CLUSTER_CLIENT_IP',        'HTTP_FORWARDED_FOR',        'HTTP_FORWARDED',        'HTTP_CF_CONNECTING_IP',        'REMOTE_ADDR'    ) as $key) {        if (array_key_exists($key, $_SERVER) === true) {            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {                    return $ip;                }            }        }    }}


function userlogin() {
	$ip = getip();
	// If there's no IP a script is being ran from CLI. Any checks here will fail, skip all.
	if ($ip == "") return;

	GLOBAL $CURUSER;
	unset($GLOBALS["CURUSER"]);

	//Check IP bans
	if (is_ipv6($ip))
		$nip = ip2long6($ip);
	else
		$nip = ip2long($ip);
	$res = SQL_Query_exec("SELECT * FROM bans");
	while ($row = mysqli_fetch_assoc($res)) {
		$banned = false;
		if (is_ipv6($row["first"]) && is_ipv6($row["last"]) && is_ipv6($ip)) {
			$row["first"] = ip2long6($row["first"]);
			$row["last"] = ip2long6($row["last"]);
			$banned = bccomp($row["first"], $nip) != -1 && bccomp($row["last"], $nip) != -1;
		} else {
			$row["first"] = ip2long($row["first"]);
			$row["last"] = ip2long($row["last"]);
			$banned = $nip >= $row["first"] && $nip <= $row["last"];
		}
		if ($banned) {
			header("HTTP/1.0 403 Forbidden");
			echo "<html><head><title>Forbidden</title></head><body><h1>Forbidden</h1>Unauthorized IP address.<br />".
			"Reason for banning: $row[comment]</body></html>";
			die;
		}
	}

	//Check The Cookie and get CURUSER details
	if (strlen($_COOKIE["pass"]) != 40 || !is_numeric($_COOKIE["uid"])) {
		logoutcookie();
		return;
	}

	//Get User Details And Permissions
	$res = SQL_Query_exec("SELECT * FROM users INNER JOIN groups ON users.class=groups.group_id WHERE id=$_COOKIE[uid] AND users.enabled='yes' AND users.status = 'confirmed'");
	$row = mysqli_fetch_assoc($res);

	if (!$row || sha1($row["id"].$row["secret"].$row["password"].$ip.$row["secret"]) != $_COOKIE["pass"]) {
		logoutcookie();
		return;
	}




// OS detection
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$os = "";
$os_array = array(
'/windows nt 10.0/i' => 'Windows 10',
'/windows nt 6.3/i' => 'Windows 81',
'/windows nt 6.2/i' => 'Windows 8',
'/windows nt 6.1/i' => 'Windows 7',
'/windows nt 6.0/i' => 'Windows Vista',
'/windows nt 5.2/i' => 'Windows Server 2003',
'/windows nt 5.1/i' => 'Windows XP',
'/windows xp/i' => 'Windows XP',
'/windows nt 5.0/i' => 'Windows 2000',
'/windows me/i' => 'Windows ME',
'/win98/i' => 'Windows 98',
'/win95/i' => 'Windows 95',
'/win16/i' => 'Windows 3.11',
'/macintosh|mac os x/i' => 'Mac OS X',
'/mac_powerpc/i' => 'Mac OS 9',
'/linux/i' => 'Linux',
'/ubuntu/i' => 'Ubuntu',
'/iPhone/i' => 'iPhone',
'/iPod/i' => 'iPod',
'/iPad/i' => 'iPad',
'/android/i' => 'Android',
'/blackberry/i' => 'BlackBerry',
'/webos/i' => 'Mobile',
'/armv6l/i' => 'Raspberry',
);
foreach ($os_array as $regex => $value) {
if (preg_match($regex, $user_agent)) {
$os = $value;
}
}
//Browser detection
$where = where ($_SERVER["SCRIPT_FILENAME"], $row["id"], 0);
if(preg_match("/Opera/u", getenv("HTTP_USER_AGENT"))) $browser = "3";
else if(preg_match("/Edge/u", getenv("HTTP_USER_AGENT"))) $browser = "10";
else if(preg_match("/Chrome/u", getenv("HTTP_USER_AGENT"))) $browser = "7";
else if(preg_match("/Safari/u", getenv("HTTP_USER_AGENT"))) $browser = "2";
else if(preg_match("/Maxthon/u", getenv("HTTP_USER_AGENT"))) $browser = "4";
else if(preg_match("/Konqueror/u", getenv("HTTP_USER_AGENT"))) $browser = "5";
else if(preg_match("/Firefox/u", getenv("HTTP_USER_AGENT"))) $browser = "1";
else if(preg_match("/rv:11.0/u", getenv("HTTP_USER_AGENT"))) $browser = "6";
else if((preg_match("/Nav/u", getenv("HTTP_USER_AGENT"))) || (preg_match("/Gold/u", getenv("HTTP_USER_AGENT"))) || (preg_match("/X11/u", getenv("HTTP_USER_AGENT"))) || (preg_match("/Mozilla/u", getenv("HTTP_USER_AGENT"))) || (preg_match("/Netscape/u", getenv("HTTP_USER_AGENT"))) ) $browser = "8";
else $browser = "9";
//SQL_Query_exec("UPDATE users SET browser=".sqlesc($browser)." WHERE id=" . $row["id"]);
SQL_Query_exec("UPDATE users SET last_access='" . get_date_time() . "', ip=".sqlesc($ip).", browser=".sqlesc($browser).", os=".sqlesc($os)." WHERE id=" . $row["id"]);

	$GLOBALS["CURUSER"] = $row;
	unset($row);
}

function logincookie($id, $password, $secret, $updatedb = 1, $expires = 0x7fffffff) {
	$hash = sha1($id.$secret.$password.getip().$secret);
	setcookie("pass", $hash, $expires, "/");
	setcookie("uid", $id, $expires, "/");

	if ($updatedb)
		SQL_Query_exec("UPDATE users SET last_login = '".get_date_time()."' WHERE id = $id");
}

function logoutcookie() {
	setcookie("pass", null, time(), "/");
	setcookie("uid", null, time(), "/");
}

function stdhead($title = "") {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;  //Define globals
 
	//site online check
	if (!$site_config["SITE_ONLINE"]){
		if ($CURUSER["control_panel"] != "yes") {
//			echo '<br /><br /><br /><center>'. stripslashes($site_config["OFFLINEMSG"]) .'</center><br /><br />';
			echo file_get_contents('siteclosed.php');
			die;
		}else{
			echo '<br /><br /><br /><center><b><font color="#ff0000">SITE OFFLINE, STAFF ONLY VIEWING! DO NOT LOGOUT</font></b><br />If you logout please edit backend/config.php and set SITE_ONLINE to true </center><br /><br />';
		}
	}
	//end check

if ($CURUSER){
                where($title, $CURUSER['id']);
                }else{
                guestadd();
                }

    if ($title == "")
        $title = $site_config['SITENAME'];
    else
        $title = $site_config['SITENAME']. " : ". htmlspecialchars($title);

	require_once("themes/" . $THEME . "/block.php");
	require_once("themes/" . $THEME . "/header.php");
}

function stdfoot() {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;
	require_once("themes/" . $THEME . "/footer.php");
	mysqli_close($GLOBALS["DBconnector"]);
}

function leftblocks() {
    global $site_config, $CURUSER, $THEME, $LANGUAGE, $TTCache, $blockfilename;  //Define globals

    if (($blocks=$TTCache->get("blocks_left", 900)) === false) {
        $res = SQL_Query_exec("SELECT * FROM blocks WHERE position='left' AND enabled=1 ORDER BY sort");
        $blocks = array();
        while ($result = mysqli_fetch_assoc($res)) {
                $blocks[] = $result["name"];
        }
        $TTCache->Set("blocks_left", $blocks, 900);
    }

    foreach ($blocks as $blockfilename){
        include("blocks/".$blockfilename."_block.php");
    }
}

function rightblocks() {
    global $site_config, $CURUSER, $THEME, $LANGUAGE, $TTCache, $blockfilename;  //Define globals

    if (($blocks=$TTCache->get("blocks_right", 900)) === false) {
        $res = SQL_Query_exec("SELECT * FROM blocks WHERE position='right' AND enabled=1 ORDER BY sort");
        $blocks = array();
        while ($result = mysqli_fetch_assoc($res)) {
                $blocks[] = $result["name"];
        }
        $TTCache->Set("blocks_right", $blocks, 900);
    }

    foreach ($blocks as $blockfilename){
        include("blocks/".$blockfilename."_block.php");
    }
}

function middleblocks() {
    global $site_config, $CURUSER, $THEME, $LANGUAGE, $TTCache;  //Define globals

    if (($blocks=$TTCache->get("blocks_middle", 900)) === false) {
        $res = SQL_Query_exec("SELECT * FROM blocks WHERE position='middle' AND enabled=1 ORDER BY sort");
        $blocks = array();
        while ($result = mysqli_fetch_assoc($res)) {
                $blocks[] = $result["name"];
        }
        $TTCache->Set("blocks_middle", $blocks, 900);
    }

    foreach ($blocks as $blockfilename){
        include("blocks/".$blockfilename."_block.php");
    }
}

function show_error_msg($title, $message, $wrapper = "1") {
	if ($wrapper) {
        ob_start();
        ob_clean();
		stdhead($title);
	}
	begin_frame("<font class='error'>". htmlspecialchars($title) ."</font>");
	print("<center><b>" . $message . "</b></center>\n");
	end_frame();

	if ($wrapper){
		stdfoot();
		die();
	}
}

function health($leechers, $seeders) {
	if (($leechers == 0 && $seeders == 0) || ($leechers > 0 && $seeders == 0))
		return 0;
	elseif ($seeders > $leechers)
		return 10;

	$ratio = $seeders / $leechers * 100;
	if ($ratio > 0 && $ratio < 15)
		return 1;
	elseif ($ratio >= 15 && $ratio < 25)
		return 2;
	elseif ($ratio >= 25 && $ratio < 35)
		return 3;
	elseif ($ratio >= 35 && $ratio < 45)
		return 4;
	elseif ($ratio >= 45 && $ratio < 55)
		return 5;
	elseif ($ratio >= 55 && $ratio < 65)
		return 6;
	elseif ($ratio >= 65 && $ratio < 75)
		return 7;
	elseif ($ratio >= 75 && $ratio < 85)
		return 8;
	elseif ($ratio >= 85 && $ratio < 95)
		return 9;
	else
		return 10;
}


// MySQL escaping
function sqlesc($x) {
   if (!is_numeric($x)) {
       $x = "'".mysqli_real_escape_string($GLOBALS["DBconnector"],$x)."'";
   }
   return $x;
}


function unesc($x) {

		return stripslashes($x);
	return $x;
}

/**
 * Convert bytes to readable format
 *
 * @param $s
 *   integer: bytes
 * @param $precision
 *   (optional) integer: decimal precision (default: 2)
 * @return
 *   string: formatted size
 */
function mksize ($s, $precision = 2) {
	$suf = array("B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");

	for ($i = 1, $x = 0; $i <= count($suf); $i++, $x++) {
		if ($s < pow(1024, $i) || $i == count($suf)) // Change 1024 to 1000 if you want 0.98GB instead of 1,0000MB
			return number_format($s/pow(1024, $x), $precision)." ".$suf[$x];
	}
}

function escape_url($url) {
	$ret = '';
	for($i = 0; $i < strlen($url); $i+=2)
	$ret .= '%'.$url[$i].$url[$i + 1];
	return $ret;
}

/**
 * Scrape torrent and return stats
 *
 * @param $scrape
 *   string: Scrape URL
 * @param $hash
 *   string: SHA1 hash (info_hash) of torrent
 * @return
 *   array:
 *     All -1 if failed
 *     - seeds: integer - number of seeders
 *     - leechers: integer - number of leechers
 *     - downloaded: integer - number of complete downloads
 *
 */
function torrent_scrape_url($scrape, $hash) {
	if (function_exists("curl_exec")) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt ($ch, CURLOPT_URL, $scrape.'?info_hash='.escape_url($hash));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$fp = curl_exec($ch);
		curl_close($ch);
	} else {
		ini_set('default_socket_timeout',10); 
		$fp = @file_get_contents($scrape.'?info_hash='.escape_url($hash));
	}
	$ret = array();
	if ($fp) {
		$stats = BDecode($fp);
		$binhash = pack("H*", $hash);
        $binhash = addslashes($binhash);
		$seeds = $stats['files'][$binhash]['complete'];
		$peers = $stats['files'][$binhash]['incomplete'];
		$downloaded = $stats['files'][$binhash]['downloaded'];
		$ret['seeds'] = $seeds;
		$ret['peers'] = $peers;
		$ret['downloaded'] = $downloaded;
	}

	if ($ret['seeds'] === null) {
		$ret['seeds'] = -1;
		$ret['peers'] = -1;
		$ret['downloaded'] = -1;
	}

	return $ret;
}

function mkprettytime($s) {
	if ($s < 0)
		$s = 0;

	$t = array();
	$t["day"] = floor($s / 86400);
	$s -= $t["day"] * 86400;

	$t["hour"] = floor($s / 3600);
	$s -= $t["hour"] * 3600;

	$t["min"] = floor($s / 60);
	$s -= $t["min"] * 60;

	$t["sec"] = $s;

	if ($t["day"])
		return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
	if ($t["hour"])
		return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
        return sprintf("%d:%02d", $t["min"], $t["sec"]);
}

function gmtime() {
	return sql_timestamp_to_unix_timestamp(get_date_time());
}

function loggedinonly() {
	global $CURUSER;
	if (!$CURUSER) {
		header("Refresh: 0; url=account-login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
		exit();
	}
}

function validfilename($name) {
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email) {
	if (function_exists("filter_var"))
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	return preg_match('/^([a-z0-9._-](\+[a-z0-9])*)+@[a-z0-9.-]+\.[a-z]{2,6}$/i', $email);
}

function mksecret($len = 20) {
	$chars = array_merge(range(0, 9), range("A", "Z"), range("a", "z"));
	shuffle($chars);
	$x = count($chars) - 1;
	for ($i = 1; $i <= $len; $i++)
		$str .= $chars[mt_rand(0, $x)];
	return $str;
}

function deletetorrent($id) {
	global $site_config;

	$row = @mysqli_fetch_assoc(@SQL_Query_exec("SELECT image1,image2 FROM torrents WHERE id=$id"));

	foreach(explode(".","peers.comments.ratings.files.announce") as $x)
		SQL_Query_exec("DELETE FROM $x WHERE torrent = $id");

	SQL_Query_exec("DELETE FROM completed WHERE torrentid = $id");

    if (file_exists($site_config["torrent_dir"] . "/$id.torrent"))
        unlink($site_config["torrent_dir"] . "/$id.torrent");
    
    if ($row["image1"]) {                             
        unlink($site_config['torrent_dir'] . "/images/" . $row["image1"]);
    }
    
    if ($row["image2"]) {
        unlink($site_config['torrent_dir'] . "/images/" . $row["image2"]);
    }
	 $path = $site_config["torrent_dir"]."/imdb/$id.jpg";
if ( is_readable( $path ) ){
     unlink( $path );
}

	@unlink($site_config["nfo_dir"]."/$id.nfo");

	SQL_Query_exec("DELETE FROM torrents WHERE id = $id");
    SQL_Query_exec("DELETE FROM reports WHERE votedfor = $id AND type = 'torrent'");
	SQL_Query_exec("DELETE FROM `snatched` WHERE `tid` = '$id'");
	

}

function deleteaccount($userid) 
{
	SQL_Query_exec("DELETE FROM users WHERE id = $userid");
	SQL_Query_exec("DELETE FROM warnings WHERE userid = $userid");
	SQL_Query_exec("DELETE FROM ratings WHERE user = $userid");
	SQL_Query_exec("DELETE FROM peers WHERE userid = $userid");
    SQL_Query_exec("DELETE FROM completed WHERE userid = $userid"); 
    SQL_Query_exec("DELETE FROM reports WHERE addedby = $userid");
    SQL_Query_exec("DELETE FROM reports WHERE votedfor = $userid AND type = 'user'");
    SQL_Query_exec("DELETE FROM forum_readposts WHERE userid = $userid");
    SQL_Query_exec("DELETE FROM pollanswers WHERE userid = $userid");
	SQL_Query_exec("DELETE FROM `snatched` WHERE `uid` = '$userid'");
}

function genrelist() {
    $ret = array();
    $res = SQL_Query_exec("SELECT id, name, parent_cat FROM categories ORDER BY parent_cat ASC, sort_index ASC");
    while ($row = mysqli_fetch_assoc($res))
        $ret[] = $row;
    return $ret;
}

function langlist() {
    $ret = array();
    $res = SQL_Query_exec("SELECT id, name, image FROM torrentlang ORDER BY sort_index, id");
    while ($row = mysqli_fetch_assoc($res))
        $ret[] = $row;
    return $ret;
}

function is_valid_id($id){
	return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

function is_valid_int($id){
	return is_numeric($id) && (floor($id) == $id);
}

function sql_timestamp_to_unix_timestamp($s){
	return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

function write_log($text){
	$text = sqlesc($text);
	$added = sqlesc(get_date_time());
	SQL_Query_exec("INSERT INTO log (added, txt) VALUES($added, $text)");
}

function get_elapsed_time($ts){
  $mins = floor((gmtime() - $ts) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  $days = floor($hours / 24);
  $hours -= $days * 24;
  $weeks = floor($days / 7);
  $days -= $weeks * 7;
  $t = "";
  if ($weeks > 0)
      return "$weeks ".T_("WK")."" . ($weeks > 1 ? "".T_("WKS")."" : "".T_("WKS2")."");
if ($days > 0)
      return "$days ".T_("DAY")."" . ($days > 1 ? "".T_("DAYS2")."" : "");
if ($hours > 0)
      return "$hours ".T_("HOUR")."" . ($hours > 1 ? "".T_("HOURS")."" : "".T_("HOURS2")."");
if ($mins > 0)
      return "$mins ".T_("MIN")."" . ($mins > 1 ? "".T_("MINS")."" : "".T_("MINS2")."");
      return "".T_("<1MIN")."";
}

function micro_hex2bin($hexdata) {
	$bindata = "";
	for ($i=0;$i<strlen($hexdata);$i+=2) {
		$bindata.=chr(hexdec(substr($hexdata,$i,2)));
	}
	return $bindata;
}

function guestadd() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = gmtime();
    SQL_Query_exec("INSERT INTO `guests` (`ip`, `time`) VALUES ('$ip', '$time') ON DUPLICATE KEY UPDATE `time` = '$time'");
}
                                
function getguests() {
    $past = (gmtime() - 2400);
    SQL_Query_exec("DELETE FROM `guests` WHERE `time` < $past");
    return get_row_count("guests");
}

function time_ago($addtime) {
   $addtime = get_elapsed_time(sql_timestamp_to_unix_timestamp($addtime));
   return $addtime;
}

function CutName ($vTxt, $Car) {
	if (strlen($vTxt) > $Car) {
		return substr($vTxt, 0, $Car) . "...";
	}
	return $vTxt;
}

function searchfield($s) {
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function get_row_count($table, $suffix = "") {
    $res = SQL_Query_exec("SELECT COUNT(*) FROM $table $suffix");
    $row = mysqli_fetch_row($res);
    return $row[0];
}

function get_date_time($timestamp = 0){
	if ($timestamp)
		return date("Y-m-d H:i:s", $timestamp);
	else
		return gmdate("Y-m-d H:i:s");
}

// Convert UTC to user's timezone
function utc_to_tz ($timestamp=0) {
	GLOBAL $CURUSER, $tzs;

	if (method_exists("DateTime", "setTimezone")) {
		if (!$timestamp)
			$timestamp = get_date_time();
		$date = new DateTime($timestamp, new DateTimeZone("UTC"));

		$date->setTimezone(new DateTimeZone($CURUSER ? $tzs[$CURUSER["tzoffset"]][1] : "Europe/London"));
		return $date->format('Y-m-d H:i:s');
	}
	if (!is_numeric($timestamp))
		$timestamp = sql_timestamp_to_unix_timestamp($timestamp);
	if ($timestamp == 0)
		$timestamp = gmtime();

	$timestamp = $timestamp + ($CURUSER['tzoffset']*60);
	if (date("I")) $timestamp += 3600; // DST Fix
	return date("Y-m-d H:i:s", $timestamp);
//	return date("F d, Y g:i:s a", $timestamp);
}


function utc_to_tz_time ($timestamp=0) {
    GLOBAL $CURUSER, $tzs;

    if (method_exists("DateTime", "setTimezone")) {
        if (!$timestamp)
            $timestamp = get_date_time();
        $date = new DateTime($timestamp, new DateTimeZone("UTC"));

        $date->setTimezone(new DateTimeZone($CURUSER ? $tzs[$CURUSER["tzoffset"]][1] : "Europe/London"));
        return sql_timestamp_to_unix_timestamp($date->format('Y-m-d H:i:s'));
    }
    if (!is_numeric($timestamp))
        $timestamp = sql_timestamp_to_unix_timestamp($timestamp);
    if ($timestamp == 0)
        $timestamp = gmtime();

    $timestamp = $timestamp + ($CURUSER['tzoffset']*60);
    if (date("I")) $timestamp += 3600; // DST Fix
    return date("Y-m-d H:i:s", $timestamp);
//    return date("F d, Y g:i:s a", $timestamp);
}

function encodehtml($s, $linebreaks = true) {
	  $s = str_replace("<", "&lt;", str_replace("&", "&amp;", $s));
	  if ($linebreaks)
		$s = nl2br($s);
	  return $s;
}


function format_urls($s){
        return preg_replace(
        "/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i",
        "\\1<a href='\\2' target='_blank'>\\2</a>", $s);
}

    ###########################
       #################### - REQUEST COMMENT START - ####################
                                                    ###########################
       
         
        function reqcommenttable ($rows) {
        global $site_config, $CURUSER, $THEME, $LANGUAGE;
                $count = 0;
                foreach ($rows as $row) {
         
        if (isset($row["username"])) {
                $username = $row["username"];
                       
        $ratres = SQL_Query_exec("SELECT uploaded, downloaded, signature, privacy from users where username='$username'");
        $rat = mysqli_fetch_array($ratres);
                $privacylevel = $rat["privacy"];
         
         
        if ($rat["downloaded"] > 0) {
                $ratio = $rat['uploaded'] / $rat['downloaded'];
                $ratio = number_format($ratio, 3);
                       
                $color = get_ratio_color($ratio);
        if ($color)
                $ratio = "<ratiobackground><font color=$color><b>$ratio</b></font></ratiobackground>";
        }
                        elseif ($rat["uploaded"] > 0)
                $ratio = "Inf.";
                        else
                $ratio = "---";
                       
                $title = (isset($row["level"]));
        if ($title == "")
                $title = get_user_class_name($row["class"]);
                else
                $title = htmlspecialchars($title);
               
                $usersignature = stripslashes(format_comment($rat["signature"]));
                $userdownloaded = mksize($rat["downloaded"]);
                $useruploaded = mksize($rat["uploaded"]);
               
        if ($site_config["REQ_CLASS_USER"]) {
                $reqstrong = "<tr class='p-title'><th align='center'><a name=comm". $row["id"] . " href='account-details.php?id=" . $row["user"] . "'>" . class_user($row["username"]) . "</a>" . ((isset($row["donor"])) == "0" ? "<img src=images/star.png alt='Donor'>" : "") . ($row["warned"] == "yes" ? "<img src=images/warned.png alt=\"Warned\">" : "") . "</th><th align='right'>\n";
        }else{
                $reqstrong = "<tr class='p-title'><th align='center'><a name=comm". $row["id"] . " href='account-details.php?id=" . $row["user"] . "'>" . $row["username"] . "</a>" . ((isset($row["donor"])) == "0" ? "<img src='images/star.png' alt='Donor'>" : "") . ($row["warned"] == "yes" ? "<img src='images/warned.png' alt=\"Warned\">" : "") . "</th><th align='right'>\n";
        }
                $profilepm = "<a href=\"account-details.php?id=$row[user]\"><img src=\"themes/$THEME/forums/icon_profile.png\" border=\"0\" alt=\"[Profile]\" title=\"Profile\" /></a> <a href=\"mailbox.php?compose&amp;id=$row[user]\"><img src=\"themes/$THEME/forums/icon_pm.png\" border=\"0\" alt=\"[PM]\" title=\"PM\" /></a>";
         
        if ($privacylevel == "strong") {
        if (get_user_class($CURUSER) <= 5 ){
                $row['username'] = "".T_("ANONYMOUS")."";     
                $userdownloaded = "<img border=\"0\" src=\"images/requests/maskred.png\" width=\"12\" height=\"\" title=\"?\"\">";
                $useruploaded = "<img border=\"0\" src=\"images/requests/maskgreen.png\" width=\"12\" height=\"\" title=\"?\"\">";
                $ratio = "<img border=\"0\" src=\"images/requests/maskpurple.png\" width=\"12\" height=\"\" title=\"?\"\">";
                $title = "";
                $arr2['username'] = "".T_("ANONYMOUS")."";
                $profilepm = "<img src=\"themes/$THEME/forums/icon_profile.png\" border=\"0\" alt=\"[Anonymous]\" title=\"Anonymous\" /> <img src=\"themes/$THEME/forums/icon_pm.png\" border=\"0\" alt=\"[Anonymous]\" title=\"Anonymous\" />";
                $reqstrong = "<tr class='p-title'><th align='center'>" . $row["username"] . "" . ((isset($row["donor"])) == "0" ? "<img src='images/star.gif' alt='Donor'>" : "") . ($row["warned"] == "yes" ? "<img src='images/warned.png' alt=\"Warned\">" : "") . "</th><th align='right'>\n";
        }     
        }     
                        print("<center><div class='f-post f-border'><table class='table_table' width='100%' border='0' cellspacing='0' cellpadding='3'>");
                        print("$reqstrong");                                         
        }
                        else
                        print("<a name=\"comm" . $row["id"] . "\"><i>(orphaned)</i></a>\n");
                        print("<div style='float: left;'>".T_("POSTED_AT")." - ".$row["added"]."</div> #" . $row["id"] . "  </th></tr>\n");
                                $buttons = "" .($row["user"] == $CURUSER["id"] || get_user_class() >= 5 ? " <a href=reqcomment.php?action=edit&amp;cid=$row[id]><img style=\"padding-left: 3px;\" src=images/requests/edit1.png width='14' title=".T_("EDIT")." alt=".T_("EDIT")."></a>" : "") .
                                (get_user_class() >= 5 ? " <a href=reqcomment.php?action=delete&amp;cid=$row[id]><img style=\"padding-left: 3px;\" src=images/requests/delete1.png width='14' title=".T_("_DEL_")." alt=".T_("_DEL_")."></a>" : "") .
                                ($row["editedby"] && get_user_class() >= 5 ? " <a href=reqcomment.php?action=vieworiginal&amp;cid=$row[id]><img style=\"padding-left: 3px;\" src=images/requests/original1.png width='14' title='".T_("VIEW_ORIGINAL")."' alt='".T_("VIEW_ORIGINAL")."'></a>" : "");
                               
                       
                       
                $avatar = htmlspecialchars($row["avatar"]);
        if (!$avatar) {
                $avatar = "images/default_avatar.png";
        }
         
        if  ($privacylevel == "strong" && $CURUSER["control_panel"] != "yes"){
                $avatar = $site_config['SITEURL']."/images/requests/default_avatar.jpg";     
        }     
               
               
                $text = format_comment($row["text"]);
                $rectext = format_comment($row["editedby"]);
                if (is_valid_id($row["editedby"])){
                $res2 = SQL_Query_exec("SELECT username, privacy FROM users WHERE id=$row[editedby]");
        if (mysqli_num_rows($res2) == 1) {
                $arr2 = mysqli_fetch_assoc($res2);
         
        if ($site_config["REQ_CLASS_USER"]) {
                $rectext = "<p><font size=1 class=small>".T_("EDITED_BY")." <a href='account-details.php?id=$arr2[editedby]'><b>".class_user($arr2[username])."</b></a> ".date("Y-m-d \\".T_("A")."\\".T_("T")."\\:H:i:s", utc_to_tz_time((isset($arr['editedat']))))."</font></p>\n";               
        }else{ 
                $rectext = "<p><font size=1 class=small>".T_("EDITED_BY")." <a href='account-details.php?id=$row[editedby]'>$arr2[username]</a> ".date("Y-m-d \\".T_("A")."\\".T_("T")."\\:H:i:s", utc_to_tz_time((isset($arr['editedat']))))."</font></p>\n";                                                                       
        }
         
                $privacylevel = $arr2["privacy"]; 
        if (get_user_class($CURUSER) <= 5 ){
        if ($privacylevel == "strong"){
                $arr2['username'] = "".T_("ANONYMOUS")."";
                $rectext = "<p><font size=1 class=small>".T_("EDITED_BY")." $arr2[username] ".date("Y-m-d \\".T_("A")."\\".T_("T")."\\:H:i:s", utc_to_tz_time((isset($arr['editedat']))))."</font></p>\n";
               
        }
        } 
        $text .= "<br /><br />\n";
        }
        }   
                       
                    print("<tr  class='p-foot' valign='top'>\n");
                        print("<td class='table_col1' width='150px' style='padding: 5px'><center><img width='100px' height='' src='$avatar'></center>
                               <br /><center><b>$title</b></center><br />
                               <img border=\"0\" src=\"images/requests/red_down.png\" width=\"10\" height=\"\" alt=\"".T_("DOWNLOADED")."\" title=\"".T_("DOWNLOADED")."\"></a>: <font color=\"red\">$userdownloaded</font><br />
                               <hr /><img border=\"0\" src=\"images/requests/green_up.png\" width=\"10\" height=\"\" alt=\"".T_("UPLOADED")."\" title=\"".T_("UPLOADED")."\"></a>: <font color=\"green\">$useruploaded</font><br />
                               <hr /><img border=\"0\" src=\"images/requests/red_down.png\" width=\"10\" height=\"\" alt=\"".T_("RATIO")."\" title=\"".T_("RATIO")."\"><img border=\"0\" src=\"images/requests/green_up.png\" width=\"10\" height=\"\" alt=\"".T_("RATIO")."\" title=\"".T_("RATIO")."\">: $ratio<br /><hr /><br /></td>
                               <td  class='table_col1'><div class='container outer'><div class='inner'>$text </div><hr /><br /><div class='f-sig' align='center'>$usersignature</div><br /></div></td></tr>
                               <tr class='p-foot'><td align='center'>$profilepm</td><td align='right'><div style='float: left;'>$rectext</div><div style='padding-right: 10px'>$buttons</div></td>
                               </tr>\n");
                    print("</tr>\n");
                    print("</table></div>");
                    print("<br />");
        }
        }
         
        if (!function_exists("get_ratio_color")) {
        function get_ratio_color ($ratio) {
                  if ($ratio < 0.1) return "#ff0000";
                  if ($ratio < 0.2) return "#ee0000";
                  if ($ratio < 0.3) return "#dd0000";
                  if ($ratio < 0.4) return "#cc0000";
                  if ($ratio < 0.5) return "#bb0000";
                  if ($ratio < 0.6) return "#aa0000";
                  if ($ratio < 0.7) return "#990000";
                  if ($ratio < 0.8) return "#880000";
                  if ($ratio < 0.9) return "#770000";
                  if ($ratio < 1) return "#660000";
                  return "#000000";
        }
        }
         
                                                    #########################
       #################### - REQUEST COMMENT END - ####################
                                                    #########################

function format_comment($text) {
	global $site_config, $smilies;

	$s = $text;

	$s = htmlspecialchars($s);
	$s = format_urls($s);

	// [*]
	$s = preg_replace("/\[\*\]/", "<li>", $s);

	// [b]Bold[/b]
	$s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", "<b>\\1</b>", $s);

	// [i]Italic[/i]
	$s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", "<i>\\1</i>", $s);

	// [u]Underline[/u]
	$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", "<u>\\1</u>", $s);


	// [u]Underline[/u]
	//$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/i", "<u>\\1</u>", $s);

    // [img]http://www/image.gif[/img]
            $s = preg_replace("/\[img\]((http|https):\/\/[^\s'\"<>]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\[\/img\]/i", "<img border='0' src=\"\\1\" width='190px' alt='' />", $s);
     
            // [img=http://www/image.gif]
            $s = preg_replace("/\[img=((http|https):\/\/[^\s'\"<>]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\]/i", "<img border='0' src=\"\\1\" width='190px' alt='' />", $s);

	// [color=blue]Text[/color]
	$s = preg_replace(
		"/\[color=([a-zA-Z]+)\]((\s|.)+?)\[\/color\]/i",
		"<font color='\\1'>\\2</font>", $s);

	// [color=#ffcc99]Text[/color]
	$s = preg_replace(
		"/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]((\s|.)+?)\[\/color\]/i",
		"<font color='\\1'>\\2</font>", $s);

        // [url=http://www.example.com]Text[/url]
        $s = preg_replace(
                "/\[url=((http|ftp|https|ftps|irc):\/\/[^<>\s]+?)\]((\s|.)+?)\[\/url\]/i",
                "<a href='\\1' target='_blank'>\\3</a>", $s);

        // [url]http://www.example.com[/url]
        $s = preg_replace(
                "/\[url\]((http|ftp|https|ftps|irc):\/\/[^<>\s]+?)\[\/url\]/i",
                "<a href='\\1' target='_blank'>\\1</a>", $s);
	// [size=4]Text[/size]
	$s = preg_replace(
		"/\[size=([1-7])\]((\s|.)+?)\[\/size\]/i",
		"<font size='\\1'>\\2</font>", $s);

	// [font=Arial]Text[/font]
	$s = preg_replace(
		"/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i",
		"<font face=\"\\1\">\\2</font>", $s);

	//[quote]Text[/quote]
	while (preg_match("/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", $s))
	$s = preg_replace(
		"/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
		"<p class='sub'><b>Quote:</b></p><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>\\1</td></tr></table><br />", $s);

	//[quote=Author]Text[/quote]
	while (preg_match("/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", $s))
	$s = preg_replace(
		"/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
		"<p class='sub'><b>\\1 wrote:</b></p><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>\\2</td></tr></table><br />", $s);

	// [spoiler]Text[/spoiler]
	$r = substr(md5($text), 0, 4);
	$i = 0;
	while (preg_match("/\[spoiler\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i", $s)) {
		$s = preg_replace("/\[spoiler\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i",
		"<br /><img src='images/plus.gif' id='pic$r$i' title='Spoiler' onclick='klappe_torrent(\"$r$i\")' alt='' /><div id='k$r$i' style='display: none;'>\\1<br /></div>", $s);
		$i++;
	}

	// [spoiler=Heading]Text[/spoiler]
	while (preg_match("/\[spoiler=(.+?)\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i", $s)) {
		$s = preg_replace("/\[spoiler=(.+?)\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i",
		"<br /><img src='images/plus.gif' id='pic$r$i' title='Spoiler' onclick='klappe_torrent(\"$r$i\")' alt='' /><b>\\1</b><div id='k$r$i' style='display: none;'>\\2<br /></div>", $s);
		$i++;
	}

     //[hr]
        $s = preg_replace("/\[hr\]/i", "<hr />", $s);

     //[hr=#ffffff] [hr=red]
        $s = preg_replace("/\[hr=((#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])|([a-zA-z]+))\]/i", "<hr color=\"\\1\"/>", $s);

        //[swf]http://somesite.com/test.swf[/swf]
        $s = preg_replace("/\[swf\]((www.|http:\/\/|https:\/\/)[^\s]+(\.swf))\[\/swf\]/i",
        "<param name='movie' value='\\1'/><embed width='470' height='310' src='\\1'></embed>", $s);

        //[swf=http://somesite.com/test.swf]
        $s = preg_replace("/\[swf=((www.|http:\/\/|https:\/\/)[^\s]+(\.swf))\]/i",
        "<param name='movie' value='\\1'/><embed width='470' height='310' src='\\1'></embed>", $s);
	
	//[hide]Text[/hide] via Tiloup @support-tt-france.fr
    while (preg_match("/\[hide\]\s*((\s|.)+?)\s*\[\/hide\]\s*/i", $s))
        $s = preg_replace(
        "/\[hide\]\s*((\s|.)+?)\s*\[\/hide\]\s*/i",
        "<div style='margin:20px; margin-top:5px'><div><input type='button' value='View' style='text-align:center;width:100px;margin:0px;padding:0px;' onclick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerText = ''; this.value = 'Close'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = 'View'; }\" /></div><br/><div><div style='display: none;'>\\1</div></div></div>",$s);
	
//[video=https://www.youtube.com]
          $s = preg_replace(
        "/\[video=\bhttps?:\/\/www\.youtube\.com\/watch\?v=([\w\d-]+?)\]/i",
        "<iframe width="
        . "\"420\" "
        . "height=\"315\""
        . "src=\"https://www.youtube.com/embed/" . "\\1\""
        . "frameborder=\"0\""
        . "allowfullscreen>"
        . "</iframe>", $s);
	
//alternate option for youtube
//[video=https://www.youtube.com]
//        $s = preg_replace(
//      "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
//      "<iframe width=\"420\" height=\"315\" src=\"//www.youtube.com/embed/$2\" allowfullscreen></iframe>",
//      $s);
	
//[video]https://www.youtube.com[/video]
          $s = preg_replace(
        "/\[video\]\bhttps?:\/\/www\.youtube\.com\/watch\?v=([\w\d-]+?)\[\/video\]/i",
        "<iframe width="
        . "\"420\" "
        . "height=\"315\""
        . "src=\"https://www.youtube.com/embed/" . "\\1\""
        . "frameborder=\"0\""
        . "allowfullscreen>"
        . "</iframe>", $s);

	// Linebreaks
	$s = nl2br($s);

	// Maintain spacing
	$s = str_replace("  ", " &nbsp;", $s);

	// Smilies
	require_once("smilies.php");
	reset($smilies);
	while (list($code, $url) = thisEach($smilies))
        $s = str_replace($code, '<img border="0" src="'.$site_config["SITEURL"].'/images/smilies/'.$url.'" alt="'.$code.'" title="'.$code.'" />', $s);

	if($site_config["OLD_CENSOR"])
	{
    $r = SQL_Query_exec("SELECT * FROM censor");
	while($rr=mysqli_fetch_row($r))
	$s = preg_replace("/".preg_quote($rr[0])."/i", $rr[1], $s);
    }
	else
	{
	
    $f = @fopen("censor.txt","r");
    
    if ($f && filesize("censor.txt") != 0) {
    
       $bw = fread($f, filesize("censor.txt"));
       $badwords = explode("\n",$bw);
       
       for ($i=0; $i<count($badwords); ++$i)
           $badwords[$i] = trim($badwords[$i]);
       $s = str_replace($badwords, "<img src='images/censored.png' border='0' alt='Censored' title='Censored' />", $s);
    }
    @fclose($f);
	}
	/////////to clear the ajax shoutbox////////////////
    global $CURUSER;
            $is_mod=$CURUSER["control_panel"]=='yes';
           
           
     
      if ((preg_match_all ('' . '#^/clean(.*)$#', $s, $Matches, PREG_SET_ORDER) AND $is_mod))
      {
            return execcommand_clean ($Matches);
      }elseif ((preg_match_all ('' . '#^/clean(.*)$#', $s, $Matches, PREG_SET_ORDER) AND !$is_mod))
      {return execcommand_noclean($Matches);}
	  ////////////end clear ajax shoutbox//////////////
	return $s;
}

function torrenttable($res) {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;  //Define globals

	if ($site_config["MEMBERSONLY_WAIT"] && $site_config["MEMBERSONLY"] && in_array($CURUSER["class"], explode(",",$site_config["WAIT_CLASS"]))) {
		$gigs = $CURUSER["uploaded"] / (1024*1024*1024);
		$ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
		if ($ratio < 0 || $gigs < 0) $wait = $site_config["WAITA"];
		elseif ($ratio < $site_config["RATIOA"] || $gigs < $site_config["GIGSA"]) $wait = $site_config["WAITA"];
		elseif ($ratio < $site_config["RATIOB"] || $gigs < $site_config["GIGSB"]) $wait = $site_config["WAITB"];
		elseif ($ratio < $site_config["RATIOC"] || $gigs < $site_config["GIGSC"]) $wait = $site_config["WAITC"];
		elseif ($ratio < $site_config["RATIOD"] || $gigs < $site_config["GIGSD"]) $wait = $site_config["WAITD"];
		else $wait = 0;
	}

	// Columns
	$cols = explode(",", $site_config["torrenttable_columns"]);
	$cols = array_map("strtolower", $cols);
	$cols = array_map("trim", $cols);
	$colspan = count($cols);
	// End
	
	// Expanding Area
	$expandrows = array();
	if (!empty($site_config["torrenttable_expand"])) {
		$expandrows = explode(",", $site_config["torrenttable_expand"]);
		$expandrows = array_map("strtolower", $expandrows);
		$expandrows = array_map("trim", $expandrows);
	}
	// End
	
	
	   //openSSL torrent encryption
require_once("backend/URLencrypt.php");
////end torrent encryption

	
	
	
	
	echo '<table align="center" class="ttable_headinner" width="100%"><thead><tr class="ttable_head">';

	foreach ($cols as $col) {
		switch ($col) {
			case 'category':
				echo "<th>".T_("TYPE")."</th>";
			break;

			case 'name':
				echo "<th>".T_("NAME")."</th>";
			break;
			case 'dl':
				echo "<th>".T_("DL")."</th>";
			break;
			case 'magnet':
				echo "<th>".T_("MAGNET2")."</th>";
			break;
			case 'imdb':
                echo "<th>".T_("IMDB_INDEX_PAGE")."</th>";
            break;
			case 'tube':
                echo "<th>".T_("YOUTUBE")."</th>";
            break;
			case 'uploader':
				echo "<th>".T_("UPLOADER")."</th>";
			break;
			case 'comments':
				echo "<th>".T_("COMM")."</th>";
			break;
			case 'nfo':
				echo "<th>".T_("NFO")."</th>";
			break;
			case 'size':
				echo "<th>".T_("SIZE")."</th>";
			break;
			case 'completed':
				echo "<th>".T_("C")."</th>";
			break;
			case 'seeders':
				echo "<th>".T_("S")."</th>";
			break;
			case 'leechers':
				echo "<th>".T_("L")."</th>";
			break;
			case 'health':
				echo "<th>".T_("HEALTH")."</th>";
			break;
			case 'external':
				if ($site_config["ALLOWEXTERNAL"])
					echo "<th>".T_("L/E")."</th>";
			break;
			case 'added':
				echo "<th>".T_("ADDED")."</th>";
			break;
			case 'speed':
				echo "<th>".T_("SPEED")."</th>";
			break;
			case 'wait':
				if ($wait)
					echo "<th>".T_("WAIT")."</th>";
			break;
			case 'rating':
				echo "<th>".T_("RATINGS")."</th>";
			break;
		}
	}
	if ($wait && !in_array("wait", $cols))
		echo "<th>".T_("WAIT")."</th>";
	
	echo "</tr></thead>";

	while ($row = mysqli_fetch_assoc($res)) {
		$id = $row["id"];

		print("<tr class='t-row'>\n");

	$x = 1;

	foreach ($cols as $col) {
		switch ($col) {
			case 'category':
				print("<td class='ttable_col$x' align='center' valign='middle'>");
				if (!empty($row["cat_name"])) {
					print("<a href=\"torrents.php?cat=" . $row["category"] . "\">");
					if (!empty($row["cat_pic"]) && $row["cat_pic"] != "")
						print("<img border=\"0\"src=\"" . $site_config['SITEURL'] . "/images/categories/" . $row["cat_pic"] . "\" alt=\"" . $row["cat_name"] . "\" />");
					else
						print($row["cat_parent"].": ".$row["cat_name"]);
					print("</a>");
				} else
					print("-");
				print("</td>\n");
			break;
			case 'name':
				$char1 = 35; //cut name length 
				$smallname = htmlspecialchars(CutName($row["name"], $char1));
				$dispname = "<b>".$smallname."</b>";
                
				$last_access = $CURUSER["last_browse"];
				$time_now = gmtime();
				if ($last_access > $time_now || !is_numeric($last_access))
					$last_access = $time_now;
				if (sql_timestamp_to_unix_timestamp($row["added"]) >= $last_access)
//					$dispname .= "<b><font color='#ff0000'> - (".T_("NEW")."!)</font></b>";
					$dispname .= " <img src='images/newtorrent.png' border='0'>";

if ($row["sticky"] == "yes")
                                        $dispname .= " <img src='images/sticky.gif' border='0'>";
				if ($row["freeleech"] == 1)
					$dispname .= " <img src='images/free.gif' border='0' alt='' />";
					
					    if ($row["uplreq"] == 'yes')
                   $dispname .= "<b><font color='#0000FF'> - (".T_("REQUEST").")</font></b>";
					
					
					
//////////BALLOON TOOLTIP MOD UFFENO1 VERSION (8-4-2018). Edited to suit the use of CSS to control the color of background. See theme.css of each theme for ballooncolor and balloonheadercolor.
                    $preparing_descr=SQL_Query_exec("SELECT torrents.anon, torrents.views, torrents.hits, torrents.descr, torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, torrents.nfo, torrents.last_action, torrents.numratings, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.external, torrents.image1, torrents.announce, torrents.numfiles, torrents.freeleech, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.numratings, categories.name AS cat_name, torrentlang.name AS lang_name, torrentlang.image AS lang_image, categories.parent_cat as cat_parent, users.username, users.privacy FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id");
               $descr=mysqli_fetch_array($preparing_descr);
               
               if (empty($descr["lang_name"]))
               $descr["lang_name"] = "Unknown/NA";
               $lang = "<b>" .T_("LANG"). " : </b>" . $descr["lang_name"] . "";
               
               if (isset($descr["lang_image"]) && $descr["lang_image"] != "")
               $flag = "<img src=$site_config[SITEURL]/images/languages/$descr[lang_image] border=0   alt=$descr[lang_name]>";
               
               $bimg = @mysqli_fetch_array(@SQL_Query_exec("SELECT image1 FROM torrents WHERE id=$id"));
                    $balon =($bimg["image1"] ? "$site_config[SITEURL]/uploads/images/" . htmlspecialchars($bimg["image1"]) : "images/nocover.png");
               
               $des = mysqli_real_escape_string($GLOBALS["DBconnector"],format_comment($descr['descr']));
               $char5 = 500; //cut name length
               
               if (($descr["anon"] == "yes" || $row["privacy"] == "strong") && $CURUSER["id"] != $descr["owner"] && $CURUSER["edit_torrents"] != "yes") {
               $descr['username'] = "".T_("ANONYMOUS")."";
               }else{
               $descr['username'];
               }
               print("<td class='ttable_col$x' nowrap='nowrap'>".(count($expandrows)?"<a href=\"javascript: klappe_torrent('t".$row['id']."')\"><img border=\"0\" src=\"".$site_config["SITEURL"]."/images/plus.gif\" id=\"pict".$row['id']."\" alt=\"Show/Hide\" class=\"showthecross\" /></a>":"")."&nbsp;<a href=\"torrents-details.php?id=$id&amp;hit=1\" onMouseover=\"return overlib('<table class=ballooncolor border=1 width=100% class=ttable_headinner cellspacing=0 cellpadding=5 align=center><tr><td class=balloonheadercolor colspan=2 align=center>$smallname</td></tr><tr valign=top><td class=table_col1 align=center><img border=0 width=120 src=$balon></td><td width=80% class=table_col1><div align=left><b>Uploaded on: </b>" . date("m-d-Y", utc_to_tz_time($row["added"])) . "<br /><b>Size: </b>". mksize($row["size"]) . "<br /><b>Completed: </b>" . $row["times_completed"] . "<br /></div><div align=left><b>Views: </b>" . $descr["views"] . "<br />".$lang." ".$flag."<br /><b>Hits: </b>" . $descr["hits"] . "<br /><b>Seeders: </b><font color=green>" . $row["seeders"] . "</font><br /><b>Leechers: </b><font color=red>" . $row["leechers"] . "</font><br /><b> Uploaded by: </b>" . $descr['username'] . "</div></td></tr><tr><td class=balloonheadercolor colspan=2 align=center>".T_("DESCRIPTION")."</td></tr><tr><td class=table_col1 colspan=2>" . CutName($des, $char5) . "</td></tr></table>', CENTER, HEIGHT, 150, WIDTH, 300)\"; onMouseout=\"return nd()\">".$dispname."</a></td>");   
break;
////////END BALLOON MOD



/////This goes below to reverse the torrent encryption/////<a href=\"download.php?id=$id&amp;name=" . rawurlencode($row["filename"]) . "\">
//		case 'dl':
//				print("<td class='ttable_col$x' align='center'><a href=\"download.php?id=$id&amp;name=" . rawurlencode($row["filename"]) . "\"><img src='" . $site_config['SITEURL'] . "/images/icon_download.png' border='0' width='50' height='50'alt=\"Download .torrent\" /></a></td>");
//			break;
				
			case 'dl':
			require_once("backend/URLencrypt.php");
				print("<td class='ttable_col$x' align='center'><a href='/download.php?id=" . $row["id"] . "'><img src='" . $site_config['SITEURL'] . "/images/icon_download.png' border='0' width='50' height='50'title='Download .torrent' /></a></td>");
			break;

			case 'magnet':
				$shit = mysqli_fetch_array(SQL_Query_exec("SELECT info_hash FROM torrents WHERE id=$id"));
		//	if ($row["external"]!='yes') {
		print ("<td class='ttable_col$x' align='center'><a href=\"magnet:?xt=urn:btih:" . $shit["info_hash"] . "&dn=" . rawurlencode($row['name']) . "&tr=" . $row['announce'] . "?passkey=". $CURUSER ['passkey']. "\"><img src='" . $site_config['SITEURL'] . "/images/magnetique.png' border='0' width='50' height='50'title='Download via Magnet' /></a></td>");
		//	}else{
	//	print ("<td class='ttable_col$x' align='center'><img src='" . $site_config['SITEURL'] . "/images/no_magnet.png' border='0' width='50' height='50'title='No magnet links on externally tracked torrents due to user passkey' /></td>");
	//			}
		break;
		
			// case 'magnet':
			// 	    $shit = @mysqli_fetch_array(@SQL_Query_exec("SELECT info_hash FROM torrents WHERE id=$id"));
			// 		print ("<td class='ttable_col$x' align='center'><a href=\"magnet:?xt=urn:btih:" . $shit["info_hash"] . "&dn=" . rawurlencode($row['name']) . "&tr=" . $row['announce'] . "?passkey=". $CURUSER ['passkey']. "\"><img src='" . $site_config['SITEURL'] . "/images/magnetique.png' border='0' width='50' height='50'title='Download via Magnet' /></a></td>");
			// break;	
			 
			case 'imdb':
      if ($row["imdb"])   
            print("<td class='ttable_col$x' align='center'><a href=".$row['imdb']." target='_blank'><".htmlspecialchars($row['imdb'])."><img src='" . $site_config['SITEURL'] . "/images/imdb.png'  border='0' width='50' height='50' alt=\"\" /></a></td>");
         else
         print("<td class='ttable_colx' align='center'>-</td>");   
         break;
		 
		 case 'tube':
		 	$video=$row["trailers"]; //Dont forget to make changes to torrents-details also around line 304
			$video=substr($video,27); //adjustments to shorten the URL removes first 27 characters from URL
			$video2=$video; //changing variables to further shorten the URL
			$video2=substr($video2, 0, -21); //more adjustments to shorten the URL down the the IMDB video number only. removes last 21 characters
      if ($row["tube"])   
            print("<td class='ttable_col$x' align='center'><a rel=\"prettyPhoto\"  href=".$row['tube']." ><".htmlspecialchars($row['tube'])."><img src='" . $site_config['SITEURL'] . "/images/youtube1.png'  border='0' width='50' height='50' title='Click to watch the YouTube Trailer' /></a></td>");
      elseif ($row["trailers"])   
            print("<td class='ttable_col$x' align='center'><a rel=\"prettyPhoto[iframes]\"  href=\"http://www.imdb.com/videoembed/". $video2."?ref_=embed&?iframe=true\"><img src='" . $site_config['SITEURL'] . "/images/movietrailers.png'  border='0' width='50' height='50' title='Click to watch the IMDb Trailer' /></a></td>");	
			else
         print("<td class='ttable_colx' align='center'>-</td>");   
         break;
		 
			case 'uploader':
				echo "<td class='ttable_col$x' align='center'>";
				if (($row["anon"] == "yes" || $row["privacy"] == "strong") && $CURUSER["id"] != $row["owner"] && $CURUSER["edit_torrents"] != "yes")
					echo "Anonymous";
				elseif ($row["username"])
					echo "<a href='account-details.php?id=$row[owner]'>".class_user($row['username'])."</a>";
				else
					echo "Unknown";
				echo "</td>";
			break;
			case 'comments':
				print("<td class='ttable_col$x' align='center'><font size='1' face='verdana'><a href='comments.php?type=torrent&amp;id=$id'>" . number_format($row["comments"]) . "</a></font></td>\n");
			break;
			case 'nfo':
				if ($row["nfo"] == "yes")
					print("<td class='ttable_col$x' align='center'><a href='nfo-view.php?id=$row[id]'rel=\"prettyPhoto\"><img src='" . $site_config['SITEURL'] . "/images/icon_nfo.gif' border='0' alt='View NFO' /></a></td>");
				else
					print("<td class='ttable_col$x' align='center'>-</td>");
			break;
			case 'size':
				print("<td class='ttable_col$x' align='center'>".mksize($row["size"])."</td>\n");
			break;
			case 'completed':    
				print("<td class='ttable_col$x' align='center'><font color='orange'><b>".number_format($row["times_completed"])."</b></font></td>");
			break;
			case 'seeders':
				print("<td class='ttable_col$x' align='center'><font color='green'><b>".number_format($row["seeders"])."</b></font></td>\n");
			break;
			case 'leechers':
				print("<td class='ttable_col$x' align='center'><font color='#ff0000'><b>" . number_format($row["leechers"]) . "</b></font></td>\n");
			break;
			case 'health':
				print("<td class='ttable_col$x' align='center'><img src='".$site_config["SITEURL"]."/images/health/health_".health($row["leechers"], $row["seeders"]).".gif' alt='' /></td>\n");
			break;
			case 'external':
				if ($site_config["ALLOWEXTERNAL"]){
					if ($row["external"]=='yes')
						print("<td class='ttable_col$x' align='center'>".T_("E")."</td>\n");
					else
						print("<td class='ttable_col$x' align='center'>".T_("L")."</td>\n");
				}
			break;
			case 'added':
				print("<td class='ttable_col$x' align='center'>".date("n-d-Y g:i a", utc_to_tz_time($row['added']))."</td>");
			break;
			case 'speed':
				if ($row["external"] != "yes" && $row["leechers"] >= 1){
					$speedQ = SQL_Query_exec("SELECT (SUM(downloaded)) / (UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(started)) AS totalspeed FROM peers WHERE seeder = 'no' AND torrent = '$id' ORDER BY started ASC");
					$a = mysqli_fetch_assoc($speedQ);
					$totalspeed = mksize($a["totalspeed"]) . "/s";
				} else
					$totalspeed = "--";
			print("<td class='ttable_col$x' align='center'>$totalspeed</td>");
			break;
			case 'wait':
				if ($wait){
					$elapsed = floor((gmtime() - strtotime($row["added"])) / 3600);
					if ($elapsed < $wait && $row["external"] != "yes") {
						$color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
						print("<td class='ttable_col$x' align='center'><a href=\"faq.php#section46\"><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></a></td>\n");
					} else
						print("<td class='ttable_col$x' align='center'>--</td>\n");
				}
			break;
			case 'rating':
				if (!$row["rating"])
					$rating = "--";
				else
					$rating = "<a title='$row[rating]/5'>".ratingpic($row["rating"])."</a>";
					//$rating = ratingpic($row["rating"]);
                     //$srating .= "$rpic (" . $row["rating"] . " out of 5) " . $row["numratings"] . " users have rated this torrent";
				print("<td class='ttable_col$x' align='center'>$rating</td>");
			break;
		}
		if ($x == 2)
			$x--;
		else
			$x++;
	}
	?>
<script type="text/javascript">
$(document).ready(function(){

  $("img").hover(function() {
    var temp = $(this).attr("src");
    $(this).attr("src", $(this).attr("data-alt-src"));
    $(this).attr("data-alt-src", temp);
  });
})
</script>
<?php
	
		//Wait Time Check
		if ($wait && !in_array("wait", $cols)) {
			$elapsed = floor((gmtime() - strtotime($row["added"])) / 3600);
			if ($elapsed < $wait && $row["external"] != "yes") {
				$color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
				print("<td class='ttable_col$x' align='center'><a href=\"faq.php\"><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></a></td>\n");
			} else
				print("<td class='ttable_col$x' align='center'>--</td>\n");
			$colspan++;
			if ($x == 2)
				$x--;
			else
				$x++;
		}
		
		print("</tr>\n");

		//Expanding area
		if (count($expandrows)) {
			print("<tr class='t-row'><td class='ttable_col$x' colspan='$colspan'><div id=\"kt".$row['id']."\" style=\"margin-left: 2px; display: none;\">");
			print("<table width='100%' border='0' cellspacing='0' cellpadding='0'>");
			foreach ($expandrows as $expandrow) {
				switch ($expandrow) {
					case 'size':
						print("<tr><td><b>".T_("SIZE")."</b>: ".mksize($row['size'])."</td></tr>");
					break;
					case 'speed':
						if ($row["external"] != "yes" && $row["leechers"] >= 1){
							$speedQ = SQL_Query_exec("SELECT (SUM(downloaded)) / (UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(started)) AS totalspeed FROM peers WHERE seeder = 'no' AND torrent = '$id' ORDER BY started ASC");
							$a = mysqli_fetch_assoc($speedQ);
							$totalspeed = mksize($a["totalspeed"]) . "/s";
							print("<tr><td><b>".T_("SPEED").":</b> $totalspeed</td></tr>");
						}
					break;
					case 'added':
						print("<tr><td><b>".T_("ADDED").":</b> ".date("m-d-Y \\a\\t g:i:s", utc_to_tz_time($row['added']))."</td></tr>");
					break;
					case 'tracker':
					if ($row["external"] == "yes")
						print("<tr><td><b>".T_("TRACKER").":</b> ".htmlspecialchars($row["announce"])."</td></tr>");
					break;
					case 'completed':
						print("<tr><td><b>".T_("COMPLETED")."</b>: ".number_format($row['times_completed'])."</td></tr>");
					break;
				}
			}
				print("</table></div></td></tr>\n");
		}
		//End Expanding Area


	}

	print("</table><br />\n");

}

function pager($rpp, $count, $href, $opts = array()) {
    $pages = ceil($count / $rpp);

    if (!$opts["lastpagedefault"])
        $pagedefault = 0;
    else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    }

    if (isset($_GET["page"])) {
        $page = (int) $_GET["page"];
        if ($page < 0)
            $page = $pagedefault;
    }
    else
        $page = $pagedefault;

    $pager = "";

    $mp = $pages - 1;
    $as = "<b>&lt;&lt;&nbsp;".T_("PREVIOUS")."</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;
    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as = "<b>".T_("NEXT")."&nbsp;&gt;&gt;</b>";
    if ($page < $mp && $mp >= 0) {
        $pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "...";
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count)
                $end = $count;
            $text = "$start&nbsp;-&nbsp;$end";
            if ($i != $page)
                $pagerarr[] = "<a href=\"{$href}page=$i\"><b>$text</b></a>";
            else
                $pagerarr[] = "<b>$text</b>";
        }
        $pagerstr = join(" | ", $pagerarr);
        $pagertop = "<p align=\"center\">$pager<br />$pagerstr</p>\n";
        $pagerbottom = "<p align=\"center\">$pagerstr<br />$pager</p>\n";
    }
    else {
        $pagertop = "<p align=\"center\">$pager</p>\n";
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}

function commenttable($res, $type = null) {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;  //Define globals

	while ($row = mysqli_fetch_assoc($res)) {

		$postername = class_user($row["username"]);
		if ($postername == "") {
			$postername = T_("DELUSER");
			$title = T_("DELETED_ACCOUNT");
			$avatar = "";
			$usersignature = "";
			$userdownloaded = "";
			$useruploaded = "";
		}else {
			$privacylevel = $row["privacy"];
			$avatar = htmlspecialchars($row["avatar"]);
			$title =  format_comment($row["title"]);
			$usersignature = stripslashes(format_comment($row["signature"]));
			$userdownloaded = mksize($row["downloaded"]);
			$useruploaded = mksize($row["uploaded"]);
		}

		if ($row["downloaded"] > 0)
			$userratio = number_format($row["uploaded"] / $row["downloaded"], 2);
		else
			$userratio = "---";

		if (!$avatar)
			$avatar = $site_config["SITEURL"]."/images/default_avatar.gif";

		$commenttext = format_comment($row["text"]);
   
        $edit = null;
        if ($type == "torrent" && $CURUSER["edit_torrents"] == "yes" || $type == "news" && $CURUSER["edit_news"] == "yes" || $CURUSER['id'] == $row['user']) 
            $edit = '[<a href="comments.php?id='.$row["id"].'&amp;type='.$type.'&amp;edit=1">Edit</a>]&nbsp;';
       
        $delete = null;
        if ($type == "torrent" && $CURUSER["delete_torrents"] == "yes" || $type == "news" && $CURUSER["delete_news"] == "yes")
            $delete = '[<a href="comments.php?id='.$row["id"].'&amp;type='.$type.'&amp;delete=1">Delete</a>]&nbsp;';
        
        print('<div class="f-post f-border"><table cellspacing="0" width="100%">');
        print('<tr class="p-title">');
        print('<th align="center" width="150"></th>');
        print('<th align="right">' . $edit . $delete . '[<a href="report.php?comment='.$row["id"].'">Report</a>] Posted: '.date("m-d-Y \\a\\t g:i:s", utc_to_tz_time($row["added"])).'<a id="comment'.$row["id"].'"></a></th>');
        print('</tr>');
        print('<tr valign="top">');
        if ($CURUSER['edit_users'] == 'yes' && $privacylevel == 'strong')
            print('<td class="f-border comment-details" align="left" width="150"><center><b>'.$postername.'</b><br /><i>'.$title.'</i><br /><img width="80" height="80" src="'.$avatar.'" alt="" /><br /><br />Uploaded: ---<br />Downloaded: ---<br />Ratio: ---<br /><br /><a href="account-details.php?id='.$row["user"].'"><img src="themes/'.$THEME.'/forums/icon_profile.gif" border="" alt="" /></a> <a href="mailbox.php?compose&amp;id='.$row["user"].'"><img src="themes/'.$THEME.'/forums/icon_pm.gif" border="0" alt="" /></a></center></td>'); 
        else
            print('<td class="f-border comment-details" align="left" width="150"><center><b>'.$postername.'</b><br /><i>'.$title.'</i><br /><img width="80" height="80" src="'.$avatar.'" alt="" /><br /><br />Uploaded: '.$useruploaded.'<br />Downloaded: '.$userdownloaded.'<br />Ratio: '.$userratio.'<br /><br /><a href="account-details.php?id='.$row["user"].'"><img src="themes/'.$THEME.'/forums/icon_profile.gif" border="0" alt="" /></a> <a href="mailbox.php?compose&amp;id='.$row["user"].'"><img src="themes/'.$THEME.'/forums/icon_pm.gif" border="0" alt="" /></a></center></td>'); 
        print('<td class="f-border comment">'.$commenttext.'</td>');
        print('</tr>');
        print('</table></div>');  
        print('<br />');      
	}
}
// WHO IS WHERE FUNCTION
function where ($where, $userid, $update=1){
        if (!is_valid_id($userid))
                die;

        if(empty($where))
                $where = "Unknown Location...";

        if ($update) 
                SQL_Query_exec("UPDATE users SET last_access='" . get_date_time() . "', page=".sqlesc($where)." WHERE id=" . $userid);
        
        if (!$update){
                return $where;
                }else{
                return;
                }
}
//END THE DAMN FUNCTION
function get_user_class_name($i){
	GLOBAL $CURUSER;
	if ($i == $CURUSER["class"])
		return $CURUSER["level"];

	$res=SQL_Query_exec("SELECT level FROM groups WHERE group_id=".$i."");
	$row=mysqli_fetch_row($res);
	return $row[0];
}

function get_user_class(){
	return $GLOBALS["CURUSER"]["class"];
}

function get_ratio_color($ratio) {
	if ($ratio < 0.1) return "#ff0000";
	if ($ratio < 0.2) return "#ee0000";
	if ($ratio < 0.3) return "#dd0000";
	if ($ratio < 0.4) return "#cc0000";
	if ($ratio < 0.5) return "#bb0000";
	if ($ratio < 0.6) return "#aa0000";
	if ($ratio < 0.7) return "#990000";
	if ($ratio < 0.8) return "#880000";
	if ($ratio < 0.9) return "#770000";
	if ($ratio < 1) return "#660000";
	return "#000000";
}

function ratingpic($num) {
	GLOBAL $site_config;
    $r = round($num * 2) / 2;
	if ($r != $num) {
		$n = $num-$r;
		if ($n < .25)
			$n = 0;
		elseif ($n >= .25 && $n < .75)
			$n = .5;
		$r += $n;
	}
    if ($r < 1 || $r > 5)
        return;

    return "<img src=\"".$site_config["SITEURL"]."/images/rating/$r.png\" border=\"0\" alt=\"rating: $num/5\" title=\"rating: $num/5\" />";
}

function DateDiff ($start, $end) {
	if (!is_numeric($start))
		$start = sql_timestamp_to_unix_timestamp($start);
	if (!is_numeric($end))
		$end = sql_timestamp_to_unix_timestamp($end);
	return ($end - $start);
}

function classlist() {
    $ret = array();
    $res = SQL_Query_exec("SELECT * FROM groups ORDER BY group_id ASC");
    while ($row = mysqli_fetch_assoc($res))
        $ret[] = $row;
    return $ret;
}

function array_map_recursive ($callback, $array) {
	$ret = array();

	if (!is_array($array))
		return $callback($array);

	foreach ($array as $key => $val) {
		$ret[$key] = array_map_recursive($callback, $val);
	}
	return $ret;
}

function strtobytes ($str) {
	$str = trim($str);
	if (!preg_match('!^([\d\.]+)\s*(\w\w)?$!', $str, $matches))
		return 0;

	$num = $matches[1];
	$suffix = strtolower($matches[2]);
	switch ($suffix) {
		case "tb": // TeraByte
			return $num * 1099511627776;
		case "gb": // GigaByte
			return $num * 1073741824;
		case "mb": // MegaByte
			return $num * 1048576;
		case "kb": // KiloByte
			return $num * 1024;
		case "b": // Byte
		default:
			return $num;
	}
}

function cleanstr ($s) {
	if (function_exists("filter_var")) {
		return filter_var($s, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
	} else {
		return preg_replace('/[\x00-\x1F]/', "", $s);
	}
}

function is_ipv6 ($s) {
	return is_int(strpos($s, ":"));
}

// Taken from php.net comments
function ip2long6($ipv6) {
  $ip_n = inet_pton($ipv6);
  $bits = 15; // 16 x 8 bit = 128bit
  while ($bits >= 0) {
    $bin = sprintf("%08b",(ord($ip_n[$bits])));
    $ipv6long = $bin.$ipv6long;
    $bits--;
  }
  return gmp_strval(gmp_init($ipv6long,2),10);
}

function long2ip6($ipv6long) {

  $bin = gmp_strval(gmp_init($ipv6long,10),2);
  if (strlen($bin) < 128) {
    $pad = 128 - strlen($bin);
    for ($i = 1; $i <= $pad; $i++) {
    $bin = "0".$bin;
    }
  }
  $bits = 0;
  while ($bits <= 7) {
    $bin_part = substr($bin,($bits*16),16);
    $ipv6 .= dechex(bindec($bin_part)).":";
    $bits++;
  }
  // compress

  return inet_ntop(inet_pton(substr($ipv6,0,-1)));
} 

function passhash ($text) {
	GLOBAL $site_config;

	switch (strtolower($site_config["passhash_method"])) {
		case "sha1":
			return sha1($text);
		break;
		case "md5":
			return md5($text);
		break;
		case "hmac":
			$ret = hash_hmac($site_config["passhash_algorithm"], $text, $site_config["passhash_salt"]);
			return $ret ? $ret : die("Config Error: Unknown algorithm '$site_config[passhash_algorithm]'");
		break;
		default:
			die("Unrecognised passhash_method. Check your config.");
	}
}

  function autolink($al_url, $al_msg) {
      stdhead();
      begin_frame("");
      echo "\n<meta http-equiv=\"refresh\" content=\"2; url=$al_url\">\n";
      echo "<b>$al_msg</b>\n";
      echo "\n<b>Redirecting ...</b>\n";
      echo "\n[ <a href='$al_url'>link</a> ]\n";
      end_frame();
      stdfoot();
      exit;
  }

  function fuck_this($al_url, $al_msg) {
      stdhead();
      begin_frame("");
      echo "\n<meta http-equiv=\"refresh\" content=\"4; url=$al_url\">\n";
      echo "<b>$al_msg</b>\n";
      echo "\n<b>Resetting...</b>\n";
      echo "\n[ <a href='$al_url'>link</a> ]\n";
      end_frame();
      stdfoot();
      exit;
  }

  
  function spl__autoload( $f )
{
require_once('backend/' . $f . '.php');
}


function seedtime($ts = 0)
{                                                                                                                                                                                                       
$days = floor($ts / 86400);
$hours = floor($ts / 3600 ) % 24;       
$mins = floor($ts / 60) % 60;
$secs = $ts % 60;
return sprintf( '%d days, %d hours, %d minutes, %d seconds...', $days, $hours, $mins, $secs);
}
?>
