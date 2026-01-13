<?php
//
//  TorrentTrader v2.x
//      $LastChangedDate: 2012-06-14 17:31:26 +0100 (Thu, 14 Jun 2012) $
//      $LastChangedBy: torrenttrader $
//
//      http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();
loggedinonly();

stdhead("Report");
//error_reporting(E_ALL);
begin_frame("Report");

$takeuser = (int) (isset($_POST["user"]) ? $_POST["user"] : null);
$takerequest = (int) (isset($_POST["request"]) ? $_POST["request"] : null);
$taketorrent = (int) (isset($_POST["torrent"]) ? $_POST["torrent"] : null);
$takeforumid = (int) (isset($_POST["forumid"]) ? $_POST["forumid"] : null);
$takecomment = (int) (isset($_POST["comment"]) ? $_POST["comment"] : null);
$takeforumpost = (int) (isset($_POST["forumpost"]) ? $_POST["forumpost"] : null);
$takereason = (isset($_POST["reason"]) ? $_POST["reason"] : null);

$user = (int)(isset($_GET["user"]) ? $_GET["user"] : null);
$request = (int)(isset($_GET["request"]) ? $_GET["request"] : null);
$torrent = (int)(isset($_GET["torrent"]) ? $_GET["torrent"] : null);
$comment = (int)(isset($_GET["comment"]) ? $_GET["comment"] : null);
$forumid = (int)(isset($_GET["forumid"]) ? $_GET["forumid"] : null);
$forumpost = (int)(isset($_GET["forumpost"]) ? $_GET["forumpost"] : null);

//take report user
if (!empty($takeuser)){
    if (empty($takereason)){
        show_error_msg(T_("ERROR"), T_("YOU_MUST_ENTER_A_REASON"), 0);
        end_frame();
        stdfoot();
        die();
    }

    $res = SQL_Query_exec("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor = $takeuser AND type = 'user'");

    if (mysqli_num_rows($res) == 0){
        SQL_Query_exec("INSERT into reports (addedby,votedfor,type,reason) VALUES ($CURUSER[id],$takeuser,'user', ".sqlesc($takereason).")");
        
		print ("<br /><table class=table_table width='50%' align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'>User: $takeuser</td></tr>");
		print ("<tr><td width='15%' align='right' class='table_col1'>Reason: </td><td class='table_col1'>".htmlspecialchars($takereason)."</td></tr>");
		print ("<tr><td align='center' colspan='2' class='table_col1'>Successfully Reported!</td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'>");
        end_frame();
        stdfoot();
        die();
    }else{
        print(T_("YOU_HAVE_ALREADY_REPORTED")." user $takeuser <input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'></p><br />");
        end_frame();
        stdfoot();
        die();
    }
}

//take report torrent
if (($taketorrent !="") && ($takereason !="")){
    if (!$takereason){
        show_error_msg(T_("ERROR"), T_("YOU_MUST_ENTER_A_REASON"), 0);
        end_frame();
		stdfoot();
        die();
    }

    $res = SQL_Query_exec("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor = $taketorrent AND type = 'torrent'");
    if (mysqli_num_rows($res) == 0){
        SQL_Query_exec("INSERT into reports (addedby,votedfor,type,reason) VALUES ($CURUSER[id],$taketorrent,'torrent', ".sqlesc($takereason).")");
        
		print ("<br /><table class=table_table width='50%' align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'>Torrent: $taketorrent</td></tr>");
		print ("<tr><td width='15%' align='right' class='table_col1'>Reason : </td><td class='table_col1'>".htmlspecialchars($takereason)."</td></tr>");
		print ("<tr><td align='center' colspan='2' class='table_col1'>Successfully Reported!</td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'></p><br />");
        end_frame();
        stdfoot();
        die();
    }else{
        print(T_("YOU_HAVE_ALREADY_REPORTED")." torrent $taketorrent <input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'>");
        end_frame();
        stdfoot();
        die();
    }
}

//take report request
if (($takerequest !="") && ($takereason !="")){
    if (!$takereason){
        show_error_msg(T_("ERROR"), T_("YOU_MUST_ENTER_A_REASON"), 0);
        end_frame();
		stdfoot();
        die();
    }

    $res = SQL_Query_exec("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor = $takerequest AND type = 'request'");
    if (mysqli_num_rows($res) == 0){
        SQL_Query_exec("INSERT into reports (addedby,votedfor,type,reason) VALUES ($CURUSER[id],$takerequest,'request', ".sqlesc($takereason).")");
        
		print ("<br /><table class=table_table width='50%' align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'>Request: $takerequest</td></tr>");
		print ("<tr><td width='15%' align='right' class='table_col1'>Reason : </td><td class='table_col1'>".htmlspecialchars($takereason)."</td></tr>");
		print ("<tr><td align='center' colspan='2' class='table_col1'>Successfully Reported!</td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'></p><br />");
        end_frame();
        stdfoot();
        die();
    }else{
        print (T_("YOU_HAVE_ALREADY_REPORTED")." request $takerequest <input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'>");
        end_frame();
        stdfoot();
        die();
    }
}

