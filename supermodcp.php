<?php
//
//  TorrentTrader v2.x
//	$LastChangedDate: 2011-12-23 18:28:59 +0000 (Fri, 23 Dec 2011) $
//      $LastChangedBy: spank-d
//	
//	http://www.torrenttrader.org
//
//

// VERY BASIC Super Mod CP

require_once ("backend/functions.php");
require_once ("backend/bbcode.php");
dbconn(false);
loggedinonly();

if (!$CURUSER || $CURUSER["class"]<"6"){
     show_error_msg(T_("ERROR"), T_("SORRY_YOU_HAVE_NO_RIGHTS_TO_ACCESS_THIS_PAGE"), 1);
}

 $action = $_REQUEST["action"];
 $do = $_REQUEST["do"];
 
function navmenu(){
global $site_config;


begin_frame(T_("MENU"));

?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td align="center"><a href="supermodcp.php?action=cheats"><img src="images/admin/cheats.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("DETECT_POSS_CHEATS"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=avatars"><img src="images/admin/avatar_log.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("AVATAR_LOG"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=freetorrents"><img src="images/admin/free_leech.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("FREE_LEECH_TORRENTS"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=polls&amp;do=view"><img src="images/admin/polls.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("POLLS"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=bannedtorrents"><img src="images/admin/banned_torrents.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("BANNED_TORRENTS"); ?></a><br /></td>
</tr>
<tr>
    <td colspan="5">&nbsp;</td>
</tr>
<tr>
    <td align="center"><a href="supermodcp.php?action=reports&amp;do=view"><img src="images/admin/report_system.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("REPORTS"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=rules&amp;do=view"><img src="images/admin/rules.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("RULES"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=news&amp;do=view"><img src="images/admin/news.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("NEWS"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=peers"><img src="images/admin/peer_list.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("PEERS_LIST"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=lastcomm"><img src="images/admin/comments.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("LATEST_COMMENTS"); ?></a><br /></td>
</tr>
<tr>
    <td colspan="5">&nbsp;</td>
</tr>
<tr>
    <td align="center"><a href="supermodcp.php?action=torrentmanage"><img src="images/admin/torrents.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("TORRENTS"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=warned"><img src="images/admin/warned_user.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("WARNED_USERS"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=whoswhere"><img src="images/admin/whos_where.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("WHOS_WHERE"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=censor"><img src="images/admin/word_censor.png" border="0" width="118" height="80" alt="" /><br /><?php echo T_("WORD_CENSOR"); ?></a><br /></td>
    <td align="center"><a href="supermodcp.php?action=privacylevel"><img src="images/admin/privacy_level.png" border="0" width="118" height="80" alt="" /><br />Privacy Level<br /></a></td>
</tr>
<tr>
    <td colspan="5">&nbsp;</td>
</tr>
<tr>
    <td align="center"><a href="supermodcp.php?action=pendinginvite"><img src="images/admin/pending_invited_user.png" border="0" width="118" height="80" alt="" /><br />Pending Invited Users<br /></a></td>
    <td align="center"><a href="supermodcp.php?action=invited"><img src="images/admin/invited_user.png" border="0" width="118" height="80" alt="" /><br />Invited Users<br /></a></td>
    <td align="center"><a href="supermodcp.php?action=free"><img src="images/admin/free_leech.png" border="0" width="118" height="80" alt="" /><br />Freeleech Manager<br /></a></td>
</tr>
<tr>
</a></td>
</tr>
</table>

<?php
	end_frame();
}


if (!$action){
	stdhead(T_("supermod_CP"));
	navmenu();
	stdfoot();
}


/////////////////////// NEWS ///////////////////////
if ($action=="news" && $do=="view"){
	stdhead(T_("NEWS_MANAGEMENT"));
	navmenu();

	begin_frame("News");
	echo "<center><a href='supermodcp.php?action=news&amp;do=add'><b>Add News Item</b></a></center><br />";

	$res = SQL_Query_exec("SELECT * FROM news ORDER BY added DESC");
	if (mysqli_num_rows($res) > 0){
		
		while ($arr = mysqli_fetch_assoc($res)) {
			$newsid = $arr["id"];
			$body = format_comment($arr["body"]);
			$title = $arr["title"];
			$userid = $arr["userid"];
			$added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " ago)";

			$res2 = SQL_Query_exec("SELECT username FROM users WHERE id = $userid");
			$arr2 = mysqli_fetch_assoc($res2);
			
			$postername = $arr2["username"];
			
			if ($postername == "")
				$by = "Unknown";
			else
				$by = "<a href='account-details.php?id=$userid'><b>$postername</b></a>";
			
			print("<table border='0' cellspacing='0' cellpadding='0'><tr><td>");
			print("$added&nbsp;---&nbsp;by&nbsp;$by");
			print(" - [<a href='?action=news&amp;do=edit&amp;newsid=$newsid'><b>Edit</b></a>]");
			print(" - [<a href='?action=news&amp;do=delete&amp;newsid=$newsid'><b>Delete</b></a>]");
			print("</td></tr>\n");

			print("<tr valign='top'><td><b>$title</b><br />$body</td></tr></table><br />\n");
		}

	}else{
	 echo "No News Posted";
	}

	end_frame();
	stdfoot();
}

if ($action=="news" && $do=="takeadd"){
	$body = $_POST["body"];
	
	if (!$body)
		show_error_msg(T_("ERROR"),"The news item cannot be empty!",1); 

	$title = $_POST['title'];

	if (!$title)
		show_error_msg(T_("ERROR"),"The news title cannot be empty!",1);
	
	$added = $_POST["added"];

	if (!$added)
		$added = sqlesc(get_date_time());

	SQL_Query_exec("INSERT INTO news (userid, added, body, title) VALUES (".

	$CURUSER['id'] . ", $added, " . sqlesc($body) . ", " . sqlesc($title) . ")");

	if (mysqli_affected_rows($GLOBALS["DBconnector"]) == 1)
		show_error_msg(T_("COMPLETED"),"News item was added successfully.",1);
	else
		show_error_msg(T_("ERROR"),"Unable to add news",1);
}

if ($action=="news" && $do=="add"){
	stdhead(T_("NEWS_MANAGEMENT"));
	navmenu();

	begin_frame("Add News");
	print("<center><form method='post' action='supermodcp.php' name='news'>\n");
	print("<input type='hidden' name='action' value='news' />\n");
	print("<input type='hidden' name='do' value='takeadd' />\n");

	print("<b>News Title:</b> <input type='text' name='title' /><br />\n");

	echo "<br />".textbbcode("news","body")."<br />";

	print("<br /><br /><input type='submit' value='Submit' />\n");

	print("</form><br /><br /></center>\n");
	end_frame();
	stdfoot();
}

if ($action=="news" && $do=="edit"){
	stdhead(T_("NEWS_MANAGEMENT"));
	navmenu();

	$newsid = (int)$_GET["newsid"];
	
	if (!is_valid_id($newsid))
		show_error_msg(T_("ERROR"),"Invalid news item ID.",1);
                                                                                            
	$res = SQL_Query_exec("SELECT * FROM news WHERE id=$newsid");

	if (mysqli_num_rows($res) != 1)
		show_error_msg(T_("ERROR"), "No news item with ID $newsid.",1);

	$arr = mysqli_fetch_assoc($res);

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  		$body = $_POST['body'];

		if ($body == "")
    		show_error_msg(T_("ERROR"), T_("FORUMS_BODY_CANNOT_BE_EMPTY"),1);

		$title = $_POST['title'];

		if ($title == "")
			show_error_msg(T_("ERROR"), "Title cannot be empty!",1);

		$body = sqlesc($body);

		$editedat = sqlesc(get_date_time());

		SQL_Query_exec("UPDATE news SET body=$body, title='$title' WHERE id=$newsid");

		$returnto = $_POST['returnto'];

		if ($returnto != "")
			header("Location: $returnto");
		else
			show_error_msg(T_("COMPLETED"),"News item was edited successfully.", 1);
	} else {
		$returnto = htmlspecialchars($_GET['returnto']);
		begin_frame("Edit News");
		print("<form method='post' action='?action=news&amp;do=edit&amp;newsid=$newsid' name='news'>\n");
		print("<center>");
		print("<input type='hidden' name='returnto' value='$returnto' />\n");
		print("<b>News Title: </b><input type='text' name='title' value=\"".$arr['title']."\" /><br /><br />\n");
		echo "<br />".textbbcode("news","body",$arr["body"])."<br />";
		print("<br /><input type='submit' value='Okay' />\n");
		print("</center>\n");
		print("</form>\n");
	}
	end_frame();
	stdfoot();
}

if ($action=="news" && $do=="delete"){

	$newsid = (int)$_GET["newsid"];
	
	if (!is_valid_id($newsid))
		show_error_msg(T_("ERROR"),"Invalid news item ID",1);

	SQL_Query_exec("DELETE FROM news WHERE id=$newsid");
    SQL_Query_exec("DELETE FROM comments WHERE news = $newsid");
	
	show_error_msg(T_("COMPLETED"),"News item was deleted successfully.",1);
}


////////// categories /////////////////////
if ($action=="categories" && $do=="view"){
	stdhead(T_("Categories Management"));
	navmenu();

	begin_frame(T_("TORRENT_CATEGORIES"));
	echo "<center><a href='supermodcp.php?action=categories&amp;do=add'><b>Add New Category</b></a></center><br />";

	print("<i>Please note that if no image is specified, the category name will be displayed</i><br /><br />");

	echo("<center><table width='95%' class='table_table'>");
	echo("<tr><th width='10' class='table_head'>Sort</th><th class='table_head'>Parent Cat</th><th class='table_head'>Sub Cat</th><th class='table_head'>Image</th><th width='30' class='table_head'></th></tr>");
	$query = "SELECT * FROM categories ORDER BY parent_cat ASC, sort_index ASC";
	$sql = SQL_Query_exec($query);
	while ($row = mysqli_fetch_array($sql)) {
		$id = $row['id'];
		$name = $row['name'];
		$priority = $row['sort_index'];
		$parent = $row['parent_cat'];

		print("<tr><td class='table_col1'>$priority</td><td class='table_col2'>$parent</td><td class='table_col1'>$name</td><td class='table_col2' align='center'>");
		if (isset($row["image"]) && $row["image"] != "")
			print("<img border=\"0\" src=\"" . $site_config['SITEURL'] . "/images/categories/" . $row["image"] . "\" alt=\"" . $row["name"] . "\" />");
		else
			print("-");	
		print("</td><td class='table_col1'><a href='supermodcp.php?action=categories&amp;do=edit&amp;id=$id'>[EDIT]</a> <a href='supermodcp.php?action=categories&amp;do=delete&amp;id=$id'>[DELETE]</a></td></tr>");
	}
	echo("</table></center>");
	end_frame();
	stdfoot();
}


if ($action == "whoswhere")
{
    stdhead("Where are members");
    navmenu();
    
    $res = SQL_Query_exec("SELECT `id`, `username`, `page`, `last_access` FROM `users` WHERE `enabled` = 'yes' AND `status` = 'confirmed' AND `page` != '' ORDER BY `last_access` DESC LIMIT 100");
    
    begin_frame("Last 100 Page Views");
    ?>
    
    <table border="0" cellpadding="4" cellspacing="3" width="80%" align="center" class="table_table">
    <tr>
        <th class="table_head">Username</th>
        <th class="table_head">Page</th>
        <th class="table_head">Accessed</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($res)): ?>
    <tr>
        <td class="table_col1" align="center"><a href="account-details.php?id=<?php echo $row["id"]; ?>"><b><?php echo $row["username"]; ?></b></a></td>
        <td class="table_col2" align="center"><?php echo htmlspecialchars($row["page"]); ?></td>
        <td class="table_col1" align="center"><?php echo utc_to_tz($row["last_access"]); ?></td>
    </tr>
    <?php endwhile; ?>
    </table>
    
    <?php 
    end_frame();
    stdfoot(); 
}

