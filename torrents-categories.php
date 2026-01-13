<?php
require_once("backend/functions.php");
//require("backend/URLencrypt.php");
dbconn();
stdhead("Torrents by Category");

//check permissions
if ($site_config["MEMBERSONLY"]){
	loggedinonly();

	if($CURUSER["view_torrents"]=="no")
		show_error_msg(T_("ERROR"), T_("NO_TORRENT_VIEW"), 1);
}
/*	
//Sondage
echo '<br /><div align="center"><h2><b>.:Sondage:.</b></h2></div><script type="text/javascript">
var jaxPollID = "54e0f3ccc583e";
</script>
<div style="width: 560px; height: 150px;">
<script type="text/javascript" src="jaxpoll/jaxpoll.js"></script>
</div><br /><br /><br />';
//End Sondage	
*/	
$query = "SELECT id, name, image, sub_sort FROM categories ORDER BY sub_sort";
$cat = SQL_Query_exec($query);
while($catresult = mysqli_fetch_assoc($cat)){

        ////////////////////BLOCK CATEGORIES////////////////////////
        if ($_GET["sort"] || $_GET["order"]) {

	switch ($_GET["sort"]) {
		case 'category': $sort = "torrents.category";break;
		case 'name': $sort = "torrents.name";break;
		case 'completed':	$sort = "torrents.times_completed";break;
		case 'seeders':	$sort = "torrents.seeders";break;
		case 'leechers': $sort = "torrents.leechers";break;
		case 'comments': $sort = "torrents.comments";break;
		case 'size': $sort = "torrents.size";break;
		default: $sort = "torrents.id";
	}

	if ($_GET["order"] == "asc" || ($_GET["sort"] != "id" && !$_GET["order"])) {
		$sort .= " ASC";
		
	} else {
		$sort .= " DESC";
		
	}

	$orderby = "ORDER BY $sort";

	}else{
		$orderby = "ORDER BY torrents.id DESC";
		$_GET["sort"] = "id";
		$_GET["order"] = "desc";
	}
        $where = "WHERE banned = 'no' AND category='$catresult[id]' AND visible='yes'"; //ou ?
        $limit = "LIMIT 5"; // limite
        $a22 = "SELECT torrents.sticky, torrents.imdb, torrents.tube, torrents.trailers, torrents.id, torrents.anon, torrents.announce, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.image AS cat_pic, categories.parent_cat AS cat_parent, users.username, users.privacy, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";
       $res = SQL_Query_exec($a22);

        if (mysqli_num_rows($res)) {
						
            begin_frame("$catresult[parent_cat] $catresult[name]");
            torrenttable($res);
            end_frame();
        }
        //////////////////FIN BLOCK////////////////////////////////////
}

stdfoot();

?>