//take report comment  //klar
if (($takecomment !="") && ($takereason !="")){
    if (!$takereason){
        show_error_msg(T_("ERROR"), T_("YOU_MUST_ENTER_A_REASON"), 0);
        end_frame();
		stdfoot();
        die();
    }

    $res = SQL_Query_exec("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor = $takecomment AND type = 'comment'");
    if (mysqli_num_rows($res) == 0){
        SQL_Query_exec("INSERT into reports (addedby,votedfor,type,reason) VALUES ($CURUSER[id],$takecomment,'comment', ".sqlesc($takereason).")");
        
		print ("<br /><table width='50%' align='center' border='0'cellspacing=0 cellpadding=3 >");
		print("<tr><td align='center' colspan='2' class='table_head'>Comment: $takecomment</td></tr>"); 
		print ("<tr><td width='5%' align='right' class='table_col1'>Reason : </td><td class='table_col1'>".htmlspecialchars($takereason)."</td></tr>");
		print ("<tr><td align='center' colspan='2' class='table_col1'>Successfully Reported!</td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'></p><br />");
   		end_frame();
        stdfoot();
        die();
    }else{
        print (T_("YOU_HAVE_ALREADY_REPORTED")." comment $takecomment <input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'>");
        end_frame();
        stdfoot();
        die();
    }
}

//take forum post report  //klar
if (($takeforumid !="") && ($takereason !="")){
    if (!$takereason){
        show_error_msg(T_("ERROR"), T_("YOU_MUST_ENTER_A_REASON"), 0);
        end_frame();
		stdfoot();
        die();
    }

    $res = SQL_Query_exec("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor= $takeforumid AND votedfor_xtra= $takeforumpost AND type = 'forum'");

    if (mysqli_num_rows($res) == 0){
        SQL_Query_exec("INSERT into reports (addedby,votedfor,votedfor_xtra,type,reason) VALUES ($CURUSER[id],$takeforumid,$takeforumpost ,'forum', ".sqlesc($takereason).")");

		print ("<br /><table width='50%' align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'>Forumpost : $takeforumpost</td></tr>"); 
		print ("<tr><td width='5%' align='right' class='table_col1'>Reason : </td><td class='table_col1'>".htmlspecialchars($takereason)."</td></tr>");
		print ("<tr><td align='center' colspan='2' class='table_col1'>Successfully Reported!</td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='button' name='knapp5' value='".T_("BACK")."' onClick='history.go(-2)'></p><br />");
        end_frame();
        stdfoot();
        die();
    }else{
        print(T_("YOU_HAVE_ALREADY_REPORTED")." post $takeforumid");
        end_frame();
        stdfoot();
        die();
    }

}

//report user form  //klar
if ($user !=""){
$res = SQL_Query_exec("SELECT username, class FROM users WHERE id=$user");
if (mysqli_num_rows($res) == 0){
        print(T_("INVALID_USERID"));
end_frame();
stdfoot();
die();
    }    

$arr = mysqli_fetch_assoc($res);
		print ("<br /><table align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'><b>Are you sure you would like to report user?</b></td></tr>");
		print ("<tr><td class='table_col1' align='right'><b>Username</b> : </td><td class='table_col1'><a href='account-details.php?id=$user'><b>$arr[username]</b></a></td></tr>");
		print ("<tr><td class='table_col1' align='right'><font color='red'><b>NOTE!</b></font> : </td><td class='table_col1'><p>This is <b>not</b> to be used to report leechers, we have scripts in place to deal with them.</p></td></tr>");
		print ("<tr><td class='table_col1'><b>Reason</b> (required) : </td><td class='table_col1'><form method='post' action='report.php'><input type='hidden' name='user' value='$user' /><input type='text' size='100' name='reason' /></td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='submit' value='Confirm' /></p></form><br />");
end_frame();
stdfoot();
die();
}