if ($action=="peers"){
	stdhead("Peers List");
	navmenu();

	begin_frame("Peers List");

	$count1 = number_format(get_row_count("peers"));

	print("<center>We have $count1 peers</center><br />");

	$res4 = SQL_Query_exec("SELECT COUNT(*) FROM peers $limit");
	$row4 = mysqli_fetch_array($res4);

	$count = $row4[0];
	$peersperpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($peersperpage, $count, "supermodcp.php?action=peers&amp;");

	print("$pagertop");

	$sql = "SELECT * FROM peers ORDER BY started DESC $limit";
	$result = SQL_Query_exec($sql);

	if( mysqli_num_rows($result) != 0 ) {
		print'<center><table width="100%" border="0" cellspacing="0" cellpadding="3" class="table_table">';
		print'<tr>';
		print'<th class="table_head">User</th>';
		print'<th class="table_head">Torrent</th>';
		print'<th class="table_head">IP</th>';
		print'<th class="table_head">Port</th>';
		print'<th class="table_head">Upl.</th>';
		print'<th class="table_head">Downl.</th>';
		print'<th class="table_head">Peer-ID</th>';
		print'<th class="table_head">Conn.</th>';
		print'<th class="table_head">Seeding</th>';
		print'<th class="table_head">Started</th>';
		print'<th class="table_head">Last<br />Action</th>';
		print'</tr>';

		while($row = mysqli_fetch_assoc($result)) {
			if ($site_config['MEMBERSONLY']) {
				$sql1 = "SELECT id, username FROM users WHERE id = $row[userid]";
				$result1 = SQL_Query_exec($sql1);
				$row1 = mysqli_fetch_assoc($result1);
			}

			if ($row1['username'])
				print'<tr><td class="table_col1"><a href="account-details.php?id=' . $row['userid'] . '">' . $row1['username'] . '</a></td>';
			else
				print'<tr><td class="table_col1">'.$row["ip"].'</td>';

			$sql2 = "SELECT id, name FROM torrents WHERE id = $row[torrent]";
			$result2 = SQL_Query_exec($sql2);

			while ($row2 = mysqli_fetch_assoc($result2)) {

                $smallname = CutName(htmlspecialchars($row2["name"]), 40);
                
				print'<td class="table_col2"><a href="torrents-details.php?id=' . $row['torrent'] . '">' . $smallname . '</a></td>';
				print'<td align="center" class="table_col1">' . $row['ip'] . '</td>';
				print'<td align="center" class="table_col2">' . $row['port'] . '</td>';

				if ($row['uploaded'] < $row['downloaded'])
					print'<td align="center" class="table_col1"><font color="#ff0000">' . mksize($row['uploaded']) . '</font></td>';
				else
					if ($row['uploaded'] == '0')
						print'<td align="center" class="table_col1">' . mksize($row['uploaded']) . '</td>';
					else
						print'<td align="center" class="table_col1"><font color="green">' . mksize($row['uploaded']) . '</font></td>';
				print'<td align="center" class="table_col2">' . mksize($row['downloaded']) . '</td>';
				print'<td align="center" class="table_col1">' . htmlspecialchars($row["peer_id"]) . '</td>';
				if ($row['connectable'] == 'yes')
					print'<td align="center" class="table_col2"><font color="green">' . $row['connectable'] . '</font></td>';
				else
					print'<td align="center" class="table_col2"><font color="#ff0000">' . $row['connectable'] . '</font></td>';
				if ($row['seeder'] == 'yes')
					print'<td align="center" class="table_col1"><font color="green">' . $row['seeder'] . '</font></td>';
				else
					print'<td align="center" class="table_col1"><font color="#ff0000">' . $row['seeder'] . '</font></td>';
				print'<td align="center" class="table_col2">' . utc_to_tz($row['started']) . '</td>';
				print'<td align="center" class="table_col1">' . utc_to_tz($row['last_action']) . '</td>';
				print'</tr>';
			}
		}
		print'</table>';
		print("$pagerbottom</center>");
	}else{
		print'<center><b>No Peers</b></center><br />';
	}
	end_frame();

	stdfoot();
}
                           

if ($action=="lastcomm"){
    
    $count = get_row_count("comments");
    
    list($pagertop, $pagerbottom, $limit) = pager(10, $count, "supermodcp.php?action=lastcomm&amp;");

	stdhead("Latest Comments");
	navmenu();

	begin_frame("Last Comments");

	$res = SQL_Query_exec("SELECT c.id, c.text, c.user, c.torrent, c.news, t.name, n.title, u.username, c.added FROM comments c LEFT JOIN torrents t ON c.torrent = t.id LEFT JOIN news n ON c.news = n.id LEFT JOIN users u ON c.user = u.id ORDER BY c.added DESC $limit");
    
	while ($arr = mysqli_fetch_assoc($res)) {
		$userid = $arr["user"];
		$username = $arr["unome"];
		$data = $arr["added"];
		$tid = $arr["torrent"];
        $nid = $arr["news"];
		$title = ( $arr['title'] ) ? $arr['title'] : $arr['name'];
		$comentario = stripslashes(format_comment($arr["text"]));
		$cid = $arr["id"];    
        
        $type = 'Torrent: <a href="torrents-details.php?id='.$tid.'">'.$title.'</a>';
        
        if ( $nid > 0 )
        {
             $type = 'News: <a href="comments.php?id='.$nid.'">'.$title.'</a>';
        }
                       
		echo "<table align='center' cellpadding='1' cellspacing='0' style='border-collapse: collapse' width='100%' border='1'><tr><td class='ttable_col1' align='center'>".$type."</td></tr><tr><td class='ttable_col2'>".$comentario."</td></tr><tr><td class='ttable_col1' align='center'>Posted in <b>".$data."</b> by <a href=\"account-details.php?id=".$userid."\">".$username."</a><!--  [ <a href=\"edit-comments.php?cid=".$cid."\">edit</a> | <a href=\"edit-comments.php?action=delete&amp;cid=".$cid."\">delete</a> ] --></td></tr></table><br />";
        $rows[] = $arr;
	}
    
    if ($count > 10) echo $pagerbottom;
    
	end_frame();
	stdfoot();
}


 if ($action == "torrentmanage") {
        
        if ($_POST["do"] == "delete") {
            if (!@count($_POST["torrentids"]))
                  show_error_msg("Error", "Nothing selected click <a href='supermodcp.php?action=torrentmanage'>here</a> to go back.", 1);
            foreach ($_POST["torrentids"] as $id) {
                deletetorrent(intval($id));
                write_log("Torrent ID $id was deleted by $CURUSER[username]");
            }
            show_error_msg("Torrents Deleted", "Go <a href='supermodcp.php?action=torrentmanage'>back</a>?", 1);
        }
        
        $search = (!empty($_GET["search"])) ? htmlspecialchars(trim($_GET["search"])) : "";
        
        $where = ($search == "") ? "" : "WHERE name LIKE " . sqlesc("%$search%") . "";

        $count = get_row_count("torrents", $where);
        
        list($pagertop, $pagerbottom, $limit) = pager(25, $count, "supermodcp.php?action=torrentmanage&amp;");
        
        $res = mysqli_query($GLOBALS["DBconnector"],"SELECT id, name, seeders, leechers, visible, banned, external FROM torrents $where ORDER BY name $limit");
        
        stdhead("Torrent Management");
        navmenu();
        
        begin_frame("Torrent Management");

        ?>

        <center>
        <form method='get' action='supermodcp.php'>
        <input type='hidden' name='action' value='torrentmanage' />
        Search: <input type='text' name='search' value='<?php echo $search; ?>' size='30' />
        <input type='submit' value='Search' />
        </form>

        <form id="myform" method='post' action='supermodcp.php?action=torrentmanage'>
        <input type='hidden' name='do' value='delete' />
        <table cellpadding='5' cellspacing='3' width='100%' align='center' class='table_table'>
        <tr>
            <th class='table_head'>Name</th>
            <th class='table_head'>Visible</th>
            <th class='table_head'>Banned</th>
            <th class='table_head'>Seeders</th>
            <th class='table_head'>Leechers</th>
            <th class='table_head'>External</th>
            <th class='table_head'>Edit</th>
            <th class='table_head'><input type='checkbox' name='checkall' onclick='checkAll(this.form.id);' /></th>
        </tr>
        
        <?php while ($row = mysqli_fetch_array($res)) { ?>
        
        <tr>
            <td class='table_col1'><a href='torrents-details.php?id=<?php echo $row["id"]; ?>'><?php echo CutName(htmlspecialchars($row["name"]), 40); ?></a></td>
            <td class='table_col2'><?php echo $row["visible"]; ?></td>
            <td class='table_col1'><?php echo $row["banned"]; ?></td>
            <td class='table_col2'><?php echo number_format($row["seeders"]); ?></td>
            <td class='table_col1'><?php echo number_format($row["leechers"]); ?></td>
            <td class='table_col2'><?php echo $row["external"]; ?></td>
            <td class='table_col1'><a href='torrents-edit.php?id=<?php echo $row["id"]; ?>&amp;returnto=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>'>Edit</a></td>
            <td class='table_col2' align='center'><input type='checkbox' name='torrentids[]' value='<?php echo $row["id"]; ?>' /></td>    
        </tr>
        
        <?php } ?>
        
        </table>
        <br />
        <input type='submit' value='Delete checked' />
        </form>
        <br />
        <?php echo $pagerbottom; ?>
        </center>
        
        <?php
        
        end_frame();
        stdfoot();
        
    }

