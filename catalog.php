
<?php
require_once("backend/functions.php");
dbconn();
loggedinonly();
   
   $search = trim(isset($_GET["search"]) ? $_GET["search"] : "");
   $pagemenu = (isset($pagemenu));
   $menu = (isset($menu));
if ($search != '' || (isset($class) ? $class : "")) {
   $query = "name LIKE  '$search%'";
if ($search)
      $q = "search=" . (isset($arr["name"]));
      }else{
      $letter = trim($_GET["letter"]);
if (strlen($letter) > 1)
die;

if ($letter == "" || strpos("0123456789abcdefghijklmnopqrstuvwxyz", $letter) === false)
      $letter = "a";
      $query = "name LIKE '$letter%'";
      $q = "letter=$letter";
      }
$letter = (isset($letter) ? $letter : "");

if ($CURUSER["view_xxx"] != "yes")
$where = "AND category != '40' && category != '7'";

stdhead("Site Catalog");

    begin_frame("".T_("CATALOGUE")."");
      print("<br /><center><form method=get action=?>\n");
      print("<input align='center' type=text size=30 name=search placeholder='".T_("SEARCH_CAT")."' id=searchinput>\n");
      print("<input type=submit value=".T_("SEARCH").">\n");
      print("</form>\n");
      print("<p><br />\n");

      for ($i = 97; $i < 123; ++$i)
      {
      $l = chr($i);
      $L = chr($i - 32);

if ($l == $letter)

      print("<b><u>$L</u></b>\n");
      else
      print("<a href=?letter=$l><b>$L</b></a>\n");
      }
      print("</p><br />\n");

///number test
      for ($i = 48; $i < 58; ++$i)
      {
      $l = chr($i);
      $L = chr($i);

if ($l == $letter)

      print("<b><u>$L</u></b>\n");
      else
      print("<a href=?letter=$l><b>$L</b></a>\n");
      }
      print("</p><br /></center>\n");
///end test

      $page = (isset($_GET['page']) ? $_GET['page'] : "");
      $perpage = 4;

$res = SQL_Query_exec("SELECT COUNT(*) FROM torrents WHERE $query $where AND visible != 'no'");
$arr = mysqli_fetch_row($res);
      $pages = floor($arr[0] / $perpage);

if ($pages * $perpage < $arr[0])

      ++$pages;

if ($page < 1)

      $page = 1;
      else

if ($page > $pages)

      $page = $pages;
      for ($i = 1; $i <= $pages; ++$i)

if ($i == $page) {
   
      $pagemenu .= "<b>$i</b>\n";
      }else{
      $pagemenu .= "<a href=?$q&page=$i><b>$i</b></a>\n";
      }

   
if ($page == 1) {
     
     $menu .= "<b>&lt;&lt; ".T_("PREVIOUS")."</b>";
     }else{
      $menu .= "<a href=?$q&page=" . ($page - 1) . "><b>&lt;&lt; ".T_("PREVIOUS")."</b></a>";
     }
      $menu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";


if ($page >= $pages)
     $menu .= "<b>".T_("NEXT")." &gt;&gt;</b>";
     else
     $menu .= "<a href=?$q&page=" . ($page + 1) . "><b>".T_("NEXT")." &gt;&gt;</b></a>";
     print("<center><p>$menu<br />".$pagemenu."</p></center><br />");
     $offset = ($page * $perpage) - $perpage;

$res = SQL_Query_exec("SELECT * FROM torrents WHERE $query $where AND visible != 'no' ORDER BY name ASC LIMIT $offset,$perpage");
$num = mysqli_num_rows($res);