//report torrent form  //klar
if ($torrent !=""){
$res = SQL_Query_exec("SELECT name FROM torrents WHERE id=$torrent");

if (mysqli_num_rows($res) == 0){
        print("Invalid TorrentID");
end_frame();
stdfoot();
die();
    }

$arr = mysqli_fetch_array($res);
		print ("<br /><table class=table_table align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'><b>Are you sure you would like to report torrent?</b></td></tr>");
		print ("<tr><td  class='table_col1' align='right'><b>".T_("TORRENT")."</b> : </td><td class='table_col1'><a href='torrents-details.php?id=$torrent'><b>$arr[name]</b></a>");
		print ("<tr><td class='table_col1' align='right'><b>Reason</b> (required): </td><td class='table_col1'><form method='post' action='report.php'><input type='hidden' name='torrent' value='$torrent' /><input type='text' size='100' name='reason' /></td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='submit' value='Confirm' /></p></form><br />");
end_frame();
stdfoot();
die();
}

//report requests form  //klar
if ($request !=""){
$res = SQL_Query_exec("SELECT request FROM requests WHERE requests.id=$request");

if (mysqli_num_rows($res) == 0){
        print("Invalid RequestID");
end_frame();
stdfoot();
die();
}

$arr = mysqli_fetch_array($res);
		print ("<br /><table class=table_table align='center' border='1'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'><b>Are you sure you would like to report request?</b></td></tr>");
		print ("<tr><td class=table_col1 align='right'><b>".T_("REQUEST")."</b> : </td><td class='table_col1'><a href='reqall.php?Section=Request_Details&id=$request'><b>$arr[request]</b></a></td></tr>");
		print ("<tr><td class='table_col1'><b>Reason</b> (required): </td><td class='table_col1'><form method='post' action='report.php'><input type='hidden' name='request' value='$request' /><input type='text' size='100' name='reason' /></td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='submit' value='Confirm' /></p></form><br />");
end_frame();
stdfoot();
die();
}

//report forum post form  //klar
if (($forumid !="") && ($forumpost !="")){
$res = SQL_Query_exec("SELECT subject FROM forum_topics WHERE id=$forumid");

if (mysqli_num_rows($res) == 0){
        print ("Invalid Forum ID");
end_frame();
stdfoot();
die();
}

$arr = mysqli_fetch_array($res);
		print ("<br /><table class=table_table align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'><b>Are you sure you would like to report the following forum post?</b></td></tr>");
		print ("<tr><td class='table_col1' align='right'><b>".T_("POST")."</b> : </td><td class='table_col1'><a href='forums.php?action=viewtopic&amp;topicid=$forumid&amp;page=p#post$forumpost'>$arr[subject]</a></td></tr>");
		print ("<tr><td class='table_col1'><b>Reason</b> (required): </td><td class='table_col1'><form method='post' action='report.php'><input type='hidden' name='forumid' value='$forumid' /><input type='hidden' name='forumpost' value='$forumpost'><input type='text' size='100' name='reason' /></td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='submit'  value='Confirm' /></p></form><br />");
end_frame();
stdfoot();
die();
}

//report comment form  //klar 
if ($comment !=""){
$res = SQL_Query_exec("SELECT id, text FROM comments WHERE id=$comment");
if (mysqli_num_rows($res) == 0){
		print ("Invalid Comment");
end_frame();
stdfoot();
die();
}    

$arr = mysqli_fetch_assoc($res);
		print ("<br /><table class=table_table align='center' border='0'cellspacing=0 cellpadding=3 >");
		print ("<tr><td align='center' colspan='2' class='table_head'><b>Are you sure you would like to report this comment?</b></td></tr>");
		print ("<tr><td colspan='2' class='table_col1'><!-- Codes by HTML.am -->
			<div style='height:100px; line-height:2em; overflow:auto; padding:15px;'>".format_comment($arr["text"])."</div></td></tr>");
		print ("<tr><td class='table_col1' align='right'><font color='red'><b>NOTE!</b></font> : </td><td class='table_col1'><p>This is <b>not</b> to be used to report leechers, we have scripts in place to deal with them.</p></td></tr>");
		print ("<tr><td class='table_col1'><b>Reason</b> (required) : </td><td class='table_col1'><form method='post' action='report.php'><input type='hidden' name='comment' value='$comment' /><input type='text' size='100' name='reason' /></td></tr>");
		print ("</table>");
		print ("<br /><p align='center'><input type='submit'  value='Confirm' /></p></form><br />");
end_frame();
stdfoot();
die();
}

//error
if (($user !="") && ($torrent !="")){
		print ("<h1>".T_("MISSING_INFO")."</h1>");
end_frame();
stdfoot();
die();
}

show_error_msg(T_("ERROR"), T_("MISSING_INFO").".", 0);
end_frame();
stdfoot();
?>