if ($action == "cheats") {
	stdhead("Possible Cheater Detection");
	navmenu();

    $megabts = (int) $_POST['megabts'];
    $daysago = (int) $_POST['daysago'];

	if ($daysago && $megabts){

		$timeago = 84600 * $daysago; //last 7 days
		$bytesover = 1048576 * $megabts; //over 500MB Upped

		$result = SQL_Query_exec("select * FROM users WHERE UNIX_TIMESTAMP('" . get_date_time() . "') - UNIX_TIMESTAMP(added) < '$timeago' AND status='confirmed' AND uploaded > '$bytesover' ORDER BY uploaded DESC "); 
		$num = mysqli_num_rows($result); // how many uploaders

		begin_frame("Possible Cheater Detection");
		echo "<p>" . $num . " Users with found over last ".$daysago." days with more than ".$megabts." MB (".$bytesover.") Bytes Uploaded.</p>";

		$zerofix = $num - 1; // remove one row because mysql starts at zero

		if ($num > 0){
		echo "<table align='center' class='table_table'>";
		echo "<tr>";
		 echo "<th class='table_head'>No.</th>";
		 echo "<th class='table_head'>" .T_("USERNAME"). "</th>";
		 echo "<th class='table_head'>" .T_("UPLOADED"). "</th>";
		 echo "<th class='table_head'>" .T_("DOWNLOADED"). "</th>";
		 echo "<th class='table_head'>" .T_("RATIO"). "</th>";
		 echo "<th class='table_head'>" .T_("TORRENTS_POSTED"). "</th>";
		 echo "<th class='table_head'>AVG Daily Upload</th>";
		 echo "<th class='table_head'>" .T_("ACCOUNT_SEND_MSG"). "</th>";
		 echo "<th class='table_head'>Joined</th>";
		echo "</tr>";

		for ($i = 0; $i <= $zerofix; $i++) {
			 $id = mysqli_result($result, $i, "id");
			 $username = mysqli_result($result, $i, "username");
			 $added = mysqli_result($result, $i, "added");
			 $uploaded = mysqli_result($result, $i, "uploaded");
			 $downloaded = mysqli_result($result, $i, "downloaded");
			 $donated = mysqli_result($result, $i, "donated");
			 $warned = mysqli_result($result, $i, "warned");
			 $joindate = "" . get_elapsed_time(sql_timestamp_to_unix_timestamp($added)) . " ago";
			 $upperquery = "SELECT added FROM torrents WHERE owner = $id";
			 $upperresult = SQL_Query_exec($upperquery);
			 $seconds = mkprettytime(utc_to_tz_time() - utc_to_tz_time($added));
			 $days = explode("d ", $seconds);

			 if(sizeof($days) > 1) {
				 $dayUpload  = $uploaded / $days[0];
				 $dayDownload = $downloaded / $days[0];
			}
		 
		  $torrentinfo = mysqli_fetch_array($upperresult);
		 
		  $numtorrents = mysqli_num_rows($upperresult);
		   
		  if ($downloaded > 0){
		   $ratio = $uploaded / $downloaded;
		   $ratio = number_format($ratio, 3);
		   $color = get_ratio_color($ratio);
		   if ($color)
		   $ratio = "<font color='$color'>$ratio</font>";
		   }
		  else
		   if ($uploaded > 0)
			$ratio = "Inf.";
		   else
			$ratio = "---";
		  
		 
		 $counter = $i + 1;
		 
		 echo "<tr>";
		  echo "<td align='center class='table_col1'>$counter.</td>";
		  echo "<td class='table_col2'><a href='account-details.php?id=$id'>$username</a></td>";
		  echo "<td class='table_col1'>" . mksize($uploaded). "</td>";
		  echo "<td class='table_col2'>" . mksize($downloaded) . "</td>";
		  echo "<td class='table_col1'>$ratio</td>";
		  if ($numtorrents == 0) echo "<td class='table_col2'><font color='red'>$numtorrents torrents</font></td>";
		  else echo "<td class=table_col2>$numtorrents torrents</td>";

		  echo "<td class='table_col1'>" . mksize($dayUpload) . "</td>";

		  echo "<td align='center' class='table_col2'><a href='mailbox.php?compose&amp;id=$id'>PM</a></td>";
		  echo "<td class='table_col1'>" . $joindate . "</td>";
		 echo "</tr>";

		 
		 }
		echo "</table><br /><br />";
		end_frame();
		}

		if ($num == 0)
		{
		end_frame();
		}

	}else{
	begin_frame("Possible Cheater Detection");?>
	<center><form action='supermodcp.php?action=cheats' method='post'>
		Number of days joined: <input type='text' size='4' maxlength='4' name='daysago' /> Days<br /><br />
		MB Uploaded: <input type='text' size='6' maxlength='6' name='megabts' /> MB<br />
		<input type='submit' value='Submit' />
		</form></center><?php
	end_frame();
	}
	stdfoot();
}


if ($action=="polls" && $do=="view"){
	stdhead(T_("POLLS_MANAGEMENT"));
	navmenu();
	begin_frame(T_("POLLS_MANAGEMENT"));

	echo "<center><a href='supermodcp.php?action=polls&amp;do=add'>Add New Poll</a>";
	echo "<a href='supermodcp.php?action=polls&amp;do=results'>View Poll Results</a></center>";

	echo "<br /><br /><b>Polls</b> (Top poll is current)<br />";

	$query = SQL_Query_exec("SELECT id,question,added FROM polls ORDER BY added DESC");

	while($row = mysqli_fetch_assoc($query)){
		echo "<a href='supermodcp.php?action=polls&amp;do=add&amp;subact=edit&amp;pollid=$row[id]'>".stripslashes($row["question"])."</a> - ".utc_to_tz($row['added'])." - <a href='supermodcp.php?action=polls&amp;do=delete&amp;id=$row[id]'>Delete</a><br />\n\n";
	}

	end_frame();

	stdfoot();
}


