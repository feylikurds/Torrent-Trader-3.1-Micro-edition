<?php
//
//      TorrentTrader v2.x
//      JWcinema 
//      $CreatedDate: 2012-07-08 12:38:09 +0000 (Mon, 08 July 2012) $
//      $CreatedBy: Torrentor Tracker $
//      http://torrentor.try.hu
//
//      http://www.torrenttrader.org
//
require_once("backend/functions.php");
dbconn(false);
loggedinonly();

stdhead("Site Videos");

begin_frame("Movies and Videos");
// Pager
    $where = " WHERE cinemaonline='yes'";
    $res = SQL_Query_exec("SELECT COUNT(*) FROM jwcinema $where") or die(mysqli_error($GLOBALS["DBconnector"]));
    $row = mysqli_fetch_array($res);
    $count = $row[0];
    unset($where);

    $orderby = "ORDER BY id DESC";    

// sql info
if ($count) {
    list($pagertop, $pagerbottom, $limit) = pager(10, $count, "jw-cinema.php?");
      $query = "SELECT description, poster, aviurl, id, name, info FROM jwcinema $where $orderby $limit";
	  $res = SQL_Query_exec($query) or die(mysqli_error($GLOBALS["DBconnector"]));
}else{
    unset($res3);
}
//truncate youtube data to a readable length on the main page//
// Original PHP code by Chirp Internet: www.chirp.com.au
// Please acknowledge use of this code by including this header.
function Truncate($string, $limit, $break=".", $pad="...")
{
  // return with no change if string is shorter than $limit
  if(strlen($string) <= $limit) return $string;

  // is $break present between $limit and the end of the string?
  if(false !== ($breakpoint = strpos($string, $break, $limit))) {
    if($breakpoint < strlen($string) - 1) {
      $string = substr($string, 0, $breakpoint) . $pad;
    }
  }
  return $string;
}
//end youtube data truncation//
if ($count) {
print($pagertop);

$arr = @mysqli_fetch_array(@SQL_Query_exec("SELECT poster FROM jwcinema WHERE id= '$id' "));
		
while($arr = mysqli_fetch_assoc($res)) {
print ('<BR /><table class="ttable_headinner" align="center" border="0" cellpadding="0" cellspacing="0" width="70%">');
print ('<tr><td class="ttable_head" width="auto" colspan="2">'.$arr["name"].'</td></tr>');
print ('<td align="left" width="20%">');
echo '<a title="Play Movie" href="jw-view-cinema.php?id='.$arr["id"].'"><img src="'.$arr["poster"].'" width="160px" alt="Click Here" border="0" /></a>';

$shortdesc = Truncate($arr["info"], 200);
echo ('</td><td width="auto">'.$shortdesc.'</td></tr></table><BR />');
}

    print($pagerbottom);
}
end_frame();
stdfoot();
?>