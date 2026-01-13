<?php
//
//  TorrentTrader v2.x
//      $LastChangedDate: 2011-11-04 15:06:52 +0000 (Fri, 04 Nov 2011) $
//      $LastChangedBy: dj-howarth1 $
//
//      http://www.torrenttrader.org
//
//
error_reporting(0); //disable error reporting

// check if client can handle gzip
if (stristr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip") && extension_loaded('zlib') && ini_get("zlib.output_compression") == 0) {
    if (ini_get('output_handler')!='ob_gzhandler') {
        ob_start("ob_gzhandler");
    } else {
        ob_start();
    }
}else{
     ob_start();
}
// end gzip control

require_once("backend/mysql.php");
require_once("backend/mysql.class.php");
require_once("backend/config.php");
require_once("backend/cache.php");

function dbconn() {
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

    if (!$GLOBALS["DBconnector"] = mysqli_connect($mysql_host, $mysql_user, $mysql_pass))
    {
      die('DATABASE: mysqli_connect: ' . mysqli_error($GLOBALS["DBconnector"]));
    }
     mysqli_select_db($mysql_db)
        or die('DATABASE: mysqli_select_db: ' + mysqli_error($GLOBALS["DBconnector"]));

    unset($mysql_pass); //security
}

function hex2bin($hexdata) {
  $bindata = "";
  for ($i=0;$i<strlen($hexdata);$i+=2) {
    $bindata.=chr(hexdec(substr($hexdata,$i,2)));
  }

  return $bindata;
}

function sqlesc($x) {
    return "'".mysqli_real_escape_string($GLOBALS["DBconnector"],$x)."'";
}

dbconn();

$infohash = array();

foreach (explode("&", $_SERVER["QUERY_STRING"]) as $item) {
    if (preg_match("#^info_hash=(.+)\$#", $item, $m)) {
        $hash = urldecode($m[1]);

        if (get_magic_quotes_gpc())
            $info_hash = stripslashes($hash);
        else
            $info_hash = $hash;
        if (strlen($info_hash) == 20)
            $info_hash = bin2hex($info_hash);
        else if (strlen($info_hash) != 40)
            continue;
        $infohash[] = sqlesc(strtolower($info_hash));
    }
}

if (!count($infohash)) die("Invalid infohash.");
    $query = "SELECT info_hash, seeders, leechers, times_completed, filename FROM torrents WHERE info_hash IN (".join(",", $infohash).")";
if ($site_config["cache_scrape"]) {
$rows = SQL_Query_exec_cached($query, $site_config["cache_scrape_time"]);
if (!$rows)
$rows = array();
} else {
$res = SQL_Query_exec($query);
$rows = array();
while ($row = mysqli_fetch_row($res))
$rows[] = $row;
}$result="d5:filesd";

foreach ($rows as $row)
{
    $hash = hex2bin($row[0]);
    $result.="20:".$hash."d";
    $result.="8:completei".$row[1]."e";
    $result.="10:downloadedi".$row[3]."e";
    $result.="10:incompletei".$row[2]."e";
    $result.="4:name".strlen($row[4]).":".$row[4]."e";
    $result.="e";
}

$result.="ee";

echo $result;
ob_end_flush();
mysqli_close($GLOBALS["DBconnector"]);
?>