/////////////
if ($action=="polls" && $do=="results"){
	stdhead("Polls");
	navmenu();
	begin_frame("Results");
	echo "<table class=\"table_table\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" width=\"95%\">";
	echo '<tr>';
	echo '<th class="table_head">Username</th>';
	echo '<th class="table_head">Question</th>';
	echo '<th class="table_head">Voted</th>';
	echo '</tr>';

	$poll = SQL_Query_exec("SELECT * FROM pollanswers ORDER BY pollid DESC");

	while ($res = mysqli_fetch_assoc($poll)) {
		$user = mysqli_fetch_assoc(SQL_Query_exec("SELECT username,id FROM users WHERE id = '".$res['userid']."'"));
		$option = "option".$res["selection"];
		if ($res["selection"] < 255) {
			$vote = mysqli_fetch_assoc(SQL_Query_exec("SELECT ".$option." FROM polls WHERE id = '".$res['pollid']."'"));
		} else {
			$vote["option255"] = "Blank vote";
		}
		$sond = mysqli_fetch_assoc(SQL_Query_exec("SELECT question FROM polls WHERE id = '".$res['pollid']."'"));
		
		echo '<tr>';
		echo '<td class="table_col1" align="left"><b>';
		echo '<a href="account-details.php?id='.$user["id"].'">';
		echo '&nbsp;&nbsp;'.$user['username'];
		echo '</a>';
		echo '</b></td>';
		echo '<td class="table_col2" align="center">';
		echo '&nbsp;&nbsp;'.$sond['question'];
		echo '</td>';
		echo '<td class="table_col1" align="center">';
		echo $vote["$option"];
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
	end_frame();
	stdfoot();
}


if ($action=="polls" && $do=="delete"){
	$id = (int)$_GET["id"];
	
	if (!is_valid_id($id))
		show_error_msg(T_("ERROR"),"Invalid news item ID",1);

	SQL_Query_exec("DELETE FROM polls WHERE id=$id");
	SQL_Query_exec("DELETE FROM pollanswers WHERE  pollid=$id");
	
	show_error_msg(T_("COMPLETED"),"Poll and answers deleted",1);
}

if ($action=="polls" && $do=="add"){
	stdhead("Polls");
	navmenu();

	$pollid = (int)$_GET["pollid"];

	if ($_GET["subact"] == "edit"){
		$res = SQL_Query_exec("SELECT * FROM polls WHERE id = $pollid");
		$poll = mysqli_fetch_array($res);
	}
                                
	begin_frame("Polls");
	?>                                                
    <form method="post" action="supermodcp.php?action=polls&amp;do=save">
	<table border="0" cellspacing="0" cellpadding="3">
    <tr><td>Question <font color="#ff0000">*</font></td><td align="left"><input name="question" size="60" maxlength="255" value="<?php echo $poll['question']; ?>" /></td></tr>
    <tr><td>Option 1 <font color="#ff0000">*</font></td><td align="left"><input name="option0" size="60" maxlength="40" value="<?php echo $poll['option0']; ?>" /><br /></td></tr>
    <tr><td>Option 2 <font color="#ff0000">*</font></td><td align="left"><input name="option1" size="60" maxlength="40" value="<?php echo $poll['option1']; ?>" /><br /></td></tr>
    <tr><td>Option 3</td><td align="left"><input name="option2" size="60" maxlength="40" value="<?php echo $poll['option2']; ?>" /><br /></td></tr>
    <tr><td>Option 4</td><td align="left"><input name="option3" size="60" maxlength="40" value="<?php echo $poll['option3']; ?>" /><br /></td></tr>
    <tr><td>Option 5</td><td align="left"><input name="option4" size="60" maxlength="40" value="<?php echo $poll['option4']; ?>" /><br /></td></tr>
    <tr><td>Option 6</td><td align="left"><input name="option5" size="60" maxlength="40" value="<?php echo $poll['option5']; ?>" /><br /></td></tr>
    <tr><td>Option 7</td><td align="left"><input name="option6" size="60" maxlength="40" value="<?php echo $poll['option6']; ?>" /><br /></td></tr>
    <tr><td>Option 8</td><td align="left"><input name="option7" size="60" maxlength="40" value="<?php echo $poll['option7']; ?>" /><br /></td></tr>
    <tr><td>Option 9</td><td align="left"><input name="option8" size="60" maxlength="40" value="<?php echo $poll['option8']; ?>" /><br /></td></tr>
    <tr><td>Option 10</td><td align="left"><input name="option9" size="60" maxlength="40" value="<?php echo $poll['option9']; ?>" /><br /></td></tr>
    <tr><td>Option 11</td><td align="left"><input name="option10" size="60" maxlength="40" value="<?php echo $poll['option10']; ?>" /><br /></td></tr>
    <tr><td>Option 12</td><td align="left"><input name="option11" size="60" maxlength="40" value="<?php echo $poll['option11']; ?>" /><br /></td></tr>
    <tr><td>Option 13</td><td align="left"><input name="option12" size="60" maxlength="40" value="<?php echo $poll['option12']; ?>" /><br /></td></tr>
    <tr><td>Option 14</td><td align="left"><input name="option13" size="60" maxlength="40" value="<?php echo $poll['option13']; ?>" /><br /></td></tr>
    <tr><td>Option 15</td><td align="left"><input name="option14" size="60" maxlength="40" value="<?php echo $poll['option14']; ?>" /><br /></td></tr>
    <tr><td>Option 16</td><td align="left"><input name="option15" size="60" maxlength="40" value="<?php echo $poll['option15']; ?>" /><br /></td></tr>
    <tr><td>Option 17</td><td align="left"><input name="option16" size="60" maxlength="40" value="<?php echo $poll['option16']; ?>" /><br /></td></tr>
    <tr><td>Option 18</td><td align="left"><input name="option17" size="60" maxlength="40" value="<?php echo $poll['option17']; ?>" /><br /></td></tr>
    <tr><td>Option 19</td><td align="left"><input name="option18" size="60" maxlength="40" value="<?php echo $poll['option18']; ?>" /><br /></td></tr>
    <tr><td>Option 20</td><td align="left"><input name="option19" size="60" maxlength="40" value="<?php echo $poll['option19']; ?>" /><br /></td></tr>
    <tr><td>Sort</td><td>
    <input type="radio" name="sort" value="yes" <?php echo $poll["sort"] != "no" ? " checked='checked'" : "" ?> />Yes
    <input type="radio" name="sort" value="no" <?php echo $poll["sort"] == "no" ? " checked='checked'" : "" ?> /> No
    </td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="<?php echo $pollid ? "Edit poll": "Create poll"; ?>" /></td></tr>
    </table>
    <p><font color="#ff0000">*</font> required</p>
    <input type="hidden" name="pollid" value="<?php echo $poll["id"]?>" />
    <input type="hidden" name="subact" value="<?php echo $pollid?'edit':'create'?>" />
    </form>
	<?php
	end_frame();
	stdfoot();
}

if ($action=="polls" && $do=="save"){

	$subact = $_POST["subact"];
	$pollid = (int)$_POST["pollid"];

	$question = $_POST["question"];
	$option0 = $_POST["option0"];
	$option1 = $_POST["option1"];
	$option2 = $_POST["option2"];
	$option3 = $_POST["option3"];
	$option4 = $_POST["option4"];
	$option5 = $_POST["option5"];
	$option6 = $_POST["option6"];
	$option7 = $_POST["option7"];
	$option8 = $_POST["option8"];
	$option9 = $_POST["option9"];
	$option10 = $_POST["option10"];
	$option11 = $_POST["option11"];
	$option12 = $_POST["option12"];
	$option13 = $_POST["option13"];
	$option14 = $_POST["option14"];
	$option15 = $_POST["option15"];
	$option16 = $_POST["option16"];
	$option17 = $_POST["option17"];
	$option18 = $_POST["option18"];
	$option19 = $_POST["option19"];
	$sort = (int)$_POST["sort"];

	if (!$question || !$option0 || !$option1)
		show_error_msg(T_("ERROR"), T_("MISSING_FORM_DATA")."!", 1);

	if ($subact == "edit"){

		if (!is_valid_id($pollid))
			show_error_msg(T_("ERROR"),T_("INVALID_ID"),1);

		SQL_Query_exec("UPDATE polls SET " .
		"question = " . sqlesc($question) . ", " .
		"option0 = " . sqlesc($option0) . ", " .
		"option1 = " . sqlesc($option1) . ", " .
		"option2 = " . sqlesc($option2) . ", " .
		"option3 = " . sqlesc($option3) . ", " .
		"option4 = " . sqlesc($option4) . ", " .
		"option5 = " . sqlesc($option5) . ", " .
		"option6 = " . sqlesc($option6) . ", " .
		"option7 = " . sqlesc($option7) . ", " .
		"option8 = " . sqlesc($option8) . ", " .
		"option9 = " . sqlesc($option9) . ", " .
		"option10 = " . sqlesc($option10) . ", " .
		"option11 = " . sqlesc($option11) . ", " .
		"option12 = " . sqlesc($option12) . ", " .
		"option13 = " . sqlesc($option13) . ", " .
		"option14 = " . sqlesc($option14) . ", " .
		"option15 = " . sqlesc($option15) . ", " .
		"option16 = " . sqlesc($option16) . ", " .
		"option17 = " . sqlesc($option17) . ", " .
		"option18 = " . sqlesc($option18) . ", " .
		"option19 = " . sqlesc($option19) . ", " .
		"sort = " . sqlesc($sort) . " " .
    "WHERE id = $pollid");
	}else{
  	SQL_Query_exec("INSERT INTO polls VALUES(0" .
		", '" . get_date_time() . "'" .
    ", " . sqlesc($question) .
    ", " . sqlesc($option0) .
    ", " . sqlesc($option1) .
    ", " . sqlesc($option2) .
    ", " . sqlesc($option3) .
    ", " . sqlesc($option4) .
    ", " . sqlesc($option5) .
    ", " . sqlesc($option6) .
    ", " . sqlesc($option7) .
    ", " . sqlesc($option8) .
    ", " . sqlesc($option9) .
 		", " . sqlesc($option10) .
		", " . sqlesc($option11) .
		", " . sqlesc($option12) .
		", " . sqlesc($option13) .
		", " . sqlesc($option14) .
		", " . sqlesc($option15) .
		", " . sqlesc($option16) .
		", " . sqlesc($option17) .
		", " . sqlesc($option18) .
		", " . sqlesc($option19) . 
    ", " . sqlesc($sort) .
  	")");
	}

	show_error_msg("OK","Poll Updates ".T_("COMPLETE"), 1);
}


if ($action=="avatars"){
	stdhead("Avatar Log");
	navmenu();

	begin_frame("Avatar Log");

	$query = SQL_Query_exec("SELECT count(*) FROM users WHERE enabled='yes' AND avatar !=''");
	$count = mysqli_fetch_row($query);
	$count = $count[0];

	list($pagertop, $pagerbottom, $limit) = pager(50, $count, 'supermodcp.php?action=avatars&amp;');
	echo ($pagertop);
	?>
	<table border="0" class="table_table" align="center">
	<tr>
	<th class="table_head"><?php echo T_("USER")?></th>
	<th class="table_head">Avatar</th>
	</tr><?php

	$query = "SELECT username, id, avatar FROM users WHERE enabled='yes' AND avatar !='' $limit";
	$res = SQL_Query_exec($query);

	while($arr = mysqli_fetch_array($res)){
			echo("<tr><td class='table_col1'><b><a href=\"account-details.php?id=" . $arr['id'] . "\">" . $arr['username'] . "</a></b></td><td class='table_col2'>");

			if (!$arr['avatar'])
				echo "<img width=\"80\" src='images/default_avatar.gif' alt='' /></td></tr>";
			else
				echo "<img width=\"80\" src=\"".htmlspecialchars($arr["avatar"])."\" alt='' /></td></tr>";
	}
	?>
	</table>
	<?php
	echo ($pagerbottom);
	end_frame();
	stdfoot();
}

if ($action=="freetorrents"){
    
    /*
    * Todo:
    *  Optimize Query show freeleech ONLY!
    */
    
	stdhead("Free Leech ".T_("TORRENT_MANAGEMENT"));
	navmenu();

	$search = trim($search);

	if ($search != '' ){
		$whereand = "AND name LIKE " . sqlesc("%$search%") . "";
	}

	$res2 = SQL_Query_exec("SELECT COUNT(*) FROM torrents WHERE freeleech='1' $whereand");
	$row = mysqli_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "supermodcp.php?action=freetorrents&amp;");

	begin_frame("Free Leech ".T_("TORRENT_MANAGEMENT")."");

	print("<center><form method='get' action='?'>\n");
	print("<input type='hidden' name='action' value='freetorrents' />\n");
	print(T_("SEARCH").": <input type='text' size='30' name='search' />\n");
	print("<input type='submit' value='Search' />\n");
	print("</form></center>\n");

	echo $pagertop;
	?>
	<table align="center" cellpadding="0" cellspacing="0" class="table_table" width="100%" border="0">
	<tr>
	<th class="table_head">Name</th>
	<th class="table_head">Visible</th>
	<th class="table_head">Banned</th>
	<th class="table_head">Seeders</th>
	<th class="table_head">Leechers</th>
	<th class="table_head">Edit?</th>
	</tr>
	<?php
	$rqq = "SELECT id, name, seeders, leechers, visible, banned FROM torrents WHERE freeleech='1' $whereand ORDER BY name $limit";
	$resqq = SQL_Query_exec($rqq);

	while ($row = mysqli_fetch_array($resqq)){
		
		$char1 = 35; //cut name length 
		$smallname = CutName(htmlspecialchars($row["name"]), $char1);

		echo "<tr><td class='table_col1'>" . $smallname . "</td><td class='table_col2'>$row[visible]</td><td class='table_col1'>$row[banned]</td><td class='table_col2'>".number_format($row["seeders"])."</td><td class='table_col1'>".number_format($row["leechers"])."</td><td class='table_col2'><a href=\"torrents-edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\"><font size='1' face='verdana'>EDIT</font></a></td></tr>\n";
	}

	echo "</table>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}

if ($action=="bannedtorrents"){
	stdhead("Banned Torrents");
	navmenu();
		
	$res2 = SQL_Query_exec("SELECT COUNT(*) FROM torrents WHERE banned='yes'");
	$row = mysqli_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "supermodcp.php?action=bannedtorrents&amp;");

	begin_frame("Banned ".T_("TORRENT_MANAGEMENT"));

	print("<center><form method='get' action='?'>\n");
	print("<input type='hidden' name='action' value='bannedtorrents' />\n");
	print(T_("SEARCH").": <input type='text' size='30' name='search' />\n");
	print("<input type='submit' value='Search' />\n");
	print("</form></center>\n");

	echo $pagertop;
	?>
	<center><table align="center" cellpadding="0" cellspacing="0" class="table_table" width="100%" border="0">
	<tr>
	<th class="table_head">Name</th>
	<th class="table_head">Visible</th>
	<th class="table_head">Seeders</th>
	<th class="table_head">Leechers</th>
	<th class="table_head">External?</th>
	<th class="table_head">Edit?</th>
	</tr>
	<?php
	$rqq = "SELECT id, name, seeders, leechers, visible, banned, external FROM torrents WHERE banned='yes' ORDER BY name";
	$resqq = SQL_Query_exec($rqq);

	while ($row = mysqli_fetch_array($resqq)){

		$char1 = 35; //cut name length 
		$smallname = CutName(htmlspecialchars($row["name"]), $char1);

		echo "<tr><td class='table_col1'>" . $smallname . "</td><td class='table_col2'>$row[visible]</td><td class='table_col1'>".number_format($row["seeders"])."</td><td class='table_col2'>".number_format($row["leechers"])."</td><td class='table_col1'>$row[external]</td><td class='table_col2'><a href=\"torrents-edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\"><font size='1' face='verdana'>EDIT</font></a></td></tr>\n";
	}

	echo "</table></center>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}


if ($action=="rules" && $do=="view"){
	stdhead(T_("SITE_RULES_EDITOR"));
	navmenu();

	begin_frame(T_("SITE_RULES_EDITOR"));

	$res = SQL_Query_exec("SELECT * FROM rules ORDER BY id");

	print("<center><a href='supermodcp.php?action=rules&amp;do=addsect'>Add New Rules Section</a></center><br />\n");

	while ($arr=mysqli_fetch_assoc($res)){
		
        
        #begin_frame($arr[title]);
		print("<div class='f-border'>");
        print("<div class='f-cat'>".$arr["title"]."</div>");
        print("<div>");
        print("<form method='post' action='supermodcp.php?action=rules&amp;do=edit'><table width='100%' border='0'>");
		print("<tr><td width='100%'>");
		print(format_comment($arr["text"]));
		print("</td></tr><tr><td><input type='hidden' value='$arr[id]' name='id' /><input type='submit' value='Edit' /></td></tr></table></form>");
		print("</div>");
        print("</div>");
        print("<br />");
        #end_frame();
	}
	end_frame();
	stdfoot();
}

if ($action=="rules" && $do=="edit"){

	if ($_GET["save"]=="1"){
		$id = (int)$_POST["id"];
		$title = sqlesc($_POST["title"]);
		$text = sqlesc($_POST["text"]);
		$public = sqlesc($_POST["public"]);
		$class = sqlesc($_POST["class"]);
		SQL_Query_exec("update rules set title=$title, text=$text, public=$public, class=$class where id=$id");
		write_log("Rules have been changed by ($CURUSER[username])");
		show_error_msg(T_("COMPLETE"), "Rules edited ok<br /><br /><a href='supermodcp.php?action=rules&amp;do=view'>Back To Rules</a>",1);
		die;
	}


	stdhead(T_("SITE_RULES_EDITOR"));
	navmenu();
	
	begin_frame("Edit Rule Section");
	$id = (int)$_POST["id"];
	$res = @mysqli_fetch_array(@SQL_Query_exec("select * from rules where id='$id'"));

	print("<form method=\"post\" action=\"supermodcp.php?action=rules&amp;do=edit&amp;save=1\">");
	print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
	print("<tr><td>Section Title:</td><td><input style=\"width: 400px;\" type=\"text\" name=\"title\" value=\"$res[title]\" /></td></tr>\n");
	print("<tr><td style=\"vertical-align: top;\">Rules:</td><td><textarea cols=\"60\" rows=\"15\" name=\"text\">" . stripslashes($res["text"]) . "</textarea><br />NOTE: Remember that BB can be used (NO HTML)</td></tr>\n");

	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"radio\" name='public' value=\"yes\" ".($res["public"]=="yes"?"checked='checked'":"")." />For everybody<input type=\"radio\" name='public' value=\"no\" ".($res["public"]=="no"?"checked='checked'":"")." />Members Only (Min User Class: <input type=\"text\" name='class' value=\"$res[class]\" size=\"1\" />)</td></tr>\n");
	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"hidden\" value=\"$res[id]\" name=\"id\" /><input type=\"submit\" value=\"Save\" style=\"width: 60px;\" /></td></tr>\n");
	print("</table></form>");
	end_frame();
	stdfoot();
}