if (!$num){

      print("<table width=100% cellpadding=0><tr><td align=center class=table_col2><b>".T_("CATALOGUE")."</b></td></tr>".
      "<tr><td>".T_("NO_MATCHES")."</td></tr>".
      "</table>");
      } else {
      for ($i = 0; $i < $num; ++$i)
      {
$arr = mysqli_fetch_assoc($res);
      {
      $id = $arr["id"];

$ret = SQL_Query_exec("SELECT seeder, ip, port, uploaded, downloaded, to_go, UNIX_TIMESTAMP(started) AS st, UNIX_TIMESTAMP(last_action) AS la, userid FROM peers WHERE torrent = $id AND seeder ='yes' ORDER BY to_go ASC LIMIT 5");
      $nul = mysqli_num_rows($ret);
if (!$nul)
      {
      $owner1 = "<b><i>".T_("TORRENT_NOT_ACTIVE")."</i></b>";
      $s = "<br /><table class=table_test width=100% border=1 cellpadding=3>";
      $s .= "<tr><td colspan=7 class=table_head align=center><b>".T_("SEED_INFO")."</b></td></tr>";
      $s .= "<tr><td class=table_col2><b>".T_("USERNAME")."</b></td><td class=table_col2 align=right><b>".T_("UPLOADED")."</b></td><td class=table_col2 align=right><b>".T_("SPEED")."</b></td><td class=table_col2 align=right><b>".T_("DOWNLOADED")."</b></td><td class=table_col2 align=right><b>".T_("SPEED")."</b></td>";
      $s .= "<td class=table_col2 align=right><b>".T_("RATIO")."</b></td><td class=table_col2 align=right><b>".T_("FINISHED")." %</b></td></tr>";
      $s .= "<tr><td class=table_col1 class=outer>$owner1</td><td colspan=6 class=ttable_col1 align=center><b>".T_("NO_INFO")."</b></td></tr>";
      $s .= "</table><br /></fieldset><br /><br />";

      } else {
      $s = "<br /><table class=table_test width=100% border=1 cellpadding=3>";
      $s .= "<tr><td colspan=7 class=table_head align=center><b>".T_("SEED_INFO")."</b></td></tr>";
      $s .= "<tr><td class=table_col2><b>".T_("USERNAME")."</b></td><td class=table_col2 align=right><b>".T_("UPLOADED")."</b></td><td class=table_col2 align=right><b>".T_("SPEED")."</b></td><td class=table_col2 align=right><b>".T_("DOWNLOADED")."</b></td><td class=table_col2 align=right><b>".T_("SPEED")."</b></td>";
      $s .= "<td class=table_col2 align=right><b>".T_("RATIO")."</b></td><td class=table_col2 align=right><b>".T_("FINISHED")." %</b></td></tr>";

      for ($m = 0; $m < $nul; ++$m)
      {
      $arrs = mysqli_fetch_assoc($ret);
      {
      $cres = SQL_Query_exec("SELECT id, username, privacy, donated, warned FROM users WHERE id='$arrs[userid]'");
      $cros = mysqli_fetch_assoc($cres);
      $privacylevel = $cros["privacy"];
     $donated = $cros["donated"];
     $warned = $cros["warned"];
     $now = time();
      $secs = max(1, ($now - $arrs["st"]) - ($now - $arrs["la"]));
      $revived = (isset($arrs["revived"])) == "yes";

if ($privacylevel == "strong"){
if (get_user_class() <= 5){
    $owner1 = "<i>".T_("ANONYMOUS")."</i>";
     }else{
if ($site_config["CLASS_USER"]) {
    $owner1 = "<a href='account-details.php?id=$cros[id]'>" . class_user($cros["username"]) . "</a>";
    }else{
    $owner1 = "<a href='account-details.php?id=$cros[id]'>" . $cros["username"] . "</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
    }
    }   
     }else{
if ($site_config["CLASS_USER"]) {
    $owner1 = "<a href='account-details.php?id=$cros[id]'>" . class_user($cros["username"]) . "</a>";
    }else{
    $owner1 = "<a href='account-details.php?id=$cros[id]'>" . $cros["username"] . "</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";                     
    }
     }
     if (empty($cros["username"])) {
     $owner1 = "<i>".T_("UNKNOWN_USER")."</i>";
    }
   
      $bah = SQL_Query_exec("SELECT COUNT(*) FROM peers WHERE seeder = 'yes' AND torrent = $id");
      $count = mysqli_fetch_row($bah);
      $seeders = $count[0];
      $bah1 = SQL_Query_exec("SELECT COUNT(*) FROM peers WHERE seeder = 'no' AND torrent = $id");
      $count1 = mysqli_fetch_row($bah1);
      $leechers = $count1[0];

    $s .= "<tr><td class=table_col1 class=outer>".$owner1."</td>";
   $s .= "<td class=table_col1 align=right class=outer><nobr>" . mksize($arrs["uploaded"]) . "</nobr></td>";
   $s .= "<td class=table_col1 align=right class=outer><nobr>" . mksize(($arrs["uploaded"] - (isset($arrs["uploadoffset"]))) / $secs) . "/s</nobr></td>";
   $s .= "<td class=table_col1 align=right class=outer><nobr>" . mksize($arrs["downloaded"]) . "</nobr></td>";

if ($arrs["seeder"] == "no")
   $s .= "<td class=table_col1 align=right class=outer><nobr>" . mksize(($arrs["downloaded"] - $arrs["downloadoffset"]) / $secs) . "/s</nobr></td>";
   else
    $s .= "<td class=table_col1 align=right class=outer><nobr>" . mksize(($arrs["downloaded"] - (isset($arrs["downloadoffset"]))) / max(1, (isset($arrs["finishedat"])) - $arrs["st"])) . "/s</nobr></td>";

if ($arrs["downloaded"]) {
      $ratio = floor(($arrs["uploaded"] / $arrs["downloaded"]) * 1000) / 1000;
      $s .= "<td align=right class=outer><font color=" . get_ratio_color($ratio) . ">" . number_format($ratio, 3) . "</font></td>";
      }
      else

if ($arrs["uploaded"])
      $s .= "<td class=table_col1 align=right class=outer>Inf.</td>";
      else
      $s .= "<td class=table_col1 align=right class=outer>---</td>";
      $s .= "<td class=table_col1 align=right class=outer>" . sprintf("%.2f%%", 100 * (1 - ($arrs["to_go"] / $arr["size"]))) . "</td>";
      }
      }
      $s .= "</table><br /></fieldset><br /><br />";
      }

      $char1 = 50;
      $shortname = CutName(htmlspecialchars($arr["name"]), $char1);

      $image1 = (isset($row['image1']));
      }
if ($arr["image1"] == '')
      $image1 = "<div class='cursor'><img src=images/no_poster.png width=175 height=255></div>";
      else
      $image1 = "<a href=".$site_config["SITEURL"]."/uploads/images/$arr[image1] rel='prettyPhoto'><img src=".$site_config["SITEURL"]."/uploads/images/$arr[image1] width=175 height=255 border=0 title='".T_("CLICK_ENLARGE")."'></a>";


  $ret = SQL_Query_exec("SELECT torrents.anon, torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, torrents.nfo, torrents.last_action, torrents.numratings, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.external, torrents.image1, torrents.image2, torrents.announce, torrents.numfiles, torrents.freeleech, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.numratings, categories.name AS cat_name, torrentlang.name AS lang_name, torrentlang.image AS lang_image, categories.parent_cat as cat_parent, users.username, users.donated, users.warned, users.privacy FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id");
    $catarr =  mysqli_fetch_assoc($ret);
    $donated = $catarr["donated"];
    $warned = $catarr["warned"];
if ($site_config["CLASS_USER"]) {
    $ca = "<a href='account-details.php?id=$catarr[owner]'>".class_user($catarr['username'])."</a>";
    }else{
    $ca = "<a href='account-details.php?id=$catarr[owner]'>" . $catarr['username']."</a> " . ($donated > 0 ? "<img src='".$site_config["SITEURL"]."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
    }

if (($arr["anon"] == "yes" || $catarr["privacy"] == "strong") && $CURUSER["id"] != $catarr["owner"] && $CURUSER["edit_torrents"] != "yes")
     $owner2 = "<b>" .T_("ADDED_BY"). ": </b><i>".T_("ANONYMOUS")."</i>";
      else
     $owner2 = "<b>" .T_("ADDED_BY"). ": </b>".$ca."";
if (empty($catarr["username"])) {
     $owner1 = "<i>".T_("UNKNOWN_USER")."</i>";
    }     
 


      $flagname = $catarr["lang_name"];
 if (empty($catarr["lang_name"]))
      $catarr["lang_name"] = "Unknown/NA";
      $lang = "<td class=ttable_col2 align='center'><b>" .T_("LANG"). " : </b>" . $catarr["lang_name"] . "";

if (empty($catarr["lang_image"]))
      $catarr["lang_image"] = "unknown.gif";
      $flag = "&nbsp;<img border=\"0\" src=\"" . $site_config['SITEURL'] . "/images/languages/" . $catarr["lang_image"] . "\" alt=\"".$flagname."\" /></td>";

      print("<fieldset class='download'><legend ><!--<a style='text-decoration:none' title='".$arr["name"]."' href=torrents-details.php?id=$id&hit=1>--><b>" . $shortname . "</b><!--</a>--></legend>");
      print("<br /><table class=table_test border=1 width=100% cellpadding=3><tr><th colspan=10 align=center class=table_head><b>".T_("TORRENT_INFO")."</b></th></tr>" .
      "<tr><td colspan='4' valign=top rowspan=2 class=ttable_col1 cellpadding=5 style='padding: 10px;'><center><i><b><u>".T_("DESCRIPTION")."</u></b></i></center><br /><div style='height:200px;line-height:2em;overflow:auto;padding:5px;'><b><font size=2>" . format_comment($arr['descr']) . "</font></b></div></td><td align=center colspan=3 class=table_col2><b>" . $owner2 . "</b></td></tr>
      <tr><td colspan=3 class=ttable_col1 align=left width=10%>$image1</td></tr>
      <tr><td class=ttable_col2 align='center'><b>" .T_("CATEGORY"). " : </b>" . $catarr["cat_parent"] . " > " . $catarr["cat_name"] . "</td>".$lang." ".$flag."<td class=ttable_col2 align='center'><b>" .T_("TOTAL_SIZE"). " : </b>" . mksize($catarr["size"]) . " </td><td align=center class=ttable_col2><b>".T_("NUMBER_OF_DOWNLOADS")." : <font color=green>$arr[times_completed]</b></font> ".T_("TIMES")."</td><td width=10 class=ttable_col2 align=center><a href=\"download.php?id=$id&amp;name=" . rawurlencode($arr["filename"]) . "\"><img src='" . $site_config['SITEURL'] . "/images/icon_download.gif' border='0' title='".T_("DIRECT_DOWN")."' alt=\"Download torrent\" /></a></td><td class=ttable_col2 align=center><img border=\"0\" src=\"images/red_down.png\" width='10' height='10' title=\"".T_("LEECHERS")."!\"></a>(<font color=red><b>" . $arr["leechers"] . "</b></font>) - <img border=\"0\" src=\"images/green_up.png\" width='10' height='10' title=\"".T_("SEEDERS")."!\"></a>(<font color=green><b>" . $arr["seeders"] . "</b></font>)</td><td class=ttable_col2 align=center><a style='text-decoration:none' href=torrents-details.php?id=$id&hit=1><input type=button value='".T_("TORRENT")."' title='".T_("TORRENT_GO")."' alt='[".T_("TORRENT_GO")."]'></font></a></td></tr></table><br />");
      print("$s");
      }
      }
end_frame();
stdfoot();
?>

