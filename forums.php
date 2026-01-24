<?php
//
//  TorrentTrader v2.08-FINAL
//      $LastChangedDate: 2018-07-06 15:15:54 +0100 (Fri, 6 Jul 2018) $
//      $LastChangedBy: UFFENO1 $
//
//      http://www.torrenttrader.org
//
//

require_once("backend/functions.php");
require_once("backend/bbcode.php");

dbconn();

if (!$site_config["FORUMS_GUESTREAD"])
	loggedinonly();

	$action = strip_tags(isset($_REQUEST["action"]) ? $_REQUEST["action"] : "");
    
if (!$CURUSER && ($action == "newtopic" || $action == "post")) 
    show_error_msg(T_("FORUM_ERROR"), T_("FORUM_NO_ID"));

if ($CURUSER["forumbanned"] == "yes" || $CURUSER["view_forum"] == "no")
    show_error_msg(T_("FORUM_BANNED"), T_("FORUM_BANNED"));

//Here we decide if the forums is on or off
if ($site_config["FORUMS"]) {
$themedir = "themes/".$THEME."/forums/";
$dossier = $CURUSER['bbcode'];
//setup the forum head aread
function forumheader($location){
echo "<div class='f-header'>
  <div class='f-logo'>
  <table width='100%' cellspacing='6'>
    <tr>
      <td align='left' valign='top'><a href='forums.php'>".T_("FORUM_WELCOME")."</a></td>
      <td align='right' valign='top'><img src='images/forum/help.png'  alt='' />&nbsp;<a href='faq.php'>".T_("FORUM_FAQ")."</a>&nbsp; &nbsp;&nbsp;<img src='images/forum/search.png' alt='' />&nbsp;<a href='forums.php?action=search'>".T_("SEARCH")."</a></td>
    </tr>
    <tr>
      <td align='left' valign='bottom'>&nbsp;</td>
      <td align='right' valign='bottom'><b>".T_("FORUM_CONTROL")."</b> &middot; <a href='forums.php?action=viewunread'>".T_("FORUM_NEW_POSTS")."</a> &middot; <a href='?catchup'>".T_("FORUM_MARK_READ")."</a></td>
    </tr>
  </table>
  </div>
</div>
<br />";
print ("<div class='f-location'><div class='f-nav'>".T_("YOU_ARE_IN").": &nbsp;<a href='forums.php'>".T_("FORUMS")."</a> <b style='vertical-align:middle'>/ $location</b></div></div><br />");
}

// Mark all forums as read
function catch_up(){ 
	global $CURUSER;
	
    if (!$CURUSER)
		 return;
    
	$userid = $CURUSER["id"];
	$res = SQL_Query_exec("SELECT id, lastpost FROM forum_topics");
	while ($arr = mysqli_fetch_assoc($res)) {
		$topicid = $arr["id"];
		$postid = $arr["lastpost"];
		$r = SQL_Query_exec("SELECT id,lastpostread FROM forum_readposts WHERE userid=$userid and topicid=$topicid");
		if (mysqli_num_rows($r) == 0){
			SQL_Query_exec("INSERT INTO forum_readposts (userid, topicid, lastpostread) VALUES($userid, $topicid, $postid)");
		}else{
			$a = mysqli_fetch_assoc($r);
			if ($a["lastpostread"] < $postid)
			SQL_Query_exec("UPDATE forum_readposts SET lastpostread=$postid WHERE id=" . $a["id"]);
		}
	}
}

// Returns the minimum read/write class levels of a forum
function get_forum_access_levels($forumid){ 
	$res = SQL_Query_exec("SELECT minclassread, minclasswrite FROM forum_forums WHERE id=$forumid");
	if (mysqli_num_rows($res) != 1)
		return false;
	$arr = mysqli_fetch_assoc($res);
		return array("read" => $arr["minclassread"], "write" => $arr["minclasswrite"]);
}


// Returns the forum ID of a topic, or false on error
function get_topic_forum($topicid) {
    $res = SQL_Query_exec("SELECT forumid FROM forum_topics WHERE id=$topicid");
    if (mysqli_num_rows($res) != 1)
      return false;
    $arr = mysqli_fetch_row($res);
    return $arr[0];
}

// Returns the ID of the last post of a forum

function update_topic_last_post($topicid) {
    $res = SQL_Query_exec("SELECT id FROM forum_posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1");
    $arr = mysqli_fetch_row($res) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_NO_POST_FOUND"));
    $postid = $arr[0];
    SQL_Query_exec("UPDATE forum_topics SET lastpost=$postid WHERE id=$topicid");
}


function get_forum_last_post($forumid)  {
    $res = SQL_Query_exec("SELECT lastpost FROM forum_topics WHERE forumid=$forumid ORDER BY lastpost DESC LIMIT 1");
    $arr = mysqli_fetch_row($res);
    $postid = $arr[0];
    if ($postid)
      return $postid;
    else
      return 0;

}

//Top forum posts

function forumpostertable($res) {
global $site_config;  //Define globals
	print("<br /><table align='center'width='50%' cellpadding='5'><tr><td>\n");
	print("<table class='ttable_headinner' width='100%' cellpadding='5'>");
    print("<tr class='ttable_head'><th width='10' align='center'><font size='1'>".T_("FORUM_RANK")."</font></th>
      <th width='140' align='center'><font size='1'>".T_("FORUM_USER")."</font></th>
      <th width='10' align='center'><font size='1'>".T_("FORUM_POST")."</font></th></tr>");
   
    $num = 0;
    while ($a = mysqli_fetch_assoc($res)) {
       $privacylevel = $a["privacy"];
	  ++$num;
 
	  $donated = $a["donated"];
	  $warned = $a["warned"];
	  
	  if ($privacylevel == "strong"){
	  if (get_user_class() <= 5){
	  print("<tr class='f-row'><td align='center' class='alt3'>$num</td><td class='alt2' style='text-align: justify'><b>".T_("ANONYMOUS")."</b></td><td align='center' class='alt3'>$a[num]</td></tr>\n");
	  }else{
	  if ($site_config["CLASS_USER"]) {
	  print("<tr class='f-row'><td align='center' class='alt3'>$num</td><td class='alt2' style='text-align: justify'><a href='account-details.php?id=$a[id]'><b>".class_user($a['username'])."</b></a></td><td align='center' class='alt3'>$a[num]</td></tr>\n");
	  }else{
	  print("<tr class='f-row'><td align='center' class='alt3'>$num</td><td class='alt2' style='text-align: justify'><a href='account-details.php?id=$a[id]'><b>".$a['username']."</b></a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "</td><td align='center' class='alt3'>$a[num]</td></tr>\n");
	  }
	  }
	  }else{
	  if ($site_config["CLASS_USER"]) {
	  print("<tr class='f-row'><td align='center' class='alt3'>$num</td><td class='alt2' style='text-align: justify'><a href='account-details.php?id=$a[id]'><b>".class_user($a['username'])."</b></a></td><td align='center' class='alt3'>$a[num]</td></tr>\n");
	  }else{
	  print("<tr class='f-row'><td align='center' class='alt3'>$num</td><td class='alt2' style='text-align: justify'><a href='account-details.php?id=$a[id]'><b>".$a['username']."</b></a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "</td><td align='center' class='alt3'>$a[num]</td></tr>\n");
	  }
	  }
	  }


    if ($num == 0)
    print("<tr class='t-row'><td align='center' class='ttable_col1' colspan='3'><b>".T_("FORUM_NO_FORUM_POSTER")."</b></td></tr>");
    
  print("</table>");
	 print("</td></tr></table>\n");
}

// Inserts a quick jump menu
function insert_quick_jump_menu($currentforum = 0) {
    print("<div style='text-align:right'><form method='get' action='?' name='jump'>\n");
    print("<input type='hidden' name='action' value='viewforum' />\n");
    $res = SQL_Query_exec("SELECT * FROM forum_forums ORDER BY name");
   
    if ( mysqli_num_rows($res) > 0 ) 
    {
         print( T_("FORUM_JUMP") . ": ");
         print("<select class='styled' name='forumid' onchange='if(this.options[this.selectedIndex].value != -1){ forms[jump].submit() }'>\n");
   
         while ($arr = mysqli_fetch_assoc($res))
         {
             if (get_user_class() >= $arr["minclassread"] || (!$CURUSER && $arr["guest_read"] == "yes"))
                 print("<option value='" . $arr["id"] . "'" . ($currentforum == $arr["id"] ? " selected='selected'>" : ">") . $arr["name"] . "</option>\n");
         }
         
         print("</select>\n");
         print("<button class='forumbutton'>".T_("FORUM_JUMP2")."</button><!--<input type='submit' value='".T_("GO")."' />-->\n");
    }

   // print("<input type='submit' value='Go!'>\n");
    print("</form>\n</div>");
}

// Inserts a compose frame
    function insert_compose_frame($id, $newtopic = true,$dossier) {
/*function insert_compose_frame($id, $newtopic = true) {*/
    global $maxsubjectlength, $site_config;

	if ($newtopic) {
		$res = SQL_Query_exec("SELECT name FROM forum_forums WHERE id=$id");
		$arr = mysqli_fetch_assoc($res) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_BAD_FORUM_ID"));
		$forumname = stripslashes($arr["name"]);

		print("<p align='center'><b>".T_("FORUM_NEW_TOPIC")." <a href='forums.php?action=viewforum&amp;forumid=$id'>$forumname</a></b></p>\n");
	}else{
		$res = SQL_Query_exec("SELECT * FROM forum_topics WHERE id=$id");
		$arr = mysqli_fetch_assoc($res) or show_error_msg(T_("FORUM_ERROR"), T_("FORUMS_NOT_FOUND_TOPIC"));
		$subject = stripslashes($arr["subject"]);
		print("<p align='center'>".T_("FORUM_REPLY_TOPIC").": <a href='forums.php?action=viewtopic&amp;topicid=$id'>$subject</a></p>");
	}

    # Language Marker #
    print("<p align='center'>".T_("FORUM_RULES")."\n");
    print("<br />".T_("FORUM_RULES2")."<br /></p>\n");


  #begin_frame("Compose Message", true);
     print("<fieldset class='download'>");
     print("<legend><b>".T_("FORUM_COMPOSE_NEW_TREAD")."</b></legend>");
     print("<div>");
    print("<form name='Form' method='post' action='?action=post'>\n");
    if ($newtopic)
      print("<input type='hidden' name='forumid' value='$id' />\n");
    else
      print("<input type='hidden' name='topicid' value='$id' />\n");

    if ($newtopic){
			print("<center><br /><table border='0' cellpadding='5' cellspacing='0'><tr><td align='center'><strong>".T_("FORUM_TITLE")." :</strong>  <input type='text' size='70' maxlength='$maxsubjectlength' name='subject' /></td></tr>");
			print("<tr><td align='center'>");
	if ($site_config["BBCODE_WITH_PREVIEW"]) {		
			textbbcode("Form", "body",$dossier);
	}else{
			textbbcode("Form", "body");
	}
			print("</td></tr><tr><td align='center'><br /><button class='forumbutton'>".T_("SUBMIT")."</button><!--<input type='submit' value='".T_("SUBMIT")."' />--><br /><br /></td></tr></table>");
	}
    print("<br /></center>");
    print("</form>\n");
    print("</div>");
    print("</fieldset><br />");
    #end_frame();

	insert_quick_jump_menu();
}

//LASTEST FORUM POSTS
function latestforumposts() {
global $site_config;  //Define globals
print("<div class='f-border f-latestpost'><table width='100%' border='0' cellspacing='0' cellpadding='5'><tr class='f-title'>".
"<th align='left'>".T_("FORUM_LATEST_TOPIC_TITLE")."</th>". 
"<th align='center'>".T_("FORUM_REPLIES")."</th>".
"<th align='center'>".T_("VIEWS")."</th>".
"<th align='center'>".T_("AUTHOR")."</th>".
"<th align='center'>".T_("FORUM_LAST_POST")."</th>".
"</tr>");


/// HERE GOES THE QUERY TO RETRIEVE DATA FROM THE DATABASE AND WE START LOOPING ///
$for = SQL_Query_exec("SELECT * FROM forum_topics ORDER BY lastpost DESC LIMIT 5");

if (mysqli_num_rows($for) == 0)
    print("<tr class='f-row'><td class='alt1' align='center' colspan='5'><b>".T_("FORUM_NO_LATEST_TOPICS")."</b></td></tr>");

while ($topicarr = mysqli_fetch_assoc($for)) {
// Set minclass
$res = SQL_Query_exec("SELECT name,minclassread,guest_read FROM forum_forums WHERE id=$topicarr[forumid]");
$forum = mysqli_fetch_assoc($res);

if ($forum && get_user_class() >= $forum["minclassread"] || $forum["guest_read"] == "yes") {
$forumname = "<a href='?action=viewforum&amp;forumid=$topicarr[forumid]'><b>" . htmlspecialchars($forum["name"]) . "</b></a>";

$topicid = $topicarr["id"];
$topic_title = stripslashes($topicarr["subject"]);
$topic_userid = $topicarr["userid"];
// Topic Views
$views = $topicarr["views"];
// End

/// GETTING TOTAL NUMBER OF POSTS ///
$res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
$arr = mysqli_fetch_row($res);
$posts = $arr[0];
$replies = max(0, $posts - 1);

/// GETTING USERID AND DATE OF LAST POST ///   
$res = SQL_Query_exec("SELECT * FROM forum_posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1");
$arr = mysqli_fetch_assoc($res);
$postid = 0 + $arr["id"];
$userid = 0 + $arr["userid"];
$added = utc_to_tz($arr["added"]);


/// GET NAME OF LAST POSTER ///
$poster_res = SQL_Query_exec("SELECT id, username, privacy, donated, warned FROM users WHERE id=$userid");
$arr = mysqli_fetch_assoc($poster_res);
if (mysqli_num_rows($poster_res) == 1) {


	  $privacylevel = $arr["privacy"];
	  $donated = $arr["donated"];
	  $warned = $arr["warned"];
	  
	  if ($privacylevel == "strong"){
	  if (get_user_class() <= 5){
	  $username = "".T_("ANONYMOUS")."";
	  }else{
	  if ($site_config["CLASS_USER"]) {
	  $username = "<a href='account-details.php?id=$userid'>".class_user($arr['username'])."</a>";
	  }else{
	  $username = "<a href='account-details.php?id=$userid'>".$arr['username']."</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
	  }
	  }
	  }else{
	  if ($site_config["CLASS_USER"]) {
	  $username = "<a href='account-details.php?id=$userid'>".class_user($arr['username'])."</a>";
	  }else{
	  $username = "<a href='account-details.php?id=$userid'>".$arr['username']."</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
	  }
	  }
	  }else
	  $username = "Unknown[$topic_userid]";
	  

/// GET NAME OF THE AUTHOR ///
$auther_res = SQL_Query_exec("SELECT username, privacy, donated, warned FROM users WHERE id=$topic_userid");
if (mysqli_num_rows($auther_res) == 1) {
$arr = mysqli_fetch_assoc($auther_res);
$donated = $arr["donated"];
$warned = $arr["warned"];
$privacylevel = $arr["privacy"];
if ($privacylevel == "strong"){
if (get_user_class() <= 5){
$author = "".T_("ANONYMOUS")."";
}else{
if ($site_config["CLASS_USER"]) {
$author = "<a href='account-details.php?id=$topic_userid'>".class_user($arr['username'])."</a>";
}else{
$author = "<a href='account-details.php?id=$topic_userid'>".$arr['username']."</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
}
}
}else{
if ($site_config["CLASS_USER"]) {
$author = "<a href='account-details.php?id=$topic_userid'>".class_user($arr['username'])."</a>";
}else{
$author = "<a href='account-details.php?id=$topic_userid'>".$arr['username']."</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
}
}
}else
$author = "Unknown[$topic_userid]";


/// GETTING THE LAST INFO AND MAKE THE TABLE ROWS ///
$r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
$a = mysqli_fetch_row($r);
$new = !$a || $postid > $a[0];
$subject = "<a href='forums.php?action=viewtopic&amp;topicid=$topicid'><b>" . stripslashes(encodehtml($topicarr["subject"])) . "</b></a>";

print("<tr class='f-row'><td class='f-img' width='65%'>$subject</td>".
"<td class='alt2' align='center'>$replies</td>" .
"<td class='alt3' align='center'>$views</td>" .
"<td class='alt2' width='10%' align='center'>".$author."</td>" .
"<td class='alt3' width='30%' align='left'>".T_("FORUM_BY").":&nbsp;".$username."<small style='white-space: nowrap'>&nbsp;$added</small></td>");

print("</tr>");
} // while
}
print("</table></div><br />");
} // end function

//Global variables
$postsperpage = 20;
$maxsubjectlength = 50;

//Action: New topic
if ($action == "newtopic") {
    $forumid = $_GET["forumid"];
    if (!is_valid_id($forumid))
    show_error_msg(T_("FORUM_ERROR"), "".T_("FORUM_NO_FORUM_ID")." $forumid");

    stdhead("".T_("FORUM_NEW_TOPIC1")."");
    begin_frame("".T_("FORUM_NEW_TOPIC1")."");

	forumheader("".T_("FORUM_COMPOSE_NEW_TREAD")."");

    insert_compose_frame($forumid,$newtopic = true,$dossier);
	/*insert_compose_frame($forumid);*/
    end_frame();
    stdfoot();
    die;
}


if ($action == "post") {
	$forumid = (isset($_POST["forumid"]) ? $_POST["forumid"] : "");
	$topicid = (isset($_POST["topicid"]) ? $_POST["topicid"] : "");

	if (!is_valid_id($forumid) && !is_valid_id($topicid))
		    show_error_msg(T_("FORUM_ERROR"), T_("FORUM_WOOT"));
	$newtopic = $forumid > 0;
	$subject = (isset($_POST["subject"]) ? $_POST["subject"] : "");
	if ($newtopic) {
		if (!$subject)
			show_error_msg(T_("ERROR"), T_("FORUM_MUST_ENTER_SUBJECT"));
		$subject = trim($subject);
		}else{
      $forumid = get_topic_forum($topicid) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_BAD_TOPIC_ID"));
	}

    ////// Make sure sure user has write access in forum
	$arr = get_forum_access_levels($forumid) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_BAD_FORUM_ID"));
	if (get_user_class() < $arr["write"])
		show_error_msg(T_("FORUM_ERROR"), T_("FORUMS_NOT_PERMIT"));
	$body = trim($_POST["body"]);
	if (!$body)
		show_error_msg(T_("ERROR"), T_("FORUM_BODY_TEXT"));
	$userid = $CURUSER["id"];

	if ($newtopic) { //Create topic
		$subject = sqlesc($subject);
		SQL_Query_exec("INSERT INTO forum_topics (userid, forumid, subject) VALUES($userid, $forumid, $subject)");
		$topicid = mysqli_insert_id($GLOBALS["DBconnector"]) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_NO_TOPIC_ID_RETURNED"));

	}else{
		//Make sure topic exists and is unlocked
		$res = SQL_Query_exec("SELECT * FROM forum_topics WHERE id=$topicid");
		$arr = mysqli_fetch_assoc($res) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_TOPIC_ID_N/A"));
		if ($arr["locked"] == 'yes')
        show_error_msg(T_("FORUM_ERROR"), T_("FORUM_TOPIC_LOCKED"));
		//Get forum ID
		$forumid = $arr["forumid"];
    }

    //Insert the new post
    $added = "'" . get_date_time() . "'";
    $body = sqlesc($body);
    SQL_Query_exec("INSERT INTO forum_posts (topicid, userid, added, body) VALUES($topicid, $userid, $added, $body)");
    $postid = mysqli_insert_id($GLOBALS["DBconnector"]) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_POST_ID_N/A"));

    //Update topic last post
    update_topic_last_post($topicid);

    //All done, redirect user to the post
    $headerstr = "Location: $site_config[SITEURL]/forums.php?action=viewtopic&topicid=$topicid&page=last";
    if ($newtopic)
		header($headerstr);
    else
		header("$headerstr#post$postid");
    die;
}

///////////////////////////////////////////////////////// Action: VIEW TOPIC
if ($action == "viewtopic") {
	$topicid = $_GET["topicid"];
	$page = (isset($_GET["page"]) ? $_GET["page"] : "");

	if (!is_valid_id($topicid))
        show_error_msg(T_("FORUM_ERROR"), T_("FORUM_TOPIC_NOT_VALID"));
	$userid = $CURUSER["id"];

    //------ Get topic info
    $res = SQL_Query_exec("SELECT * FROM forum_topics WHERE id=$topicid");
    $arr = mysqli_fetch_assoc($res) or show_error_msg(T_("FORUM_ERROR"), T_("FORUMS_NOT_FOUND_TOPIC"));
    $locked = ($arr["locked"] == 'yes');
    $subject = stripslashes($arr["subject"]);
	$sticky = $arr["sticky"] == "yes";
    $forumid = $arr["forumid"];
	
	// Check if user has access to this forum
	$res2 = SQL_Query_exec("SELECT minclassread, guest_read FROM forum_forums WHERE id=$forumid");
    $arr2 = mysqli_fetch_assoc($res2);
    if (!$arr2 || get_user_class() < $arr2["minclassread"] && $arr2["guest_read"] == "no")
        show_error_msg(T_("FORUM_ACCESS_DENIED"), T_("FORUM_NO_ACCESS"));

	// Update Topic Views
	$viewsq = SQL_Query_exec("SELECT views FROM forum_topics WHERE id=$topicid");
	$viewsa = mysqli_fetch_array($viewsq);
	$views = $viewsa[0];
	$new_views = $views+1;
	$uviews = SQL_Query_exec("UPDATE forum_topics SET views = $new_views WHERE id=$topicid");
	// End

    //------ Get forum
    $res = SQL_Query_exec("SELECT * FROM forum_forums WHERE id=$forumid");
    $arr = mysqli_fetch_assoc($res) or show_error_msg(T_("FORUM_ERROR"), T_("FORUM_IS_EMPTY"));
    $forum = stripslashes($arr["name"]);

    //------ Get post count
    $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
    $arr = mysqli_fetch_row($res);
    $postcount = $arr[0];         

    //------ Make page menu
    $pagemenu = "\n";
    $perpage = $postsperpage;
    $pages = floor($postcount / $perpage);
    if ($pages * $perpage < $postcount)
		++$pages;
    if ($page == "last")
		$page = $pages;
    else {
		if($page < 1)
			$page = 1;
		elseif ($page > $pages)
			$page = $pages;
    }
    $offset = max( 0, ( $page * $perpage ) - $perpage );  
    
	//
    if ($page == 1)
      $pagemenu .= "<center><b>&lt;&lt; ".T_("FORUM_PREV")."</b>";
    else
      $pagemenu .= "<center><a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page - 1) . "'><b>&lt;&lt; ".T_("FORUM_PREV")."</b></a>";
	//
	$pagemenu .= "&nbsp;&nbsp;";
	    for ($i = 1; $i <= $pages; ++$i) {
      if ($i == $page)
        $pagemenu .= "<b>$i</b>\n";
      else
        $pagemenu .= "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'><b>$i</b></a>\n";
    }
	//
    $pagemenu .= "&nbsp;&nbsp;";
    if ($page == $pages)
      $pagemenu .= "<b>".T_("FORUM_NEXT")." &gt;&gt;</b><br /><br />\n";
    else
      $pagemenu .= "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page + 1) . "'><b>".T_("FORUM_NEXT")." &gt;&gt;</b></a><br /><br />\n";
    $pagemenu .= "<br /></center>";
      
//Get topic posts
    $res = SQL_Query_exec("SELECT * FROM forum_posts WHERE topicid=$topicid ORDER BY id LIMIT $offset,$perpage");

    stdhead("".T_("FORUM_VIEW_TOPIC")." $subject");
    begin_frame("$forum &gt; $subject");
	forumheader("<a href='forums.php?action=viewforum&amp;forumid=$forumid'>$forum</a> <b style='font-size:16px; vertical-align:middle'>/</b> $subject");
	
	print ("<div style='padding: 10px'>");
	
	$levels = get_forum_access_levels($forumid) or die;
	if (get_user_class() >= $levels["write"])
		$maypost = true;
	else
		$maypost = false;
	
	if (!$locked && $maypost){
	print ("<div align='right'><a href='#bottom'><button class='forumbutton'>".T_("BTN_FORUM_POST_REPLY")."</button><!--<input  value='".T_("BTN_FORUM_POST_REPLY")."' type='button' alt='' />--></a></div>");
	}else{
		print ("<div align='right'><button class='forumbutton'>".T_("FORUMS_LOCKED")."</button><!--<img src='" . $themedir . "button_locked.png'  alt='".T_("FORUMS_LOCKED")."' title='".T_("FORUMS_TOPIC_LOCKED")."' />--></div>");
	}
	print ("</div>");

//------ Print table of posts
    print($pagemenu);
	
	$pc = mysqli_num_rows($res);
    $pn = 0;
	if ($CURUSER) {
	    $r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$CURUSER[id] AND topicid=$topicid");
	    $a = mysqli_fetch_row($r);
	    $lpr = $a[0];
	    if (!$lpr)
			SQL_Query_exec("INSERT INTO forum_readposts (userid, topicid) VALUES($userid, $topicid)");
	}
	
    while ($arr = mysqli_fetch_assoc($res)) {
		++$pn;
		$postid = $arr["id"];
		$posterid = $arr["userid"];
		$added = utc_to_tz($arr["added"])." ( " . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " " . T_("AGO") . " )";

		//---- Get poster details
		$res4 = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE userid=$posterid");
		$arr33 = mysqli_fetch_row($res4);
		$forumposts = $arr33[0];

		$res2 = SQL_Query_exec("SELECT * FROM users WHERE id=$posterid");
		$arr2 = mysqli_fetch_assoc($res2);
		$postername = $arr2["username"];// Username användarnamnet

			if ($postername == "") {
				$by = "".T_("DELUSER")."";
				$title = "".T_("DELETED_ACCOUNT")."";
				$privacylevel = "strong";
				$usersignature = "";
				$userdownloaded = "0";
				$useruploaded = "0";
				$avatar = "";
				$nposts = "-";
				$tposts = "-";
			}else{
				$id = $postid;
				$avatar = htmlspecialchars($arr2["avatar"]);
				$userdownloaded = mksize($arr2["downloaded"]);
				$useruploaded = mksize($arr2["uploaded"]);
				$privacylevel = $arr2["privacy"];
				$usersignature = stripslashes(format_comment($arr2["signature"]));
					if ($arr2["downloaded"] > 0) {
						$userratio = number_format($arr2["uploaded"] / $arr2["downloaded"], 2);
					}else
						if ($arr2["uploaded"] > 0)
							$userratio = "Inf.";
						else
							$userratio = "---";
        
					if(!$arr2["country"]){
						$usercountry = "".T_("FORUM_UNKNOWN")."";
					}else{
						$res4 = SQL_Query_exec("SELECT name,flagpic FROM countries WHERE id=$arr2[country] LIMIT 1");
						$arr4 = mysqli_fetch_assoc($res4);
						$usercountry = $arr4["name"];
					}

				
	$privacylevel = $arr2["privacy"];
	$title = format_comment($arr2["title"]);  
	$donated = $arr2["donated"];
	$warned = $arr2["warned"];
	  
if ($privacylevel == "strong"){
if (get_user_class() <= 5){
	$by = "<center>".T_("ANONYMOUS")."</center>";
	  }else{
if ($site_config["CLASS_USER"]) {
	$by = "<center><a href='account-details.php?id=$posterid'>".class_user($postername)."</a></center>";
	  }else{
	$by = "<center><a href='account-details.php?id=$posterid'>$postername</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "</center>";
}
}
}else{
if ($site_config["CLASS_USER"]) {
	$by = "<center><a href='account-details.php?id=$posterid'>".class_user($postername)."</a></center>";
}else{
	$by = "<center><a href='account-details.php?id=$posterid'>$postername</a> " . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "</center>";
}
}
}
if (!$avatar)
	$avatar = $site_config['SITEURL']."/images/default_avatar.png";
	# print("<a name=$postid>\n");
		print("<a id='post$postid'></a>");
if ($pn == $pc) {
		print("<a name='last'></a>\n");
if ($postid > $lpr && $CURUSER)
SQL_Query_exec("UPDATE forum_readposts SET lastpostread=$postid WHERE userid=$userid AND topicid=$topicid");
}
//working here
//Post Top

		print("<div class='f-border f-post'><table width='100%' cellspacing='0' cellpadding='5'><tr class='p-title'><th width='150'>".$by."</th><th align='right'><small>".T_("POSTED_AT")." $added </small> #".$id."</th></tr>");

//Post Middle

	$body = stripslashes(format_comment($arr["body"]));

if (is_valid_id($arr['editedby'])) {
	$res2 = SQL_Query_exec("SELECT username, privacy, donated, warned FROM users WHERE id=$arr[editedby]");

if (mysqli_num_rows($res2) == 1) {
	$arr2 = mysqli_fetch_assoc($res2);
//anonym Edit by		
	$privacylevel = $arr2["privacy"];
		
if ($privacylevel == "strong"){
if (get_user_class() <= 5){
	$body .= "<br /><br /><small><i>".T_("FORUM_LAST_EDIT_BY")." ".T_("ANONYMOUS")."</b> on ".utc_to_tz($arr["editedat"])."</i></small><br />\n";// "Edit by" namnnet
	}else{
if ($site_config["CLASS_USER"]) {
	$body .= "<br /><br /><small><i>".T_("FORUM_LAST_EDIT_BY")." <a href='account-details.php?id=$arr[editedby]'>".class_user($arr2['username'])."</b></a> on ".utc_to_tz($arr["editedat"])."</i></small><br />\n";// "Edit by" namnnet
	}else{
	$body .= "<br /><br /><small><i>".T_("FORUM_LAST_EDIT_BY")." <a href='account-details.php?id=$arr[editedby]'>".$arr2['username']."</b></a> " . ($arr2['donated'] > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($arr2['warned'] > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . " on ".utc_to_tz($arr["editedat"])."</i></small><br />\n";// "Edit by" namnnet	
}
}
	}else{	
if ($site_config["CLASS_USER"]) {
	$body .= "<br /><br /><small><i>".T_("FORUM_LAST_EDIT_BY")." <a href='account-details.php?id=$arr[editedby]'>".class_user($arr2['username'])."</b></a> on ".utc_to_tz($arr["editedat"])."</i></small><br />\n";// "Edit by" namnnet
	}else{
	$body .= "<br /><br /><small><i>".T_("FORUM_LAST_EDIT_BY")." <a href='account-details.php?id=$arr[editedby]'>".$arr2['username']."</b></a> " . ($arr2['donated'] > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($arr2['warned'] > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . " on ".utc_to_tz($arr["editedat"])."</i></small><br />\n";// "Edit by" namnnet	
}//($site_config["CLASS_USER"])
	$body .= "\n";
}//anonym
}//(mysqli_num_rows($res2) == 1)
}//(is_valid_id($arr['editedby']))

	
	$quote = htmlspecialchars($arr["body"]);
	$res22 = SQL_Query_exec("SELECT * FROM users WHERE id=$posterid");
	$arr22 = mysqli_fetch_assoc($res22);
	
	$postcount1 = SQL_Query_exec("SELECT COUNT(forum_posts.userid) FROM forum_posts WHERE id=$posterid") or forumsqlerr();
	while($row = mysqli_fetch_array($postcount1)) {

	$privacylevel = $arr22["privacy"];
if ($privacylevel == "strong" && $CURUSER["control_panel"] != "yes") { //hide stats, but not from staff
	$useruploaded = "---";
	$userdownloaded = "---";
	$userratio = "---";
	$nposts = "-";
	$tposts = "-";
	$avatar = $site_config['SITEURL']."/images/default_avatar.png";
	$usercountry = "---";
}
		print ("<tr valign='top''><td width='150' align='left' class='comment-details'><center><i>".$title."</i></center><br /><center><img width='80' height='80' src='$avatar' alt='' /></center><br />".T_("UPLOADED").": $useruploaded<br />".T_("DOWNLOADED").": $userdownloaded<br />".T_("FORUM_POST").": $forumposts<br /><br />".T_("RATIO").": $userratio<br />".T_("FORUM_LOCATION").": $usercountry<br /><br /></td>");
		print ("<td class='comment'><br />$body<br />");

if (!$usersignature){
		print("<br /></td></tr>\n");
	}else{
		print("<br /><hr /><br /><div class='f-sig' align='center'>$usersignature</div></td></tr>\n");
}
}

//Post Bottom
//anonymous buttons + Quotes
	
if ($privacylevel == "strong" && $CURUSER["control_panel"] != "yes") {
		print("<tr class='p-foot'><td width='150' align='center'><button class='forumbutton'>".T_("BTN_FORUM_PROFILE")."</button><!--<input  value='Profile' type='button' alt='' />--> <button class='forumbutton'>".T_("BTN_FORUM_PM")."</button><!--<input  value='PM' type='button' alt='' />--></td><td>");
	}else{
		print("<tr class='p-foot'><td width='150' align='center'><a href='account-details.php?id=$posterid'><button class='forumbutton'>".T_("BTN_FORUM_PROFILE")."</button><!--<input value='Profile' type='button' alt='' />--></a> <a href='mailbox.php?compose&amp;id=$posterid'><button class='forumbutton'>".T_("BTN_FORUM_PM")."</button><!--<input  value='PM' type='button' alt='' />--></a></td><td>");
}
		print ("<div style='float: left;'><a href='report.php?forumid=$topicid&amp;forumpost=$postid'><button class='forumbutton'>".T_("BTN_FORUM_REPORT")."</button><!--<input  value='Report' type='button' alt='' />--></a>&nbsp;<a href='javascript:scroll(0,0);'><button class='forumbutton'>".T_("BTN_FORUM_TOP")."</button><!--<input  value='▲' type='button' alt='' />--></a></div><div align='right'>");
	
	
	$privacylevel = $arr2["privacy"];

if ($privacylevel == "strong"){
	$postername = "".T_("ANONYMOUS")."";
}


//define buttons and who can use them

if ($CURUSER["id"] == $posterid || $CURUSER["edit_forum"] == "yes" || $CURUSER["delete_forum"] == "yes"){
		print ("<a href='forums.php?action=editpost&amp;postid=$postid'><button class='forumbutton'>".T_("BTN_FORUM_EDIT")."</button><!--<input  value='Edit' type='button' alt='' />--></a>&nbsp;");
}

if ($CURUSER["delete_forum"] == "yes"){
		print ("<a href='forums.php?action=deletepost&amp;postid=$postid&amp;sure=0'><button class='forumbutton'>".T_("BTN_FORUM_DELETE")."</button><!--<input  value='Delete' type='button' alt='' />--></a>&nbsp;");
}

if (!$locked && $maypost) {
		print ("<a href=\"javascript:SmileIT('[quote=$postername] $quote [/quote]', 'Form', 'body');\"><button class='forumbutton'>".T_("BTN_FORUM_QUOTE")."</button><!--<input  value='Quote' type='button' alt='' />--></a>&nbsp;");
		print ("<a href='#bottom'><button class='forumbutton'>".T_("BTN_FORUM_ADD_REPLY")."</button><!--<input  value='add REPLY' type='button' alt='' />--></a>");
}
		print("&nbsp;</div></td></tr></table></div><br /><br />");
}

//-------- end posts table ---------//
		print($pagemenu);

//quick reply
if (!$locked && $maypost){
		print ("<fieldset class='download'><legend><b>".T_("FORUMS_POST_REPLY")."</b></legend>");
	$newtopic = false;
		print("<a name='bottom'></a>");
		print("<form name='Form' method='post' action='?action=post'>\n");
if ($newtopic)
		print("<input type='hidden' name='forumid' value='$id' />\n");
    else
		print("<input type='hidden' name='topicid' value='$topicid' />\n");

		print("<table cellspacing='0' cellpadding='5' align='center'>");
if ($newtopic)
		print("<tr><td class='alt2'>".T_("FORUMS_SUBJECT")."</td><td class='alt1' align='left' style='padding: 0px'><input type='text' size='100' maxlength='$maxsubjectlength' name='subject' style='border: 0px; height: 19px' /></td></tr>\n");

		echo "<tr><td align='center' colspan='3'>";
if ($site_config["BBCODE_WITH_PREVIEW"]) {
	textbbcode("Form", "body",$dossier);
	}else{
	textbbcode("Form", "body");
}
		echo "</td></tr>\n";
		print("<tr><td colspan='3' align='center'><br /><button class='forumbutton'>".T_("BTN_FORUM_POST_REPLY")."</button><!--<input  value='".T_("BTN_FORUM_POST_REPLY")."' type='button' alt='' />--></td></tr>\n");
		print("</table></form>\n");
		print (" </fieldset>");
	}else{
		print ("<div align='left'><button class='forumbutton'>".T_("FORUMS_LOCKED")."</button><!--<img src='".$themedir."button_locked.png' alt='".T_("FORUMS_LOCKED")."' title='".T_("FORUMS_TOPIC_LOCKED")."' />--></div><br />");
}
	//end quick reply

    //insert page numbers and quick jump

   // insert_quick_jump_menu($forumid);

// MODERATOR OPTIONS
if ($CURUSER["delete_forum"] == "yes" || $CURUSER["edit_forum"] == "yes") {
		print("<br /><div class='f-border f-mod_options' align='center'><table width='100%' cellspacing='0' cellpadding='5'><tr class='f-title'><th>".T_("FORUMS_MOD_OPTIONS")."</th></tr>\n");
	$res = SQL_Query_exec("SELECT id,name,minclasswrite FROM forum_forums ORDER BY name");
		print("<tr><td class='ttable_col2'>\n");
		print("<form method='post' action='forums.php?action=renametopic'>\n");
		print("<input type='hidden' name='topicid' value='$topicid' />\n");
		print("<input type='hidden' name='returnto' value='forums.php?action=viewtopic&amp;topicid=$topicid' />\n");
		print("<div align='center'  style='padding:3px'>".T_("FORUM_RENAME_TOPIC")." <input type='text' name='subject' size='60' maxlength='$maxsubjectlength' value='" . stripslashes(htmlspecialchars($subject)) . "' />\n");
		print("<button class='forumbutton'>".T_("APPLY")."</button><!--<input type='submit' value='".T_("APPLY")."' />-->");
		print("</div></form>\n");
		print("<form method='post' action='forums.php?action=movetopic&amp;topicid=$topicid'>\n");
		print("<div align='center' style='padding:3px'>");
		print("".T_("FORUM_MOVE_THREAD")." <select name='forumid'>");
while ($arr = mysqli_fetch_assoc($res))
if ($arr["id"] != $forumid && get_user_class() >= $arr["minclasswrite"])
		print("<option value='" . $arr["id"] . "'>" . $arr["name"] . "</option>\n");
		print("</select> <button class='forumbutton'>".T_("APPLY")."</button><!--<input type='submit' value='".T_("APPL")."' />--></div></form>\n");
		print("<div align='center'>\n");
if ($locked)
		print(T_("FORUMS_LOCKED").": <a href='forums.php?action=unlocktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='Unlock'><img src='". $themedir ."topic_unlock.png' alt='[".T_("FORUM_UNLOCK_TOPIC")."]' /></a>\n");
	else
		print(T_("FORUMS_LOCKED").": <a href='forums.php?action=locktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='Lock'><img src='". $themedir ."topic_lock.png' alt='[".T_("FORUM_LOCK_TOPIC")."]' /></a>\n");
		print(T_("FORUM_DELETE_ENTIRE_TOPIC").": <a href='forums.php?action=deletetopic&amp;topicid=$topicid&amp;sure=0' title='Delete'><img src='". $themedir ."topic_delete.png' alt='[".T_("FORUM_DELETE_TOPIC")."]' /></a>\n");
if ($sticky)
		print(T_("FORUMS_STICKY").": <a href='forums.php?action=unsetsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='UnStick'><img src='". $themedir ."folder_sticky_new.png' alt='[".T_("FORUM_UNSTICK_TOPIC")."]' /></a>\n");
	else
		print(T_("FORUMS_STICKY").": <a href='forums.php?action=setsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='Stick'><img src='". $themedir ."folder_sticky.png' alt='[".T_("FORUM_STICK_TOPIC")."]' /></a>\n");
		print("</div><br /></td></tr></table></div><br />\n");
}
end_frame();
stdfoot();
die;
}

///////////////////////////////////////////////////////// Action: REPLY
if ($action == "reply") {
	$topicid = $_GET["topicid"];
if (!is_valid_id($topicid))
    show_error_msg(T_("FORUM_ERROR"), sprintf(T_("FORUMS_NO_ID_FORUM"), $topicid));
stdhead(T_("FORUMS_POST_REPLY"));
begin_frame(T_("FORUMS_POST_REPLY"));
	insert_compose_frame($topicid, false);
end_frame();
stdfoot();
die;
}

///////////////////////////////////////////////////////// Action: MOVE TOPIC
if ($action == "movetopic") {
    $forumid = $_POST["forumid"];
    $topicid = $_GET["topicid"];
    if (!is_valid_id($forumid) || !is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes" || $CURUSER["edit_forum"] != "yes")
         show_error_msg(T_("FORUM_ERROR"), sprintf(T_("FORUMS_NO_ID_FORUM"),$forumid,$topicid));

    // Make sure topic and forum is valid
    $res = @SQL_Query_exec("SELECT minclasswrite FROM forum_forums WHERE id=$forumid");
    if (mysqli_num_rows($res) != 1)
      show_error_msg(T_("ERROR"), T_("FORUMS_NOT_FOUND"));
    $arr = mysqli_fetch_row($res);
    if (get_user_class() < $arr[0])
    show_error_msg(T_("FORUM_ERROR"), T_("FORUMS_NOT_ALLOWED"));
    $res = @SQL_Query_exec("SELECT subject,forumid FROM forum_topics WHERE id=$topicid");
    if (mysqli_num_rows($res) != 1)
      show_error_msg(T_("ERROR"), T_("FORUMS_NOT_FOUND_TOPIC"));
    $arr = mysqli_fetch_assoc($res);
    if ($arr["forumid"] != $forumid)
      @SQL_Query_exec("UPDATE forum_topics SET forumid=$forumid, moved='yes' WHERE id=$topicid");

    // Redirect to forum page
    header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid");
    die;
}

///////////////////////////////////////////////////////// Action: DELETE TOPIC
if ($action == "deletetopic") {
	$topicid = $_GET["topicid"];
	if (!is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes")
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
	
	$sure = $_GET["sure"];
	if ($sure == "0") 
		show_error_msg(T_("FORUMS_DEL_TOPIC"), sprintf(T_("FORUMS_DEL_TOPIC_SANITY_CHK"), $topicid));

	SQL_Query_exec("DELETE FROM forum_topics WHERE id=$topicid");
	SQL_Query_exec("DELETE FROM forum_posts WHERE topicid=$topicid");
    SQL_Query_exec("DELETE FROM forum_readposts WHERE topicid=$topicid");
	header("Location: $site_config[SITEURL]/forums.php");
	die;
}

///////////////////////////////////////////////////////// Action: EDIT TOPIC
if ($action == "editpost") {
	$postid = $_GET["postid"];
	if (!is_valid_id($postid))
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
    $res = SQL_Query_exec("SELECT * FROM forum_posts WHERE id=$postid");
	if (mysqli_num_rows($res) != 1)
		show_error_msg(T_("ERROR"), sprintf(T_("FORUMS_NO_ID_POST"), $postid));
	$arr = mysqli_fetch_assoc($res);
    if ($CURUSER["id"] != $arr["userid"] && $CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes")
		show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$body = $_POST['body'];
			if ($body == "")
				show_error_msg(T_("ERROR"), T_("FORUM_CANNOT_BE_EMPTY"));
		$body = sqlesc($body);
		$editedat = sqlesc(get_date_time());
		SQL_Query_exec("UPDATE forum_posts SET body=$body, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$postid");
		$returnto = $_POST["returnto"];
			if ($returnto != "")
				header("Location: $returnto");
			else
				show_error_msg(T_("SUCCESS"), T_("FORUM_POST_SUCCESSFULLY"));
	}

    stdhead();

    begin_frame(T_("FORUMS_EDIT_POST"));
    print("<form name='Form' method='post' action='?action=editpost&amp;postid=$postid'>\n");
    print("<input type='hidden' name='returnto' value='" . htmlspecialchars($_SERVER["HTTP_REFERER"]) . "' />\n");
    print("<center><table  cellspacing='0' cellpadding='5'>\n");
    print("<tr><td colspan='2'>\n");
    
	if ($site_config["BBCODE_WITH_PREVIEW"]) {
	textbbcode("Form", "body",$dossier, htmlspecialchars($arr["body"]));
	}else{
	textbbcode("Form", "body", htmlspecialchars($arr["body"]));
	}
    print("</td></tr>");

    print("<tr><td align='center' colspan='2'><input type='submit' value='".T_("SUBMIT")."' /></td></tr>\n");
    print("</table></center>\n");
    print("</form>\n");
    end_frame();
    stdfoot();
    die;
}

///////////////////////////////////////////////////////// Action: DELETE POST
if ($action == "deletepost") {
	$postid = $_GET["postid"];
	$sure = $_GET["sure"];
	if ($CURUSER["delete_forum"] != "yes" || !is_valid_id($postid))
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));

    //SURE?
	if ($sure == "0") {
		show_error_msg(T_("FORUMS_DEL_POST"), sprintf(T_("FORUMS_DEL_POST_SANITY_CHK"), $postid));
    }

	//------- Get topic id
    $res = SQL_Query_exec("SELECT topicid FROM forum_posts WHERE id=$postid");
    $arr = mysqli_fetch_row($res) or show_error_msg(T_("ERROR"), T_("FORUMS_NOT_FOUND_POST"));
    $topicid = $arr[0];

    //------- We can not delete the post if it is the only one of the topic
    $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
    $arr = mysqli_fetch_row($res);
    if ($arr[0] < 2)
		show_error_msg(T_("ERROR"), sprintf(T_("FORUMS_DEL_POST_ONLY_POST"), $topicid));

    //------- Delete post
    SQL_Query_exec("DELETE FROM forum_posts WHERE id=$postid");

    //------- Update topic
    update_topic_last_post($topicid);
    header("Location: $site_config[SITEURL]/forums.php?action=viewtopic&topicid=$topicid");
    die;
}

///////////////////////////////////////////////////////// Action: LOCK TOPIC
if ($action == "locktopic") {
	$forumid = $_GET["forumid"];
	$topicid = $_GET["topicid"];
	$page = $_GET["page"];
	if (!is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes" || $CURUSER["edit_forum"] != "yes")
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
	SQL_Query_exec("UPDATE forum_topics SET locked='yes' WHERE id=$topicid");
	header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
	die;
}

///////////////////////////////////////////////////////// Action: UNLOCK TOPIC
if ($action == "unlocktopic") {
    $forumid = $_GET["forumid"];
    $topicid = $_GET["topicid"];
    $page = $_GET["page"];
    if (!is_valid_id($topicid) || $CURUSER["delete_forum"] != "yes" || $CURUSER["edit_forum"] != "yes")
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
    SQL_Query_exec("UPDATE forum_topics SET locked='no' WHERE id=$topicid");
    header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
    die;
}

///////////////////////////////////////////////////////// Action: STICK TOPIC
if ($action == "setsticky") {
   $forumid = $_GET["forumid"];
   $topicid = $_GET["topicid"];
   $page = $_GET["page"];
   if (!is_valid_id($topicid) || ($CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes"))
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
   SQL_Query_exec("UPDATE forum_topics SET sticky='yes' WHERE id=$topicid");
   header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
   die;
}

///////////////////////////////////////////////////////// Action: UNSTICK TOPIC
if ($action == "unsetsticky") {
   $forumid = $_GET["forumid"];
   $topicid = $_GET["topicid"];
   $page = $_GET["page"];
   if (!is_valid_id($topicid) || ($CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes"))
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
   SQL_Query_exec("UPDATE forum_topics SET sticky='no' WHERE id=$topicid");
   header("Location: $site_config[SITEURL]/forums.php?action=viewforum&forumid=$forumid&page=$page");
   die;
}

///////////////////////////////////////////////////////// Action: RENAME TOPIC
if ($action == 'renametopic') {
	if ($CURUSER["delete_forum"] != "yes" && $CURUSER["edit_forum"] != "yes")
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
  	$topicid = $_POST['topicid'];
  	if (!is_valid_id($topicid))
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
  	$subject = $_POST['subject'];
 	if ($subject == '')
		show_error_msg(T_("ERROR"), T_("FORUMS_YOU_MUST_ENTER_NEW_TITLE"));
  	$subject = sqlesc($subject);
  	SQL_Query_exec("UPDATE forum_topics SET subject=$subject WHERE id=$topicid");
  	$returnto = $_POST['returnto'];
  	if ($returnto)
		header("Location: $returnto");
  	die;
}

///////////////////////////////////////////////////////// Action: VIEW FORUM
if ($action == "viewforum") {
	$forumid = $_GET["forumid"];
	if (!is_valid_id($forumid))
        show_error_msg(T_("ERROR"), T_("FORUMS_DENIED"));
    $page = (isset($_GET["page"]) ? $_GET["page"] : "");
    $userid = $CURUSER["id"];

    //------ Get forum name
    $res = SQL_Query_exec("SELECT name, minclassread, guest_read FROM forum_forums WHERE id=$forumid");
    $arr = mysqli_fetch_assoc($res);
    $forumname = $arr["name"];
    if (!$forumname || get_user_class() < $arr["minclassread"] && $arr["guest_read"] == "no")
		show_error_msg(T_("ERROR"), T_("FORUMS_NOT_PERMIT"));

    //------ Get topic count
    $perpage = 20;
    $res = SQL_Query_exec("SELECT COUNT(*) FROM forum_topics WHERE forumid=$forumid");
    $arr = mysqli_fetch_row($res);
    $num = $arr[0];
    $page = intval($page);

    if ($page == 0)
      $page = 1;
    $first = ($page * $perpage) - $perpage + 1;
    $last = $first + $perpage - 1;
    if ($last > $num)
      $last = $num;
    $pages = floor($num / $perpage);
    if ($perpage * $pages < $num)
      ++$pages;

    //------ Build menu
    $menu = "<p align='center'><b>\n";
    $lastspace = false;
    for ($i = 1; $i <= $pages; ++$i) {
      if ($i == $page)
        $menu .= "<span class='next-prev'>$i</span>\n";
      elseif ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3)) {
    	if ($lastspace)
          continue;
   	    $menu .= "... \n";
    	$lastspace = true;
      }
      else {
        $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=$i'>$i</a>\n";
        $lastspace = false;
      }
      if ($i < $pages)
        $menu .= "</b>|<b>\n";
    }
    $menu .= "<br />\n";
    if ($page == 1)
      $menu .= "<span class='next-prev'>&lt;&lt; ".T_("FORUM_PREV")."</span>";
    else
      $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page - 1) . "'>&lt;&lt; ".T_("FORUM_PREV")."</a>";
    $menu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    if ($last == $num)
      $menu .= "<span class='next-prev'>".T_("FORUM_NEXT")." &gt;&gt;</span>";
    else
      $menu .= "<a href='forums.php?action=viewforum&amp;forumid=$forumid&page=" . ($page + 1) . "'>".T_("FORUM_NEXT")." &gt;&gt;</a>";
    $menu .= "</b></p>\n";
    $offset = $first - 1;

    //------ Get topics data and display category
    $topicsres = SQL_Query_exec("SELECT * FROM forum_topics WHERE forumid=$forumid ORDER BY sticky, lastpost DESC LIMIT $offset,$perpage");

    stdhead("Forum : $forumname");
    $numtopics = mysqli_num_rows($topicsres);
    begin_frame("$forumname");
	forumheader("<a href='forums.php?action=viewforum&amp;forumid=$forumid'>$forumname</a>");
	$arr = get_forum_access_levels($forumid) or die;
    $maypost = get_user_class() >= $arr["write"];
	$maypost = (isset($maypost) ? $maypost : "");
/*	
	if (!$maypost) {
		print("<p align='right'><i>".T_("FORUMS_YOU_NOT_PERM_POST_FORUM")."</i></p>\n");
	}else*/
	if ($maypost) {
		print ("<table border='0' cellpadding='2' cellspacing='4' width='100%'><tr><td><div align='right'><a href='forums.php?action=newtopic&amp;forumid=$forumid'><button class='forumbutton'>".T_("BTN_FORUM_NEW_POST")."</button><!--<input  value='NEW POST' type='button' alt='' />--></a></div></td></tr></table><br />");
	}
	
   if ($numtopics > 0) {
	print("<div class='f-border f-sub_forum'> <table width='100%' cellspacing='0' cellpadding='5'>");

	print("<tr class='f-title'><th align='left' colspan='2'>".T_("FORUM_TOPIC")."</th><th align='center'>".T_("FORUM_REPLIES")."</th><th align='center'>".T_("VIEWS")."</th><th align='center'>".T_("AUTHOR")."</th><th align='center'>".T_("FORUM_LAST_POST")."</th>\n");
		if ($CURUSER["edit_forum"] == "yes" || $CURUSER["delete_forum"] == "yes")
			print("<th>".T_("MODERATOR")."</th>");
      print("</tr>\n");
      while ($topicarr = mysqli_fetch_assoc($topicsres)) {
			$topicid = $topicarr["id"];
			$topic_userid = $topicarr["userid"];
			$locked = $topicarr["locked"] == "yes";
			$moved = $topicarr["moved"] == "yes";
			$sticky = $topicarr["sticky"] == "yes";
			//---- Get reply count
			$res = SQL_Query_exec("SELECT COUNT(*) FROM forum_posts WHERE topicid=$topicid");
			$arr = mysqli_fetch_row($res);
			$posts = $arr[0];
			$replies = max(0, $posts - 1);
			$tpages = floor($posts / $postsperpage);
			if ($tpages * $postsperpage != $posts)
			  ++$tpages;
			if ($tpages > 1) {
			  $topicpages = " (<img src='". (isset($site_config['SITEURL'])) . "/images/forum/multipage.png' alt='' />";
			  for ($i = 1; $i <= $tpages; ++$i)
				$topicpages .= " <a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'>$i</a>";
			  $topicpages .= ")";
        }
        else
          $topicpages = "";

        //---- Get userID and date of last post
        $res = SQL_Query_exec("SELECT * FROM forum_posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1");
        $arr = mysqli_fetch_assoc($res);
        $lppostid = $arr["id"];
        $lpuserid = ( int ) $arr["userid"];
        $lpadded = utc_to_tz($arr["added"]);

		
		

		
        //------ Get name of last poster
        if ($lpuserid > 0) {
        $res = SQL_Query_exec("SELECT * FROM users WHERE id=$lpuserid");
        if (mysqli_num_rows($res) == 1) {
        $arr = mysqli_fetch_assoc($res);
		$donated = $arr['donated'];
		$warned = $arr['warned'];
		  
		  //anonym          
		$privacylevel = $arr["privacy"];
		if ($privacylevel == "strong"){
		if (get_user_class() <= 5){
		$lpusername = "".T_("ANONYMOUS").""; 
		}else{
		if ($site_config["CLASS_USER"]) {
		$lpusername = "<a href='account-details.php?id=$lpuserid'>".class_user($arr['username'])."</a>";
		}else{
		$lpusername = "<a href='account-details.php?id=$lpuserid'>".$arr['username']."</a>&nbsp;" . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
   		} //$site_config["CLASS_USER"]
		}
		}else{
		if ($site_config["CLASS_USER"]) {
		$lpusername = "<a href='account-details.php?id=$lpuserid'>".class_user($arr['username'])."</a>";
		}else{
		$lpusername = "<a href='account-details.php?id=$lpuserid'>".$arr['username']."</a>&nbsp;" . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
		}
		}
		}else
		$lpusername = "".T_("FORUM_DELUSER")."";
		}else
        $lpusername = "".T_("FORUM_DELUSER")."";


        //------ Get author
        if ($topic_userid > 0) {
        $res = SQL_Query_exec("SELECT username, privacy, warned, donated FROM users WHERE id=$topic_userid");
        if (mysqli_num_rows($res) == 1) {
          $arr = mysqli_fetch_assoc($res);

	    //anonymous          
		$privacylevel = $arr["privacy"];
		if ($privacylevel == "strong"){
		if (get_user_class() <= 5){
		$lpauthor = "".T_("ANONYMOUS").""; 
		}else{
		if ($site_config["CLASS_USER"]) {
		$lpauthor = "<a href='account-details.php?id=$lpuserid'>".class_user($arr['username'])."</a>";
		}else{
		$lpauthor = "<a href='account-details.php?id=$lpuserid'>".$arr['username']."&nbsp;</a>" . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
   		} //$site_config["CLASS_USER"]
		}
		}else{
		if ($site_config["CLASS_USER"]) {
		$lpauthor = "<a href='account-details.php?id=$lpuserid'>".class_user($arr['username'])."</a>";
		}else{
		$lpauthor = "<a href='account-details.php?id=$lpuserid'>".$arr['username']."&nbsp;</a>" . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
		}
		}
		}else
		$lpauthor = "".T_("FORUM_DELUSER")."";
		}else
        $lpauthor = "".T_("FORUM_DELUSER")."";
	  

	  
	  
		// Topic Views
		$viewsq = SQL_Query_exec("SELECT views FROM forum_topics WHERE id=$topicid");
		$viewsa = mysqli_fetch_array($viewsq);
		$views = $viewsa[0];
		// End

        //---- Print row
		if ($CURUSER) {
			$r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
			$a = mysqli_fetch_row($r);
		}
        $new = !$a || $lppostid > $a[0];
        $topicpic = ($locked ? ($new ? "folder_locked_new" : "folder_locked") : ($new ? "folder_new" : "folder"));
        $subject = ($sticky ? "<b>".T_("FORUMS_STICKY").": </b>" : "") . "<a href='forums.php?action=viewtopic&amp;topicid=$topicid'><b>" .
        encodehtml(stripslashes($topicarr["subject"])) . "</b></a>$topicpages";
        print("<tr class='f-row'><td class='f-img' valign='middle'><img src='". $themedir ."$topicpic.png' alt='' />" .
         "</td><td class='alt1' width='60%' align='left'>\n" .
         "$subject</td><td class='alt2' align='center'>$replies</td>\n" .
		 "<td class='alt3' align='center'>$views</td>\n" .
         "<td class='alt2' width='10%' align='left'>$lpauthor</td>\n" .
         "<td class='alt3' width='50%' align='left'><span class='small'>".T_("FORUM_BY")."&nbsp;$lpusername<span style='white-space: nowrap'>&nbsp;$lpadded</span></span></td>\n");
	     if ($CURUSER["edit_forum"] == "yes" || $CURUSER["delete_forum"] == "yes") {
			  print("<td class='alt2' align='center'><span style='white-space: nowrap'>\n");
			if ($locked)
				print("<a href='forums.php?action=unlocktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='".T_("FORUM_UNLOCK_TOPIC")."'><img src='". $themedir ."topic_unlock.png' alt='[".T_("FORUM_UNLOCK_TOPIC")."]' /></a>\n");
			else
				print("<a href='forums.php?action=locktopic&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='".T_("FORUM_LOCK_TOPIC")."'><img src='". $themedir ."topic_lock.png' alt='[".T_("FORUM_LOCK_TOPIC")."]' /></a>\n");
				print("<a href='forums.php?action=deletetopic&amp;topicid=$topicid&amp;sure=0' title='".T_("FORUM_DELETE_TOPIC")."'><img src='". $themedir ."topic_delete.png' alt='[".T_("FORUM_DELETE_TOPIC")."]' /></a>\n");
			if ($sticky)
			   print("<a href='forums.php?action=unsetsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='".T_("FORUM_UNSTICK_TOPIC")."'><img src='". $themedir ."folder_sticky_new.png' alt='[".T_("FORUM_UNSTICK_TOPIC")."]' /></a>\n");
			else
			   print("<a href='forums.php?action=setsticky&amp;forumid=$forumid&amp;topicid=$topicid&amp;page=$page' title='".T_("FORUM_STICK_TOPIC")."'><img src='". $themedir ."folder_sticky.png' alt='[".T_("FORUM_STICK_TOPIC")."]' /></a>\n");
			  print("</span></td>\n");
        }
        print("</tr>\n");
      } // while
   //   end_table();
   print("</table></div><br />");
      print($menu);
    } // if
    else
      print("<p align='center'>".T_("FORUM_NO_TOPICS_FOUND")."</p>\n");
    print("<table cellspacing='0' cellpadding='5'><tr valign='middle'>\n");
    print("<td><img src='". $themedir ."folder_new.png' style='margin-right: 1px' alt='' /></td><td >".T_("FORUM_NEW_POSTS2")."</td>\n");
	 print("<td><img src='". $themedir ."folder.png' style='margin-left: 10px; margin-right: 1px' alt='' />" .
     "</td><td>".T_("FORUM_NO_NEW_POSTS")."</td>\n");
    print("<td><img src='". $themedir ."folder_locked.png' style='margin-left: 10px; margin-right: 1px' alt='' />" .
     "</td><td>".T_("FORUMS_LOCKED")." ".T_("FORUMS_SUBJECT")."</td></tr></table>\n");
    
	$arr = get_forum_access_levels($forumid) or die;
    $maypost = get_user_class() >= $arr["write"];
    
	if (!$maypost) {
		print("<br /><p align='center'><i>".T_("FORUMS_YOU_NOT_PERM_POST_FORUM")."</i></p>\n");
    }
	print("<br /><table border='0' cellspacing='2' cellpadding='4'><tr>\n");

    if ($maypost) {
		print("<td><a href='forums.php?action=newtopic&amp;forumid=$forumid'><button class='forumbutton'>".T_("BTN_FORUM_NEW_POST")."</button><!--<input  value='NEW POST' type='forumbutton' alt='' />--></a></td>\n");
    }
	print("</tr></table>\n");
    insert_quick_jump_menu($forumid);
    end_frame();
    stdfoot();
    die;
}

///////////////////////////////////////////////////////// Action: VIEW NEW POSTS
if ($action == "viewunread") {
	$userid = $CURUSER['id'];
	$maxresults = 25;
	$res = SQL_Query_exec("SELECT id, forumid, subject, lastpost FROM forum_topics ORDER BY lastpost");
    stdhead();
	begin_frame("".T_("FORUM_TOPICS_WITH_UNREAD_POSTS")."");
	forumheader("".T_("FORUM_NEW_TOPICS")."");

    $n = 0;
    $uc = get_user_class();
    while ($arr = mysqli_fetch_assoc($res)) {
      $topicid = $arr['id'];
      $forumid = $arr['forumid'];

      //---- Check if post is read
	  if ($CURUSER) {
		$r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$userid AND topicid=$topicid");
		$a = mysqli_fetch_row($r);
	  }
      if ($a && $a[0] == $arr['lastpost'])
        continue;

      //---- Check access & get forum name
      $r = SQL_Query_exec("SELECT name, minclassread, guest_read FROM forum_forums WHERE id=$forumid");
      $a = mysqli_fetch_assoc($r);
      if ($uc < $a['minclassread'] && $a["guest_read"] == "no")
        continue;
      ++$n;
      if ($n > $maxresults)
        break;
      $forumname = $a['name'];
      if ($n == 1) {
        print("<div class='f-border f-unread'><table width='100%' border='0' cellspacing='0' cellpadding='5'>\n");
        print("<tr class='f-title'><th align='left'>".T_("FORUM_UNREAD_TOPIC")."</th><th align='center'>".T_("FORUM_TOPIC")."</th><th align='center'>".T_("FORUM")."</th></tr>\n");
      }
      print("<tr class='f-row'><td class='f-img' valign='middle'>" .
       "<img src='". $themedir ."folder_unlocked_new.png' style='margin: 5px' alt='".T_("UNREAD")."' title='".T_("UNREAD")."' /></td><td align='center' class='alt2'>" .
       "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=last#last'><b>" . stripslashes(htmlspecialchars($arr["subject"])) ."</b></a></td><td class='alt1' align='center'><a href='forums.php?action=viewforum&amp;forumid=$forumid'><b>$forumname</b></a></td></tr>\n");
    }
    if ($n > 0) {
      print("</table></div><br />\n");
      if ($n > $maxresults)
        print("<p>".T_("FORUM_MAXRESULTS1")."$maxresults".T_("FORUM_MAXRESULTS2")."$maxresults.</p>\n");
      print("<center><a href='forums.php?catchup'><b>".T_("FORUM_ALL_FORUM_READ")."</b></a></center><br />\n");
    }
    else
      print("<b>".T_("FORUM_NOTHING_FOUND")."</b>");
	 end_frame();
    stdfoot();
    die;
}

///////////////////////////////////////////////////////// Action: SEARCH
if ($action == "search") {
	stdhead("".T_("FORUM_SEARCH")."");
	begin_frame("".T_("FORUM_SEARCH2")."");
	forumheader("".T_("FORUM_SEARCH2")."");
			
	$keywords = trim(isset($_GET["keywords"]) ? $_GET["keywords"] : "");
	
	if ($keywords != ""){
		print("<p>".T_("FORUM_SEARCH_PHRASE")."<b>" . htmlspecialchars($keywords) . "</b></p>\n");
		$maxresults = 50;
		$ekeywords = sqlesc($keywords);

        $res = "SELECT forum_posts.topicid, forum_posts.userid, forum_posts.id, forum_posts.added,
                MATCH ( forum_posts.body ) AGAINST ( ". $ekeywords ." ) AS relevancy
                FROM forum_posts
                WHERE MATCH ( forum_posts.body ) AGAINST ( ". $ekeywords ." IN BOOLEAN MODE )
                ORDER BY relevancy DESC";
        
		$res = SQL_Query_exec($res);
		// search and display results...
		$num = mysqli_num_rows($res);

		if ($num > $maxresults) {
			$num = $maxresults;
			print("<p>".T_("FORUM_MAXRESULTS_POSTS1")."$maxresults".T_("FORUM_MAXRESULTS_POSTS2")."$num.</p>\n");
		}
		
		if ($num == 0)
			print("<p><b>".T_("FORUM_NOTHING_FOUND")."</b></p>");
		else {
			print("<p><center><div class='f-border f-srch_results'><table width='100%' border='0' cellspacing='0' cellpadding='5'>\n");
			print("<tr class='f-title'><th align='center'>".T_("FORUM_POST_ID")."</th><th align='center'>".T_("FORUM_TOPIC")."</th><th align='center'>".T_("FORUM")."</th><th align='center'>".T_("FORUM_POSTED_BY")."</th></tr>\n");

			for ($i = 0; $i < $num; ++$i){
				$post = mysqli_fetch_assoc($res);

				$res2 = SQL_Query_exec("SELECT forumid, subject FROM forum_topics WHERE id=$post[topicid]");
				$topic = mysqli_fetch_assoc($res2);

				$res2 = SQL_Query_exec("SELECT name,minclassread, guest_read FROM forum_forums WHERE id=$topic[forumid]");
				$forum = mysqli_fetch_assoc($res2);

				if ($forum["name"] == "" || ($forum["minclassread"] > $CURUSER["class"] && $forum["guest_read"] == "no"))
					continue;
				
				$res2 = SQL_Query_exec("SELECT username, donated, warned, privacy  FROM users WHERE id=$post[userid]");
				$user = mysqli_fetch_assoc($res2);
				
		$privacylevel = $user["privacy"];
		$donated = $user['donated'];
		$warned = $user['warned'];
		
		
		if ($privacylevel == "strong"){
		if (get_user_class() <= 5){
		$searchuser = "".T_("ANONYMOUS").""; 
		}else{
		if ($site_config["CLASS_USER"]) {
		$searchuser = "<a href='account-details.php?id=$post[userid]'>".class_user($user['username'])."</a>";
		}else{
		$searchuser = "<a href='account-details.php?id=$post[userid]'>".$user['username']."&nbsp;</a>" . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
   		} //$site_config["CLASS_USER"]
		}
		}else{
		if ($site_config["CLASS_USER"]) {
		$searchuser = "<a href='account-details.php?id=$post[userid]'>".class_user($user['username'])."</a>";
		}else{
		$searchuser = "<a href='account-details.php?id=$post[userid]'>".$user['username']."&nbsp;</a>" . ($donated > 0 ? "<img src='".$site_config['SITEURL']."/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='".$site_config['SITEURL']."/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . "";
		}
		}
				if ($user["username"] == "")
					$user["username"] = "".T_("FORUM_DELUSER")."";
				print("<tr class='f-row'><td align='center'>$post[id]</td><td align='center'><a href='forums.php?action=viewtopic&amp;topicid=$post[topicid]#post$post[id]'><b>" . htmlspecialchars($topic["subject"]) . "</b></a></td><td align='center'><a href='forums.php?action=viewforum&amp;forumid=$topic[forumid]'><b>" . htmlspecialchars($forum["name"]) . "</b></a></td><td align='center'><b>".$searchuser."</b> at ".utc_to_tz($post["added"])."</td></tr>\n");
			}
			print("</table></div></center></p>\n");
			print("<br /><center><p><b>".T_("FORUM_SEARCH_AGAIN")."</b></p></center>\n");
		}
	}

	print("<center><form method='get' action='?'>\n");
	print("<input type='hidden' name='action' value='search' />\n");
	print("<table cellspacing='0' cellpadding='5'>\n");
	print("<tr><td valign='bottom' align='right'>".T_("FORUM_SEARCH_FOR")." </td><td align='left'><input type='text' size='40' name='keywords' /><br /></td></tr>\n");
	print("<tr><td colspan='2' align='center'><button class='forumbutton'>".T_("SEARCH")."</button><!--<input type='submit' value='".T_("SEARCH")."' />--></td></tr>\n");
	print("</table>\n</form></center>\n");
	end_frame();
	stdfoot();
	die;
}

///////////////////////////////////////////////////////// Action: UNKNOWN
if ($action != "")
    show_error_msg(T_("FORUM_ERROR"), "".T_("FORUM_UNKNOWN_ACTION")." '$action'.");

///////////////////////////////////////////////////////// Action: DEFAULT ACTION (VIEW FORUMS)
if (isset($_GET["catchup"]))
	catch_up();

///////////////////////////////////////////////////////// Action: SHOW MAIN FORUM INDEX
$forums_res = SQL_Query_exec("SELECT forumcats.id AS fcid, forumcats.name AS fcname, forum_forums.* FROM forum_forums LEFT JOIN forumcats ON forumcats.id = forum_forums.category ORDER BY forumcats.sort, forum_forums.sort, forum_forums.name");

stdhead("".T_("FORUMS")."");
begin_frame("".T_("FORUM_HOME")."");
forumheader("".T_("FORUM_INDEX")."");
latestforumposts();

	 print("<div class='f-border f-forums'><table width='100%' cellspacing='0' cellpadding='5'>");// MAIN LAYOUT
	 print("<tr class='f-title'><th width='100%' align='left' colspan='2'>".T_("FORUM[0]")."</th><th align='center'>".T_("FORUM_TOPICS")."</th><th width='100%' align='center'>".T_("POST[1]")."</th><th align='center'>".T_("FORUM_LAST_POST")."</th></tr>\n");// head of forum index
  
if (mysqli_num_rows($forums_res) == 0)
     print("<tr class='f-cat'><td colspan='5' align='center'>".T_("FORUM_NO_CATEGORIES")."</td></tr>\n");  
  
     $fcid = 0;
 
while ($forums_arr = mysqli_fetch_assoc($forums_res)){
	
    if (get_user_class() < $forums_arr["minclassread"] && $forums_arr["guest_read"] == "no")
        continue;
        
    if ($forums_arr['fcid'] != $fcid) {// add forum cat headers
		print("<tr class='f-cat'><td colspan='5' align='center'>".htmlspecialchars($forums_arr['fcname'])."</td></tr>\n");

		$fcid = $forums_arr['fcid'];
	}

    $forumid = 0 + $forums_arr["id"];

    $forumname = htmlspecialchars($forums_arr["name"]);

    $forumdescription = htmlspecialchars($forums_arr["description"]);
    $postcount = number_format(get_row_count("forum_posts", "WHERE topicid IN (SELECT id FROM forum_topics WHERE forumid=$forumid)"));
    $topiccount = number_format(get_row_count("forum_topics", "WHERE forumid = $forumid"));


    // Find last post ID
    $lastpostid = get_forum_last_post($forumid);
//cut last topic
		$latestleng = 10;
 		
		
		// Get last post info
    $post_res = SQL_Query_exec("SELECT added,topicid, userid FROM forum_posts WHERE id=$lastpostid");
    if (mysqli_num_rows($post_res) == 1) {
		$post_arr = mysqli_fetch_assoc($post_res) or show_error_msg(T_("ERROR"), T_("FORUM_LAST_POST_BAD"));
		$lastposterid = $post_arr["userid"];
		$lastpostdate = utc_to_tz($post_arr["added"]);
		$lasttopicid = $post_arr["topicid"];
		$user_res = SQL_Query_exec("SELECT username, donated, warned, privacy FROM users WHERE id=$lastposterid");
		$user_arr = mysqli_fetch_assoc($user_res);
		$donated = $user_arr['donated'];
		$warned = $user_arr['warned'];
		$lastposter = class_user($user_arr['username']);
		
		$topic_res = SQL_Query_exec("SELECT subject FROM forum_topics WHERE id=$lasttopicid");
		$topic_arr = mysqli_fetch_assoc($topic_res);
		$lasttopic = stripslashes(htmlspecialchars($topic_arr['subject']));
		
		//anonymous
		$privacylevel = $user_arr["privacy"];
		if ($privacylevel == "strong"){
		if (get_user_class() <= 5){
		$lastpost = "( <a title=\"".$topic_arr['subject']."\" href='forums.php?action=viewtopic&amp;topicid=$lasttopicid&amp;page=last#last'>" . CutName($lasttopic, $latestleng) . "</a> ) by: ".T_("ANONYMOUS")."</a> $lastpostdate";
		}else{
		if (isset($site_config["CLASS_USER"]) ? $site_config["CLASS_USER"] : "") {
		$lastpost = "( <a title=\"".$topic_arr['subject']."\" href='forums.php?action=viewtopic&amp;topicid=$lasttopicid&amp;page=last#last'>" . CutName($lasttopic, $latestleng) . "</a> ) by: <a href='account-details.php?id=$lastposterid'>$lastposter</a> $lastpostdate";
		}else{
		$lastpost = "( <a title=\"".$topic_arr['subject']."\" href='forums.php?action=viewtopic&amp;topicid=$lasttopicid&amp;page=last#last'>" . CutName($lasttopic, $latestleng) . "</a> ) by: <a href='account-details.php?id=$lastposterid'>$user_arr[username]</a> " . ($donated > 0 ? "<img src='" . (isset($site_config['SITEURL']) ? $site_config['SITEURL'] : "") . "/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='" . (isset($site_config['SITEURL']) ? $site_config['SITEURL'] : "") . "/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . " $lastpostdate";
		}
		}
		}else{
		if (isset($site_config["CLASS_USER"]) ? $site_config["CLASS_USER"] : "") {
		$lastpost = "( <a title=\"".$topic_arr['subject']."\" href='forums.php?action=viewtopic&amp;topicid=$lasttopicid&amp;page=last#last'>" . CutName($lasttopic, $latestleng) . "</a> ) by: <a href='account-details.php?id=$lastposterid'>$lastposter</a> $lastpostdate";
		}else{
		$lastpost = "( <a title=\"".$topic_arr['subject']."\" href='forums.php?action=viewtopic&amp;topicid=$lasttopicid&amp;page=last#last'>" . CutName($lasttopic, $latestleng) . "</a> ) by: <a href='account-details.php?id=$lastposterid'>$user_arr[username]</a> " . ($donated > 0 ? "<img src='" . (isset($site_config['SITEURL']) ? $site_config['SITEURL'] : "") . "/images/star.png' alt='".T_("DONATED")."' title='".T_("DONATED")."' />" : "") . " " . ($warned > 'no' ? "<img src='" . (isset($site_config['SITEURL']) ? $site_config['SITEURL'] : "") . "/images/warned.png' alt='".T_("WARNED")."' title='".T_("WARNED")."' />" : "") . " $lastpostdate";
		}
		}
		


	if ($CURUSER) {
			$r = SQL_Query_exec("SELECT lastpostread FROM forum_readposts WHERE userid=$CURUSER[id] AND topicid=$lasttopicid");
			$a = mysqli_fetch_row($r);
		}

		//define the images for new posts or not on index
		if ($a && $a[0] == $lastpostid)
			$img = "folder";
		else
		$img = "folder_new";
    }else{
		$lastpost = "<span class='small'>".T_("FORUM_NO_POSTS")."</span>";
		$img = "folder";
    }
	//following line is each forums display
    print("<tr class='f-row'><td class='f-img'><img src='". $themedir ."$img.png' alt='' /></td><td width='100%' align='left' class='alt1'><a href='forums.php?action=viewforum&amp;forumid=$forumid'><b>$forumname</b></a>\n" .
    "<small>- $forumdescription</small></td><td class='alt2' align='center'>$topiccount</td><td class='alt3 width='100%' align='center'>$postcount</td>" .
    "<td class='alt2' align='left'><small style='white-space: nowrap'>$lastpost</small></td></tr>\n");
}
print("</table></div>");
//forum Key
print("<table cellspacing='0' cellpadding='5'><tr valign='middle'>\n");
print("<td><img src='". $themedir ."folder_new.png' style='margin: 1px' alt='' /></td><td>".T_("FORUM_NEW_POSTS2")."</td>\n");
print("<td><img src='". $themedir ."folder.png' style='margin-left: 10px; margin-right: 1px' alt='' /></td><td>".T_("FORUM_NO_NEW_POSTS")."</td>\n");
print("<td><img src='". $themedir ."folder_locked.png' style='margin-left: 10px; margin-right: 1px' alt='' /></td><td>".T_("FORUMS_LOCKED")." ".T_("FORUMS_SUBJECT")."</td>\n");
print("<td><img src='". $themedir ."folder_sticky.png' style='margin-left: 10px; margin-right: 1px' alt='' /></td><td>".T_("FORUMS_STICKY")." ".T_("FORUMS_SUBJECT")."</td>\n");
print("</tr></table>\n");

//Top posters
$r = SQL_Query_exec("SELECT users.id, users.username, users.privacy, users.donated, users.warned, COUNT(forum_posts.userid) as num FROM forum_posts LEFT JOIN users ON users.id = forum_posts.userid GROUP BY userid ORDER BY num DESC LIMIT 10");
forumpostertable($r);

//topic count and post counts
$postcount = number_format(get_row_count("forum_posts"));
$topiccount = number_format(get_row_count("forum_topics"));
print("<br /><center>".T_("FORUM_OUR_MEMBER")." " . $postcount . " ".T_("FORUM_POSTS_IN")."  " . $topiccount . " ".T_("FORUM_TOPICS2")."</center><br />");

insert_quick_jump_menu();
end_frame();
stdfoot();

}else{//HEY IF FORUMS ARE OFF, SHOW THIS...
    show_error_msg(T_("FORUM_NOTICE"), T_("FORUM_NOT_AVAILABLE"));
}
?>