if ($action=="rules" && $do=="addsect"){

	if ($_GET["save"]=="1"){
		$title = sqlesc($_POST["title"]);
		$text = sqlesc($_POST["text"]);
		$public = sqlesc($_POST["public"]);
		$class = sqlesc($_POST["class"]);
		SQL_Query_exec("insert into rules (title, text, public, class) values($title, $text, $public, $class)");
		show_error_msg(T_("COMPLETE"), "New Section Added<br /><br /><a href='supermodcp.php?action=rules&amp;do=view'>Back To Rules</a>",1);
		die();
	}
	stdhead(T_("SITE_RULES_EDITOR"));
	navmenu();
	begin_frame(T_("ADD_NEW_RULES_SECTION"));
	print("<form method=\"post\" action=\"supermodcp.php?action=rules&amp;do=addsect&amp;save=1\">");
	print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
	print("<tr><td>Section Title:</td><td><input style=\"width: 400px;\" type=\"text\" name=\"title\" /></td></tr>\n");
	print("<tr><td style=\"vertical-align: top;\">Rules:</td><td><textarea cols=\"60\" rows=\"15\" name=\"text\"></textarea><br />\n");
	print("<br />NOTE: Remember that BB can be used (NO HTML)</td></tr>\n");

	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"radio\" name='public' value=\"yes\" checked=\"checked\" />For everybody<input type=\"radio\" name='public' value=\"no\" />&nbsp;Members Only - (Min User Class: <input type=\"text\" name='class' value=\"0\" size=\"1\" />)</td></tr>\n");
	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Add\" style=\"width: 60px;\" /></td></tr>\n");
	print("</table></form>");
	end_frame();
	stdfoot();
}

if ($action == "reports" && $do == "view") {

      $page = 'supermodcp.php?action=reports&amp;do=view&amp;';
      $pager[] = substr($page, 0, -4);

      if ($_POST["mark"])
      {
          if (!@count($_POST["reports"])) show_error_msg("Error", "Nothing selected to mark.", 1);
          $ids = array_map("intval", $_POST["reports"]);
          $ids = implode(",", $ids);
          SQL_Query_exec("UPDATE reports SET complete = '1', dealtwith = '1', dealtby = '$CURUSER[id]' WHERE id IN ($ids)");
          header("Refresh: 2; url=supermodcp.php?action=reports&do=view");
          show_error_msg("Success", "Entries marked completed.", 1);
      }
      
      if ($_POST["del"])
      {
          if (!@count($_POST["reports"])) show_error_msg("Error", "Nothing selected to delete.", 1);
          $ids = array_map("intval", $_POST["reports"]);
          $ids = implode(",", $ids);
          SQL_Query_exec("DELETE FROM reports WHERE id IN ($ids)");
          header("Refresh: 2; url=supermodcp.php?action=reports&do=view");
          show_error_msg("Success", "Entries marked deleted.", 1);
      }
      
      $where = array();
      
      switch ( $_GET["type"] )
      {
          case "user":
            $where[] = "type = 'user'";
            $pager[] = "type=user";    
            break;
          case "torrent":
            $where[] = "type = 'torrent'";
            $pager[] = "type=torrent";
            break;
          case "comment":
            $where[] = "type = 'comment'";
            $pager[] = "type=comment";  
            break;
          case "forum":
            $where[] = "type = 'forum'";
            $pager[] = "type=forum";  
            break;
          default:
            $where = null;
            break;
      }
  
      switch ( $_GET["completed"] )
      {
          case 1:
            $where[] = "complete = '1'";
            $pager[] = "complete=1";
            break;
          default:
            $where[] = "complete = '0'";
            $pager[] = "complete=0";
            break;
      }
      
      $where = implode(" AND ", $where);
      $pager = implode("&amp;", $pager);
                                
      $num = get_row_count("reports", "WHERE $where");
      
      list($pagertop, $pagerbottom, $limit) = pager(25, $num, "$pager&amp;");
      
      $res = SQL_Query_exec("SELECT reports.id, reports.dealtwith, reports.dealtby, reports.addedby, reports.votedfor, reports.votedfor_xtra, reports.reason, reports.type, users.username, reports.complete FROM `reports` INNER JOIN users ON reports.addedby = users.id WHERE $where ORDER BY reports.id DESC $limit");
      
      stdhead("Reported Items");
      navmenu();    

      begin_frame("Reported Items");
      ?>
        
      <table align="right">
      <tr>
          <td valign="top">
          <form id='sort' action=''>
          <b>Type:</b>
          <select name="type" onchange="window.location='<?php echo $page; ?>type='+this.options[this.selectedIndex].value+'&amp;completed='+document.forms['sort'].completed.options[document.forms['sort'].completed.selectedIndex].value">
          <option value="">All Types</option>
          <option value="user" <?php echo ($_GET['type'] == "user" ? " selected='selected'" : ""); ?>>Users</option>
          <option value="torrent" <?php echo ($_GET['type'] == "torrent" ? " selected='selected'" : ""); ?>>Torrents</option>
          <option value="comment" <?php echo ($_GET['type'] == "comment" ? " selected='selected'" : ""); ?>>Comments</option>
          <option value="forum" <?php echo ($_GET['type'] == "forum" ? " selected='selected'" : ""); ?>>Forum</option>
          </select>
          <b>Completed:</b>
          <select name="completed" onchange="window.location='<?php echo $page; ?>completed='+this.options[this.selectedIndex].value+'&amp;type='+document.forms['sort'].type.options[document.forms['sort'].type.selectedIndex].value">
          <option value="0" <?php echo ($_GET['completed'] == 0 ? " selected='selected'" : ""); ?>>No</option>
          <option value="1" <?php echo ($_GET['completed'] == 1 ? " selected='selected'" : ""); ?>>Yes</option>
          </select>
          </form>     
          </td>
      </tr>
      </table>
      <br />
      <br />
      
      <form id="reports" method="post" action="supermodcp.php?action=reports&amp;do=view">
      <table cellpadding="3" cellspacing="3" class="table_table" width="100%" align="center">
      <tr>
          <th class="table_head">Reported By</th>
          <th class="table_head">Subject</th>
          <th class="table_head">Type</th>
          <th class="table_head">Reason</th>
          <th class="table_head">Dealt With</th>
          <th class="table_head"><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);" /></th>
      </tr>
      
      <?php if (!mysqli_num_rows($res)): ?>
      <tr>
          <td class="table_col1" colspan="6" align="center">No reports found.</td>
      </tr>
      <?php endif; ?>
      
      <?php
      while ($row = mysqli_fetch_assoc($res)):  
          
      
      $dealtwith = '<b>No</b>';
      if ($row["dealtby"] > 0)
      {
          $q = SQL_Query_exec("SELECT username FROM users WHERE id = '$row[dealtby]'");
          $r = mysqli_fetch_assoc($q);
          $dealtwith = 'By <a href="account-details.php?id='.$row['dealtby'].'">'.$r['username'].'</a>';
      }    
      
      switch ( $row["type"] )
      {
          case "user":
            $q = SQL_Query_exec("SELECT username FROM users WHERE id = '$row[votedfor]'");
            break;
          case "torrent":
            $q = SQL_Query_exec("SELECT name FROM torrents WHERE id = '$row[votedfor]'");
            break;
          case "comment":
            $q = SQL_Query_exec("SELECT text, news, torrent FROM comments WHERE id = '$row[votedfor]'");
            break;
          case "forum":
            $q = SQL_Query_exec("SELECT subject FROM forum_topics WHERE id = '$row[votedfor]'");
            break;
      }
      
      $r = mysqli_fetch_row($q);
      
      if ($row["type"] == "user")
          $link = "account-details.php?id=$row[votedfor]";
      else if ($row["type"] == "torrent")
          $link = "torrents-details.php?id=$row[votedfor]";
      else if ($row["type"] == "comment")
          $link = "comments.php?type=".($r[1] > 0 ? "news" : "torrent")."&amp;id=".($r[1] > 0 ? $r[1] : $r[2])."#comment$row[votedfor]";
      else if ($row["type"] == "forum")
          $link = "forums.php?action=viewtopic&amp;topicid=$row[votedfor]&amp;page=last#post$row[votedfor_xtra]";
      ?>
      <tr>
          <td class="table_col1" align="center" width="10%"><a href="account-details.php?id=<?php echo $row['addedby']; ?>"><?php echo $row['username']; ?></a></td>
          <td class="table_col2" align="center" width="15%"><a href="<?php echo $link; ?>"><?php echo CutName($r[0], 40); ?></a></td>
          <td class="table_col1" align="center" width="10%"><?php echo $row['type']; ?></td>
          <td class="table_col2" align="center" width="50%"><?php echo htmlspecialchars($row['reason']); ?></td>
          <td class="table_col1" align="center" width="10%"><?php echo $dealtwith; ?></td>
          <td class="table_col2" align="center" width="5%"><input type="checkbox" name="reports[]" value="<?php echo $row["id"]; ?>" /></td>
      </tr>
      <?php endwhile; ?>
      
      <tr>
          <td colspan="6" align="right">
          <?php if ($_GET["completed"] != 1): ?>
          <input type="submit" name="mark" value="Mark Completed" />
          <?php endif; ?>
          <input type="submit" name="del" value="Delete" />
          </td>
      </tr>
      </table>
      </form>
  
      <?php
    
      print $pagerbottom;
      
      end_frame();
      stdfoot();
  }
  
#======================================================================#
# Warned Users - Updated by djhowarth (11-12-2011)
#======================================================================#
if ($action == "warned")
{
    if ($do == "delete") 
    {
        if ($_POST["removeall"])
        {
            $res = SQL_Query_exec("SELECT `id` FROM `users` WHERE `enabled` = 'yes' AND `status` = 'confirmed' AND `warned` = 'yes'");
            while ($row = mysqli_fetch_assoc($res))
            {
                SQL_Query_exec("DELETE FROM `warnings` WHERE `active` = 'yes' AND `userid` = '$row[id]'");
                SQL_Query_exec("UPDATE `users` SET `warned` = 'no' WHERE `id` = '$row[id]'");
            }
        }
        else
        {
            if (!@count($_POST['warned'])) show_error_msg("Error", "Nothing selected", 1);
            $ids = array_map("intval", $_POST["warned"]);
            $ids = implode(", ", $ids);
                
            SQL_Query_exec("DELETE FROM `warnings` WHERE `active` = 'yes' AND `userid` IN ($ids))");
            SQL_Query_exec("UPDATE `users` SET `warned` = 'no' WHERE `id` IN ($ids)");
        }
        
        
        autolink("supermodcp.php?action=warned", "Entries Confirmed");
    }
    
    $count = get_row_count("users", "WHERE enabled = 'yes' AND status = 'confirmed' AND warned = 'yes'");
    
    list($pagertop, $pagerbottom, $limit) = pager(25, $count, 'supermodcp.php?action=warned&amp;');
    
    $res = SQL_Query_exec("SELECT `id`, `username`, `class`, `added`, `last_access` FROM `users` WHERE `enabled` = 'yes' AND `status` = 'confirmed' AND `warned` = 'yes' ORDER BY `added` DESC $limit");

    stdhead("Warned Users");
    navmenu();
    
    begin_frame("Warned Users");
    ?>
    
    <center>
    This page displays all users which are enabled and have active warnings, they can be mass deleted or deleted per user. Please note that if you delete a warning which was for poor ratio then
    this is extending the time user has left to expire. <?php echo number_format($count); ?> users are warned;
    </center>

    <br />
    <?php if ($count > 0): ?>
    <br />
    <form id="warned" method="post" action="supermodcp.php?action=warned&amp;do=delete">
    <table border="0" cellpadding="3" cellspacing="0" width="100%" align="center" class="table_table">
    <tr>
        <th class="table_head">Username</th>
        <th class="table_head">Class</th>   
        <th class="table_head">Added</th>  
        <th class="table_head">Last Access</th>
        <th class="table_head">Warnings</th>
        <th class="table_head"><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);" /></th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($res)): ?>
    <tr>
        <td class="table_col1" align="center"><a href="account-details.php?id=<?php echo $row["id"]; ?>"><?php echo $row["username"]; ?></a></td>
        <td class="table_col2" align="center"><?php echo get_user_class_name($row["class"]); ?></td>  
        <td class="table_col1" align="center"><?php echo utc_to_tz($row["added"]); ?></td>
        <td class="table_col2" align="center"><?php echo utc_to_tz($row["last_access"]); ?></td>
        <td class="table_col1" align="center"><a href="account-details.php?id=<?php echo $row["id"]; ?>#warnings"><?php echo number_format(get_row_count("warnings", "WHERE userid = '$row[id]' AND active = 'yes'")); ?></a></td>
        <td class="table_col2" align="center"><input type="checkbox" name="warned[]" value="<?php echo $row["id"]; ?>" /></td>
    </tr>
    <?php endwhile; ?>
    <tr>
        <td colspan="6" align="right">
        <input type="submit" value="Remove Checked" />
        <input type="submit" name="removeall" value="Remove All" />
        </td>
    </tr>
    </table>         
    </form>
    <?php else: ?>
    <center><b>No Warned Users...</b></center>
    <?php
    endif;
    
    if ($count > 25) echo $pagerbottom;

    end_frame();
    stdfoot(); 
}



#======================================================================#
#    View Pending Invited Users - Created by djhowarth (18-11-2011) 
#======================================================================#
if ($action == "pendinginvite")
{
    if ($do == "del") 
    {
        if (!@count($_POST["users"])) show_error_msg("Error", "Nothing Selected.", 1);

        $ids = array_map("intval", $_POST["users"]);
        $ids = implode(", ", $ids);
        
        $res = SQL_Query_exec("SELECT u.id, u.invited_by, i.invitees FROM users u LEFT JOIN users i ON u.invited_by = i.id WHERE u.status = 'pending' AND u.invited_by != '0' AND u.id IN ($ids)");
        while ($row = mysqli_fetch_assoc($res))
        {    
             # We remove the invitee from the inviter and give them back there invite.
             $invitees = str_replace("$row[id] ", "", $row["invitees"]);
             SQL_Query_exec("UPDATE `users` SET `invites` = `invites` + 1, `invitees` = '$invitees' WHERE `id` = '$row[invited_by]'");
             SQL_Query_exec("DELETE FROM `users` WHERE `id` = '$row[id]'");
        }

        autolink("supermodcp.php?action=pendinginvite", "Entries Deleted");
    }
    
    $count = get_row_count("users", "WHERE status = 'pending' AND invited_by != '0'");
    
    list($pagertop, $pagerbottom, $limit) = pager(25, $count, 'supermodcp.php?action=pendinginvite&amp;');
                                                                     
    $res = SQL_Query_exec("SELECT u.id, u.username, u.email, u.added, u.invited_by, i.username as inviter FROM users u LEFT JOIN users i ON u.invited_by = i.id WHERE u.status = 'pending' AND u.invited_by != '0' ORDER BY u.added DESC $limit");
    
    stdhead("Invited Pending Users");
    navmenu();
    
    begin_frame("Invited Pending Users");
    ?>
    
    <center>
    This page displays all invited users which have been sent invites but haven't yet activated there account. By deleting a user the inviter will recieve their invite back and any data associated with the invitee will be deleted. <?php echo number_format($count); ?> members are pending;
    </center>

    <?php if ($count > 0): ?>
    <br />
    <form id="pendinginvite" method="post" action="supermodcp.php?action=pendinginvite">
    <input type="hidden" name="do" value="del" />
    <table border="0" cellpadding="3" cellspacing="0" width="100%" align="center" class="table_table">
    <tr>
        <th class="table_head">Username</th>
        <th class="table_head">E-mail</th>
        <th class="table_head">Invited</th>
        <th class="table_head">Invited By</th>
        <th class="table_head"><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);" /></th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($res)): ?>
    <tr>
        <td class="table_col1" align="center"><?php echo $row["username"]; ?></td>
        <td class="table_col2" align="center"><?php echo $row["email"]; ?></td>
        <td class="table_col1" align="center"><?php echo utc_to_tz($row["added"]); ?></td>
        <td class="table_col2" align="center"><a href="account-details.php?id=<?php echo $row["invited_by"]; ?>"><?php echo $row["inviter"]; ?></a></td>
        <td class="table_col1" align="center"><input type="checkbox" name="users[]" value="<?php echo $row["id"]; ?>" /></td>
    </tr>
    <?php endwhile; ?>
    <tr>
        <td colspan="5" align="right">
        <input type="submit" value="Delete Checked" />
        </td>
    </tr>
    </table>         
    </form>
    <?php 
    endif;
    
    if ($count > 25) echo $pagerbottom;
    
    end_frame();
    stdfoot(); 
}

#======================================================================#
# Invited Users - Created by djhowarth (11-12-2011) 
#======================================================================#
if ($action == "invited")
{
    if ($do == "del") 
    {
        if (!@count($_POST["users"])) show_error_msg("Error", "Nothing Selected.", 1);

        $ids = array_map("intval", $_POST["users"]);
        $ids = implode(", ", $ids);
        
        $res = SQL_Query_exec("SELECT u.id, u.invited_by, i.invitees FROM users u LEFT JOIN users i ON u.invited_by = i.id WHERE u.status = 'pending' AND u.invited_by != '0' AND u.id IN ($ids)");
        while ($row = mysqli_fetch_assoc($res))
        {    
             # We remove the invitee from the inviter and give them back there invite.
             $invitees = str_replace("$row[id] ", "", $row["invitees"]);
             SQL_Query_exec("UPDATE `users` SET `invites` = `invites` + 1, `invitees` = '$invitees' WHERE `id` = '$row[invited_by]'");
             deleteaccount($row['id']);
        }

        autolink("supermodcp.php?action=invited", "Entries Deleted");
    }
    
    $count = get_row_count("users", "WHERE status = 'confirmed' AND invited_by != '0'");
    
    list($pagertop, $pagerbottom, $limit) = pager(25, $count, 'supermodcp.php?action=invited&amp;');
                                                                     
    $res = SQL_Query_exec("SELECT u.id, u.username, u.email, u.added, u.last_access, u.class, u.invited_by, i.username as inviter FROM users u LEFT JOIN users i ON u.invited_by = i.id WHERE u.status = 'confirmed' AND u.invited_by != '0' ORDER BY u.added DESC $limit");
    
    stdhead("Invited Users");
    navmenu();
                    
    begin_frame("Invited Users");
    ?>
    
    <center>
    This page displays all invited users which have been sent invites and have activated there account. By deleting users the inviter will recieve there invite back and any data associated with the invitee will be deleted. <?php echo number_format($count); ?> members have confirmed invites;
    </center>

    <?php if ($count > 0): ?>
    <br />
    <form id="invited" method="post" action="supermodcp.php?action=invited">
    <input type="hidden" name="do" value="del" />
    <table border="0" cellpadding="3" cellspacing="0" width="100%" align="center" class="table_table">
    <tr>
        <th class="table_head">Username</th>
        <th class="table_head">E-mail</th>
        <th class="table_head">Class</th>
        <th class="table_head">Invited</th>
        <th class="table_head">Last Access</th> 
        <th class="table_head">Invited By</th>
        <th class="table_head"><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);" /></th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($res)): ?>
    <tr>
        <td class="table_col1" align="center"><a href="account-details.php?id=<?php echo $row["id"]; ?>"><?php echo $row["username"]; ?></a></td>
        <td class="table_col2" align="center"><?php echo $row["email"]; ?></td>
        <td class="table_col1" align="center"><?php echo get_user_class_name($row["class"]); ?></td>     
        <td class="table_col2" align="center"><?php echo utc_to_tz($row["added"]); ?></td>
        <td class="table_col1" align="center"><?php echo utc_to_tz($row["last_access"]); ?></td>  
        <td class="table_col2" align="center"><a href="account-details.php?id=<?php echo $row["invited_by"]; ?>"><?php echo $row["inviter"]; ?></a></td>
        <td class="table_col1" align="center"><input type="checkbox" name="users[]" value="<?php echo $row["id"]; ?>" /></td>
    </tr>
    <?php endwhile; ?>
    <tr>
        <td colspan="7" align="right">
        <input type="submit" value="Delete Checked" />
        </td>
    </tr>
    </table>         
    </form>
    <?php 
    endif;
    
    if ($count > 25) echo $pagerbottom;
    
    end_frame();
    stdfoot(); 
}

#======================================================================#
#  Strong Privacy Users - Added by djhowarth (01-12-2011) 
#======================================================================#
if ($action == "privacylevel")
{
    $where = array();
    
    switch ( $_GET['type'] )
    {
        case 'low': 
              $where[] = "privacy = 'low'";    break;
        case 'normal':
              $where[] = "privacy = 'normal'"; break;
        case 'strong':                         
              $where[] = "privacy = 'strong'"; break;
        default:
              break;
    }
    
    $where[] = "enabled = 'yes'";
    $where[] = "status = 'confirmed'";
    
    $where = implode(' AND ', $where);
    
    $count = get_row_count("users", "WHERE $where");
    
    list($pagertop, $pagerbottom, $limit) = pager(25, $count, htmlspecialchars($_SERVER['REQUEST_URI'] . '&'));  
                                                                     
    $res = SQL_Query_exec("SELECT id, username, class, email, ip, added, last_access FROM users WHERE $where ORDER BY username DESC $limit");
    
    stdhead("Privacy Level");
    navmenu();
    
    begin_frame("Privacy Level");
    ?>
    
    <center>
    This page displays all users which are enabled, confirmed grouped by their privacy level.
    </center>

    <br />
    <table align="right">
    <tr>
        <td valign="top">
        <form id='sort' action=''>
        <b>Privacy Level:</b>
        <select name="type" onchange="window.location='supermodcp.php?action=privacylevel&type='+this.options[this.selectedIndex].value">
        <option value="">Any</option>
        <option value="low" <?php echo ($_GET['type'] == "low" ? " selected='selected'" : ""); ?>>Low</option>
        <option value="normal" <?php echo ($_GET['type'] == "normal" ? " selected='selected'" : ""); ?>>Normal</option>
        <option value="strong" <?php echo ($_GET['type'] == "strong" ? " selected='selected'" : ""); ?>>Strong</option>
        </select>
        </form>     
    </td>
    </tr>
    </table>
    <br />
    <br />
    
    <?php if ($count > 0): ?>
    <br />
    <table border="0" cellpadding="3" cellspacing="0" width="100%" align="center" class="table_table">
    <tr>
        <th class="table_head">Username</th>
        <th class="table_head">Class</th>
        <th class="table_head">E-mail</th>
        <th class="table_head">IP</th>
        <th class="table_head">Added</th>
        <th class="table_head">Last Visited</th>  
    </tr>
    <?php while ($row = mysqli_fetch_assoc($res)): ?>
    <tr>
        <td class="table_col1" align="center"><a href="account-details.php?id=<?php echo $row["id"]; ?>"><?php echo $row["username"]; ?></a></td>
        <td class="table_col2" align="center"><?php echo get_user_class_name($row["class"]); ?></td>
        <td class="table_col1" align="center"><?php echo $row["email"]; ?></td>
        <td class="table_col2" align="center"><?php echo $row["ip"]; ?></td>
        <td class="table_col1" align="center"><?php echo utc_to_tz($row["added"]); ?></td>
        <td class="table_col2" align="center"><?php echo utc_to_tz($row["last_access"]); ?></td> 
    </tr>
    <?php endwhile; ?>
    </table>         
    <?php else: ?>
    <center><b>Nothing Found...</b></center>
    <?php  
    endif;
    
    if ($count > 25) echo $pagerbottom;
    
    end_frame();
    stdfoot(); 
}
                             
#======================================================================#
# Word Censor Filter
#======================================================================#
if($action == "censor") {
stdhead("Censor");
navmenu();
if($site_config["OLD_CENSOR"])
{
//Output
if ($_POST['submit'] == 'Add Censor'){
$query = "INSERT INTO censor (word, censor) VALUES (" . sqlesc($_POST['word']) . "," . sqlesc($_POST['censor']) . ");";
             SQL_Query_exec($query);
             }
if ($_POST['submit'] == 'Delete Censor'){
  $aquery = "DELETE FROM censor WHERE word = " . sqlesc($_POST['censor']) . " LIMIT 1";
  SQL_Query_exec($aquery);
  }

begin_frame("Edit Censored Words");  
/*------------------
|HTML form for Word Censor
------------------*/
?>

<form method="post" action="supermodcp.php?action=censor">
<table width='100%' cellspacing='3' cellpadding='3' align='center'>
<tr>
<td bgcolor='#eeeeee'><font face="verdana" size="1">Word:  <input type="text" name="word" id="word" size="50" maxlength="255" value="" /></font></td></tr>
<tr><td bgcolor='#eeeeee'><font face="verdana" size="1">Censor With:  <input type="text" name="censor" id="censor" size="50" maxlength="255" value="" /></font></td></tr>
<tr><td bgcolor='#eeeeee' align='left'>
<font size="1" face="verdana"><input type="submit" name="submit" value="Add Censor" /></font></td>
</tr>
</table>
</form>

<form method="post" action="supermodcp.php?action=censor">
<table>
<tr>
<td bgcolor='#eeeeee'><font face="verdana" size="1">Remove Censor For: <select name="censor">
<?php
/*-------------
|Get the words currently censored
-------------*/
$select = "SELECT word FROM censor ORDER BY word";
$sres = SQL_Query_exec($select);
while ($srow = mysqli_fetch_array($sres))
{
        echo "<option>" . $srow[0] . "</option>\n";
        }
echo'</select></font></td></tr><tr><td bgcolor="#eeeeee" align="left">
<font size="1" face="verdana"><input type="submit" name="submit" value="Delete Censor" /></font></td>
</tr></table></form>';
}
else
{
$to=isset($_GET["to"])?htmlentities($_GET["to"]):$to='';
switch ($to)
  {
    case 'write':
         begin_frame($LANG['ACP_CENSORED']);
         if (isset($_POST["badwords"]))
            {
            $f=fopen("censor.txt","w+");
            @fwrite($f,$_POST["badwords"]);
            fclose($f);
            }
			show_error_msg("Success","Censor Updated!",0);
         break;


    case '':
    case 'read':
    default:
      $f=@fopen("censor.txt","r");
      $badwords=@fread($f,filesize("censor.txt"));
      @fclose($f);
	  begin_frame($LANG['ACP_CENSORED']);
      echo'<form action="supermodcp.php?action=censor&to=write" method="post" enctype="multipart/form-data">
  <table width="100%" align="center">
    <tr>
      <td align="center">'.$LANG['ACP_CENSORED_NOTE'].'</td>
    </tr>
    <tr>
      <td align="center"><textarea name="badwords" rows="20" cols="60">'.$badwords.'</textarea></td>
    </tr>
    <tr>
      <td align="center">
        <input type="submit" name="write" value="Confirm" />&nbsp;&nbsp;
        <input type="submit" name="write" value="Cancel" />
      </td>
    </tr>
  </table>
</form><br />';
break;
}
}
end_frame();
stdfoot();
}


// Forum management 
if ($action == "forum") {

    $error_ac == "";
    if ($_POST["do"] == "add_this_forum") {
        
        $new_forum_name = $_POST["new_forum_name"];
        $new_desc = $_POST["new_desc"];
        $new_forum_sort = (int) $_POST["new_forum_sort"];
        $new_forum_cat  = (int) $_POST["new_forum_cat"];
        $minclassread = (int)  $_POST["minclassread"];
        $minclasswrite = (int) $_POST["minclasswrite"];
        $guest_read = sqlesc($_POST["guest_read"]);
        
        if ($new_forum_name == "") $error_ac .= "<li>Forum-name was empty</li>\n";
        if ($new_desc == "") $error_ac .= "<li>Forum-description was empty</li>\n";
        if ($new_forum_sort == "") $error_ac .= "<li>Forum sort order was empty</li>\n";
        if ($new_forum_cat == "") $error_ac .= "<li>Forum category was empty</li>\n";

        if ($error_ac == "") {
            $res = SQL_Query_exec("INSERT INTO forum_forums (`name`, `description`, `sort`, `category`, `minclassread`, `minclasswrite`, `guest_read`) VALUES (".sqlesc($new_forum_name).", ".sqlesc($new_desc).", ".sqlesc($new_forum_sort).", '$new_forum_cat', '$minclassread', '$minclasswrite', $guest_read)");
            if ($res)
                autolink("supermodcp.php?action=forum", "Thank you, new forum added to db ...");
            else
                echo "<h4>Could not save to DB - check your connection & settings!</h4>";
        } 
    }

    if ($_POST["do"] == "add_this_forumcat") {
        
        $new_forumcat_name = $_POST["new_forumcat_name"];
        $new_forumcat_sort = $_POST["new_forumcat_sort"];
        
        if ($new_forumcat_name == "") $error_ac .= "<li>Forum cat name was empty</li>\n";
        if ($new_forumcat_sort == "") $error_ac .= "<li>Forum cat sort order was empty</li>\n";

        if ($error_ac == "") {
            $res = SQL_Query_exec("INSERT INTO forumcats (`name`, `sort`) VALUES (".sqlesc($new_forumcat_name).", '".intval($new_forumcat_sort)."')");
            if ($res)
                autolink("supermodcp.php?action=forum", "Thank you, new forum cat added to db ...");
            else
                echo "<h4>Could not save to DB - check your connection & settings!</h4>";
        } 
    }

    if ($_POST["do"] == "save_edit") {
        
        $id = (int) $_POST["id"];
        $changed_sort = (int) $_POST["changed_sort"];
        $changed_forum = sqlesc($_POST["changed_forum"]);
        $changed_forum_desc = sqlesc($_POST["changed_forum_desc"]);
        $changed_forum_cat = (int) $_POST["changed_forum_cat"];
        $minclasswrite = (int) $_POST["minclasswrite"];
        $minclassread  = (int) $_POST["minclassread"];
        $guest_read = sqlesc($_POST["guest_read"]);
        
        SQL_Query_exec("UPDATE forum_forums SET sort = '$changed_sort', name = $changed_forum, description = $changed_forum_desc, category = '$changed_forum_cat', minclassread='$minclassread', minclasswrite='$minclasswrite', guest_read=$guest_read WHERE id='$id'");
        autolink("supermodcp.php?action=forum", "<center><b>Update Completed</b></center>");
    }

    if ($_POST["do"] == "save_editcat") {
        
        $id = (int) $_POST["id"];
        $changed_sortcat = (int) $_POST["changed_sortcat"];
        
        SQL_Query_exec("UPDATE forumcats SET sort = '$changed_sortcat', name = ".sqlesc($_POST["changed_forumcat"])." WHERE id='$id'");
        autolink("supermodcp.php?action=forum", "<center><b>Update Completed</b></center>");
    }

    if ($_POST["do"] == "delete_forum" && is_valid_id($_POST["id"])) 
    {
        SQL_Query_exec("DELETE FROM forum_forums WHERE id = $_POST[id]");
        SQL_Query_exec("DELETE FROM forum_topics WHERE forumid = $_POST[id]");
        SQL_Query_exec("DELETE FROM forum_posts WHERE topicid = $_POST[id]");
        SQL_Query_exec("DELETE FROM forum_readposts WHERE topicid = $_POST[id]");
        autolink("supermodcp.php?action=forum", "forum deleted ...");
    }
    
    if ($_POST["do"] == "delete_forumcat" && is_valid_id($_POST["id"])) 
    {
        SQL_Query_exec("DELETE FROM forumcats WHERE id = $_POST[id]");
        
        $res = SQL_Query_exec("SELECT id FROM forum_forums WHERE category = $_POST[id]");
        
        while ( $row = mysqli_fetch_assoc($res) )
        {
            SQL_Query_exec("DELETE FROM forum_topics WHERE forumid = $row[id]");
            SQL_Query_exec("DELETE FROM forum_posts WHERE topicid = $row[id]");
            SQL_Query_exec("DELETE FROM forum_readposts WHERE topicid = $row[id]");
            SQL_Query_exec("DELETE FROM forum_forums WHERE id = $row[id]");  
        }
        
        autolink("supermodcp.php?action=forum", "forum cat deleted ...");
    }
    
    stdhead("Forum Management");
    
    $groupsres = SQL_Query_exec("SELECT group_id, level FROM groups ORDER BY group_id ASC");
    while ($groupsrow = mysqli_fetch_row($groupsres))
        $groups[$groupsrow[0]] = $groupsrow[1];

    if ($_GET["do"] == "edit_forum") {
        
        $id = (int) $_GET["id"];
        
        $q = SQL_Query_exec("SELECT * FROM forum_forums WHERE id = '$id'");
        $r = mysqli_fetch_array($q);
        
        if (!$r)
             autolink("supermodcp.php?action=forum", "Invalid Forum.");
        
        begin_frame("Edit Forum");   
    ?>
          <form action="supermodcp.php?action=forum" method="post">
          <input type="hidden" name="do" value="save_edit" />
          <input type="hidden" name="id" value="<?php echo $id; ?>" />
          <table class='f-border a-form' align='center' width='80%' cellspacing='2' cellpadding='5'>
          <tr class='f-form'>
          <td>New Name for Forum:</td>
          <td align='right'><input type="text" name="changed_forum" class="option" size="35" value="<?php echo $r["name"]; ?>" /></td>
          </tr><tr class='f-form'>
          <td>New Sort Order:</td>
          <td align='right'><input type="text" name="changed_sort" class="option" size="35" value="<?php echo $r["sort"]; ?>" /></td>
          </tr><tr class='f-form'>
          <td>Description:</td>
          <td align='right'><textarea cols='50' rows='5' name='changed_forum_desc'><?php echo $r["description"]; ?></textarea></td>
          </tr><tr class='f-form'>
          <td>New Category:</td>
          <td align='right'><select name='changed_forum_cat'>
    <?php
    $query = SQL_Query_exec("SELECT * FROM forumcats ORDER BY sort, name");
    while ($row = mysqli_fetch_array($query))
        echo "<option value='{$row['id']}'>{$row['name']}</option>";

    echo "</select></td></tr>
    <tr class='f-form'><td>Mininum Class Needed to Read:</td>
    <td align='right'><select name='minclassread'>";

    foreach ($groups as $id => $level) {
        $s = $r["minclassread"] == $id ? " selected='selected'" : "";
        echo "<option value='$id' $s>$level</option>";
    }

    echo "</select></td></tr><tr class='f-form'><td>Mininum Class Needed to Post:</td>
    <td align='right'><select name='minclasswrite'>";

    foreach ($groups as $id => $level) {
        $s = $r["minclasswrite"] == $id ? " selected='selected'" : "";
        echo "<option value='$id' $s>$level</option>";
    }
    ?>
    </select></td></tr><tr class='f-form'>
    <td>Allow Guests to Read:</td><td align='right'><input type="radio" name="guest_read" value="yes" <?php echo $r["guest_read"] == "yes" ? "checked='checked'" : ""?> />Yes, <input type="radio" name="guest_read" value="no" <?php echo $r["guest_read"] != "yes" ? "checked='checked'" : ""?> />No</td></tr>
    <tr class='f-form'><td><input type="submit" class="button" value="Change" /></td></tr>
    </table>
    </form>
    <?php
        end_frame();
        stdfoot();
    }

if ($_GET["do"] == "del_forum") {
    
    $id = (int) $_GET["id"];
    
    $t = SQL_Query_exec("SELECT * FROM forum_forums WHERE id = '$id'");
    $v = mysqli_fetch_array($t);
    
    if (!$v)
         autolink("supermodcp.php?action=forum", "Invalid Forum.");
    
    begin_frame("Confirm"); 
?>
    <form class='a-form' action="supermodcp.php?action=forum" method="post">
    <input type="hidden" name="do" value="delete_forum" />
    <input type="hidden" name="id" value="<?php echo $id; ?>" />
    Really delete the Forum <?php echo "<b>$v[name] with ID$v[id] ???</b>"; ?> this will delete everything associated with it.
    <input type="submit" name="delcat" class="button" value="Delete" />
    </form>
<?php
          end_frame();
          stdfoot();
}

if ($_GET["do"] == "del_forumcat") {
    
    $id = (int) $_GET["id"];

    $t = SQL_Query_exec("SELECT * FROM forumcats WHERE id = '$id'");
    $v = mysqli_fetch_array($t);
    
    if (!$v)
         autolink("supermodcp.php?action=forum", "Invalid Forum Category.");
    
    begin_frame("Confirm"); 
?>
  <form class='a-form' action="supermodcp.php?action=forum" method="post">
  <input type="hidden" name="do" value="delete_forumcat" />
  <input type="hidden" name="id" value="<?php echo $id; ?>" />
      Really delete the Forum category<?php echo "<b>$v[name] with ID$v[id] ???</b>"; ?> this will delete everything associated with it.
      <input type="submit" name="delcat" class="button" value="Delete" />
      </form>
<?php
          end_frame();
          stdfoot();
}

if ($_GET["do"] == "edit_forumcat") {
    
    $id = (int) $_GET["id"];

    $q = SQL_Query_exec("SELECT * FROM forumcats WHERE id = '$id'");
    $r = mysqli_fetch_array($q);
    
    if (!$r)
         autolink("supermodcp.php?action=forum", "Invalid Forum Category.");
         
    begin_frame("Edit Category");
    ?>
    <form action="supermodcp.php?action=forum" method="post">
    <input type="hidden" name="do" value="save_editcat" />
    <input type="hidden" name="id" value="<?php echo $id; ?>" />
    <table class='f-border a-form' align='center' width='80%' cellspacing='2' cellpadding='5'>
    <tr class='p-title'><td class='f-border'>New Name for Category:</td></tr>
    <tr><td align='center' class='f-form'><input type="text" name="changed_forumcat" class="option" size="35" value="<?php echo $r["name"]; ?>" /></td></tr>
    <tr><td align='center' class='f-form'>New Sort Order:</td></tr>
    <tr><td align='center' class='f-form'><input type="text" name="changed_sortcat" class="option" size="35" value="<?php echo $r["sort"]; ?>" /></td></tr>
    <tr><td align='center' class='f-form'><input type="submit" class="button" value="Change" /></td></tr>
    </table>
    </form>
    <?php
    end_frame();
    stdfoot();
}
    
    if (!$do) {
        navmenu();
        begin_frame("Forums Management");
        $query = SQL_Query_exec("SELECT * FROM forumcats ORDER BY sort, name");
        $allcat = mysqli_num_rows($query);
        $forumcat = array();
        while ($row = mysqli_fetch_array($query))
            $forumcat[] = $row;

        echo "
    <form action='supermodcp.php' method='post'>
    <input type='hidden' name='sid' value='$sid' />
<input type='hidden' name='action' value='forum' />
<input type='hidden' name='do' value='add_this_forum' />
<table class='f-border a-form' align='center' width='80%' cellspacing='2' cellpadding='5'>
<tr class='f-form'>
<td>Name of the new Forum:</td>
<td align='right'><input type='text' name='new_forum_name' size='90' maxlength='30'  value='$new_forum_name' /></td>
</tr>
<tr class='f-form'>
<td>Forum Sort Order:</td>
<td align='right'><input type='text' name='new_forum_sort' size='30' maxlength='10'  value='$new_forum_sort' /></td>
</tr>
<tr class='f-form'>
<td>Description of the new Forum:</td>
<td align='right'><textarea cols='50' rows='5' name='new_desc'>$new_desc</textarea></td>
</tr>
<tr class='f-form'>
<td>Forum Category:</td>
<td align='right'><select name='new_forum_cat'>";
foreach ($forumcat as $row)
    echo "<option value='{$row['id']}'>{$row['name']}</option>";

echo "</select>
</td>
</tr>
<tr class='f-form'><td>Mininum Class Needed to Read:</td>
<td align='right'><select name='minclassread'>";

foreach ($groups as $id => $level) {
    $s = $r["minclassread"] == $id ? " selected='selected'" : "";
    echo "<option value='$id' $s>$level</option>";
}

echo "</select></td></tr>
<tr class='f-form'><td>Mininum Class Needed to Post:</td>
<td align='right'><select name='minclasswrite'>";

foreach ($groups as $id => $level) {
    $s = $r["minclasswrite"] == $id ? " selected='selected'" : "";
    echo "<option value='$id' $s>$level</option>";
}

echo "</select></td></tr>".
"<tr class='f-form'><td>Allow Guests to Read:</td><td align='right'><input type=\"radio\" name=\"guest_read\" value=\"yes\" checked='checked' />Yes, <input type=\"radio\" name=\"guest_read\" value=\"no\" />No</td></tr>".
"<tr class='f-form'>
<td colspan='2' align='center'>
<input type='submit' value='Add new forum' />
<input type='reset' value='Reset' />
</td>
</tr>";

if($error_ac != "") echo "<tr class='f-form'><td colspan='2' align='center' style='background:#eeeeee;border:2px red solid'><b>COULD  NOT ADD NEW forum:</b><br /><ul>$error_ac</ul></td></tr>\n";

echo "</table>
</form>

<b>Current Forums:</b>
<table class='f-border' align='center' width='80%' cellspacing='0' cellpadding='4'>";

echo "<tr><th class='f-border p-title' width='60'><font size='2'><b>ID</b></font></th><th class='f-border p-title' width='120'>NAME</th><th class='f-border p-title' width='250'>DESC</th><th class='f-border p-title' width='45'>SORT</th><th class='f-border p-title' width='45'>CATEGORY</th><th class='f-border p-title' width='18'>EDIT</th><th class='f-border p-title' width='18'>DEL</th></tr>\n";
$query = SQL_Query_exec("SELECT * FROM forum_forums ORDER BY sort, name");
$allforums = mysqli_num_rows($query);
if ($allforums == 0) {
    echo "<tr class='alt1'><td class='f-border' colspan='7' align='center'>No Forums found</td></tr>\n";
} else {
    while($row = mysqli_fetch_array($query)) {
        foreach ($forumcat as $cat)
            if ($cat['id'] == $row['category'])
                $category = $cat['name'];
            
            echo "<tr class='alt1'><td class='f-border' width='60'><font size='2'><b>ID($row[id])</b></font></td><td class='f-border' width='120'> $row[name]</td><td class='f-border'  width='250'>$row[description]</td><td class='f-border' width='45'>$row[sort]</td><td class='f-border' width='45'>$category</td>\n";
            echo "<td class='f-border' width='18'><a href='supermodcp.php?action=forum&amp;do=edit_forum&amp;id=$row[id]'>[Edit]</a></td>\n";
            echo "<td class='f-border' width='18'><a href='supermodcp.php?action=forum&amp;do=del_forum&amp;id=$row[id]'><img src='images/delete.gif' alt='Delete  Category' width='17' height='17' border='0' /></a></td></tr>\n";
    }
}
echo "</table>
<br /><b>Current Forum Categories:</b><table class='f-border' align='center' width='80%' cellspacing='0' cellpadding='4'>
<tr><th class='f-border p-title' width='60'><font size='2'><b>ID</b></font></th><th class='f-border p-title' width='120'>NAME</th><th class='f-border p-title' width='18'>SORT</th><th class='f-border p-title' width='18'>EDIT</th><th class='f-border p-title' width='18'>DEL</th></tr>\n";

if ($allcat == 0) {
    echo "<tr class='alt1'><td class='f-border' colspan='7' align='center'>No Categories found</td></tr>\n"; 
} else {
    foreach ($forumcat as $row) {
        echo "<tr class='alt1'><td class='f-border' width='60'><font size='2'><b>ID($row[id])</b></font></td><td class='f-border' width='120'> $row[name]</td><td class='f-border' width='18'>$row[sort]</td>\n";
        echo "<td class='f-border' width='18'><a href='supermodcp.php?action=forum&amp;do=edit_forumcat&amp;id=$row[id]'>[Edit]</a></td>\n";
        echo "<td class='f-border' width='18'><a href='supermodcp.php?action=forum&amp;do=del_forumcat&amp;id=$row[id]'><img src='images/delete.gif' alt='Delete  Category' width='17' height='17' border='0' /></a></td></tr>\n";
    }
}
echo "</table>\n";

echo "<br />
<form action='supermodcp.php?action=forum' method='post'>
<input type='hidden' name='do' value='add_this_forumcat' /> 
<table class='f-border a-form' align='center' width='80%' cellspacing='2' cellpadding='5'>
<tr class='f-form'>
<td>Name of the new Category:</td>
<td align='right' class='f-form'><input type='text' name='new_forumcat_name' size='60' maxlength='30'  value='$new_forumcat_name' /></td>
</tr>
<tr class='f-form'>
<td>Category Sort Order:</td>
<td align='right' class='f-form'><input type='text' name='new_forumcat_sort' size='20' maxlength='10'  value='$new_forumcat_sort' /></td>
</tr>

<tr class='f-form'>
<td class='f-form' colspan='2' align='center'>
<input type='submit' value='Add new category' />
<input type='reset' value='Reset' />
</td>
</tr>
</table>
</form>";
end_frame();
stdfoot();
    } // End New Forum


} // End Forum management



if ( $action == "free" )
  {
           if ( is_valid_id( $_POST["type"] ) )
           {
                        $type = ( $_POST["type"] == 2 ) ? 0 : 1;
                        $size = (int) $_POST["size"];

                        $where = null;
                        
                        switch ( $_POST["operand"] )
                        {
                                case 1:
                                         $operand = "=";
                                         break;
                                
                                case 2:
                                         $operand = "<";
                                         break;
                                        
                                case 3:
                                         $operand = "<=";
                                         break;
                                        
                                case 4:
                                         $operand = ">";
                                         break;
                                        
                                case 5:
                                         $operand = ">=";
                                         break;
                                        
                                default:
                                         $operand = null;
                                         break;
                        }
                        
                        if ( $operand != null )
                        {
                                 $where = "AND `size` $operand $size";
                        }

                        SQL_Query_exec("UPDATE `torrents` SET `freeleech` = '$type' WHERE `external` = 'no' AND `banned` = 'no' $where");
                        autolink("supermodcp.php?action=free", "Freeleech Updated...");
           }
          
           stdhead("Freeleech Management");
           navmenu();
          
           begin_frame("Freeleech Management");
           ?>
          
           <form method="post" action="supermodcp.php?action=free">
           <table border="0" cellpadding="3" cellspacing="0" width="75%" align="center">
           <tr>
                   <th class="table_head">Freeleech</th>
                   <th class="table_head">Operand</th>
                   <th class="table_head">Size</th>
           </tr>
           <tr align="center">
                   <td class="table_col1">
                         <select name="type">
                          <option value="0">Choose Option</option>
                          <option value="1">Yes</option>
                          <option value="2">No</option>
                         </select>
                   </td>
                  
                   <td class="table_col2">
                         <select name="operand">
                           <option value="0">N/A</option>
                           <option value="1">Equal to</option>
                           <option value="2">Less than</option>
                           <option value="3">Less than or equal to</option>
                           <option value="4">More than</option>
                           <option value="5">More than or equal to</option>
                         </select>
                   </td>
                  
                   <td class="table_col1">
                         <select name="size">
                           <option value="0">N/A</option>
                         <?php for ( $i = 1; $i < 26; $i++ ): ?>
                           <option value="<?php echo strtobytes("$i GB"); ?>"><?php echo $i; ?> GB</option>
                         <?php endfor; ?>
                         </select>
                   </td>
           </tr>
           <tr>
                        <td colspan="3" align="right">
                         <input type="submit" value="Ok" />
                        </td>
           </tr>
           </table>
           </form>
          
           <?php
           end_frame();
           stdfoot();
  }


?>
