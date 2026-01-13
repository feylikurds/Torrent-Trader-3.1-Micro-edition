<?php
require_once("backend/functions.php");
require_once("backend/bbcode.php");
dbconn();
if ($site_config["MEMBERSONLY"]){
	loggedinonly();
}
stdhead("Requests");
global $site_config, $CURUSER;

$Section=(isset($_GET["Section"])?$_GET["Section"]:"");
		
function pingto($url) {

if (headers_sent()) {
?>
<script language="javascript">
  window.location.href='<?php echo $url; ?>';
</script>
<meta http-equiv="refresh" content="2;<?php echo $url; ?>">
<?php
		echo sprintf("".T_("REDIRECTING")."", $url);
}else
    header('Location: '.$url);
die();
	}
if ($site_config["MEMBERSONLY"])
	

loggedinonly();

if($CURUSER["view_torrents"] == "no")
		show_error_msg(T_("ERROR")."!","".T_("ERROR_MSG1")."", 1);

if (get_user_class() < 1) //min class for making a request
		show_error_msg(T_("ERROR")."!","".T_("ERROR_MSG1")."", 1);

if(!$site_config["REQUESTSON"])
		show_error_msg(T_("ERROR")."!","<b><font color='red'>".T_("ERROR_MSG2")."</font></b>",1);

stdhead($Section);
switch ($Section) {


						 #################
######################### - VOTES START - #########################
						 #################

						 
case 'vote':
begin_frame("" . T_("VOTES") . "");

	$requestid = (int)$_GET["id"];
	$userid = (int)$CURUSER["id"];
$res = SQL_Query_exec("SELECT * FROM addedrequests WHERE requestid=$requestid and userid = $userid");
$arr = mysqli_fetch_assoc($res);
	$voted = $arr;

if(is_null($requestid)){
?>
<br /><p align='center'>Not a real ID!</b></a></p><br /><br />
<?php
}else{
if ($voted) {
?>
<br /><center><p><?php echo T_("ALREADY_VOTED"); ?></p><p><?php echo T_("BACK_TO"); ?><a href=reqall.php?Section=View_Requests><b> <?php echo T_("REQ_TABLE"); ?></b></a></p></center><br /><br />
<?php
}else {
SQL_Query_exec("UPDATE requests SET hits = hits + 1 WHERE id=$requestid");
@SQL_Query_exec("INSERT INTO addedrequests VALUES(0, $requestid, $userid)");

		print("<center><br /><p>".T_("SUCCESSFULLY")."$requestid</p><p>".T_("BACK_TO")." <a href='reqall.php?Section=View_Requests'><b>".T_("REQ_TABLE")."</b></a></p><br /><br /></center>");

	}
	}
end_frame();
break;


						 ###############
######################### - VOTES END - #########################
						 ###############



				   ###########################
################### - REQUEST DETAILS START - ###################
				   ###########################

					
case 'Request_Details':
	$id = (int)$_GET["id"];
$res = SQL_Query_exec("SELECT * FROM requests WHERE id = $id");
if (mysqli_num_rows($res) != 1) show_error_msg("".T_("ID_NOT_FOUND")."", "".T_("ERROR_MSG3")."",1);
$num = mysqli_fetch_array($res);

	
	$filled = $num["filled"];
	$catid = $num["cat"];

	if ($site_config["REQ_SUB_IMAGE"]) {
	$catn = SQL_Query_exec("SELECT parent_cat, categories.image as catpic, categories.image_sub as cat_pic_sub, categories.id AS cat_id, categories.name AS cat_name, name FROM categories WHERE id='$catid' ");
	}else{
	$catn = SQL_Query_exec("SELECT parent_cat, categories.image as catpic, categories.id AS cat_id, categories.name AS cat_name, name FROM categories WHERE id='$catid' ");
	}
	$catname = mysqli_fetch_array($catn);

	if ($site_config['REQ_SUB_IMAGE']) {
	$pcat = "<img border=0 width=100 src=\"" . $site_config['SITEURL'] . "/images/categories/" . $catname["catpic"] . "\" alt=\"" . $catname["parent_cat"] . "\" title=\"" . $catname["parent_cat"] . "\" /></a>";													
	}else{
	$pcat = "<img border=0 src=\"" . $site_config['SITEURL'] . "/images/categories/" . $catname["catpic"] . "\" alt=\"" . $catname["parent_cat"] . ": " . $catname["cat_name"] . "\" title=\"" . $catname["parent_cat"] . ": " . $catname["cat_name"] . "\" />";		
	}
	$ncat = "<img border=0 width=40 src=\"" . $site_config['SITEURL'] . "/images/categories/" . (isset($catname["cat_pic_sub"])) . "\" alt=\"" . $catname["cat_name"] . "\" title=\"" . $catname["cat_name"] . "\" /></a>";
	$char1 = 40; //cut length
	$shortname = CutName(htmlspecialchars($num["request"]), $char1);
	$post = format_comment($num["poster"]);

	if (empty(format_comment($num["poster"]))) {
	$post = "<img src=".$site_config["SITEURL"]."/images/requests/no-poster.jpg width='190' height= border='0' title='".T_("NO_POSTER_FOUND")."'><br />";
	}	
	$cres = SQL_Query_exec("SELECT username, privacy FROM users WHERE id=$num[userid]");

	if (mysqli_num_rows($cres) == 1)
	{
	$carr = mysqli_fetch_assoc($cres);
    
	$privacylevel = $carr["privacy"];

if ($privacylevel == "strong"){
if (get_user_class($CURUSER) <= 5 ){
	$username = "<td class='table_col1' align='center'>".T_("ANONYMOUS")."</td>";
}else{
	if ($site_config["REQ_CLASS_USER"]) {
	$username = "<td class='table_col1' align='center'><a href='account-details.php?id=$num[userid]'>".class_user($carr['username'])."</a></td>";											
	}else{
	$username = "<td class='table_col1' align='center'><a href='account-details.php?id=$num[userid]'>$carr[username]</a></td>";			
	}
	}
}else{
	if ($site_config["REQ_CLASS_USER"]) {
	$username = "<td class='table_col1' align='center'><a href='account-details.php?id=$num[userid]'>".class_user($carr['username'])."</a></td>";											
	}else{
	$username = "<td class='table_col1' align='center'><a href='account-details.php?id=$num[userid]'>$carr[username]</a></td>";			
	}
	}	
	$comment = "".(isset($carr['descr']))."";
}

	$respro = SQL_Query_exec("SELECT u.id, u.username from users u where u.id=" . $num['profilled']);
	$pro = mysqli_fetch_assoc($respro);
if ($pro['username'])
	$profill = $pro['username'];
	else
	$profill = " "; 
     
begin_frame("".T_("REQUEST_DETAILS_FOR")."<i>".$num["request"]."</i>");
				
		print("<br /><a style='text-decoration:none; padding-left:1px;' href='reqall.php'><input type='button' value='".T_("REQ_TABLE")."' title='".T_("REQ_TABLE")."' alt='".T_("REQ_TABLE")."'></a> 
			<a style='text-decoration:none' href='reqall.php?Section=my_requests&requestorid=$CURUSER[id]'><input type='button' value='".T_("MY_REQUESTS")."' title='".T_("MY_REQUESTS")."' alt='".T_("MY_REQUESTS")."'></a> 
			<a style='text-decoration:none' href='reqall.php?Section=View_Votes&requestid=$num[id]'><input type='button' value='".T_("VOTES")."' title='".T_("VOTES")."' alt='".T_("VOTES")."'></a>");
		print("<center><table class='table_table' width='100%' border='0' cellspacing='0' cellpadding='3'><br />\n");
		print("<tr><td class='table_col2' colspan='2' align='center'><b>".T_("REQUEST_DETAILS")."</b></td></tr>");
		
if ($num["descr"]) {
		print("<tr><td class='table_col1' width='50%'><center><i><b><u>".T_("DESCRIPTION")."</u></b></i></center><br /><!-- Codes by HTML.am -->
		<div style='height:200px; line-height:2em; overflow:auto; padding:15px;'>".format_comment($num["descr"])."</div></td><td align='center' class='table_col1'><br />".$post."<br /></td></tr>");
}
		print("</table><br />\n");

if ($site_config["REQ_SUB_IMAGE"]) {
	$subimage = "<th>".T_("GENRE")."</th>";
}
		print("<table width='100%' align='center' cellspacing='0' cellpadding='3' class='ttable_headinner'>\n");
		print("<thead><tr class='ttable_head'>
			<th>".T_("REQ_TYPE")."</th>
			".(isset($subimage) ? $subimage : "")."																						
			<th>".T_("REQUESTED_FILE")."</th>
			<th>".T_("REQ_DATE_ADDED")."</th>
			<th>".T_("REQUEST_BY")."</th>
			<th>".T_("REPORT")."</th>");
			if ((get_user_class($CURUSER) > 5 ) || ($num["userid"] == $CURUSER["id"] && !$pro['username'])) {
		print("<th>".T_("EDIT")."</th>");
}
if ($num["filled"] == NULL) {
if (!$pro['username']) {
		print("<th>".T_("REQ_VOTE")."!</th></tr></thead>\n");
}
}
if ($site_config["REQ_SUB_IMAGE"]) {
	$subimage = "<td class='table_col1' align='center' width='40px'>$ncat</td>";
		}
		print ("<tr><td align='center' class='table_col1'>$pcat</td>".
			"".(isset($subimage) ? $subimage : "")."".															
			"<td class='table_col1' align='left'><text style='padding-left:5px;' title='".$num["request"]."'>".$shortname."</td>".
			"<td class='table_col1' align='center'>".date("jS M \\".T_("A")."\\".T_("T")."\\: g:ia", utc_to_tz_time($num["added"]))."</td>".
			"$username".
			"<td class='table_col1' align='center'><a href='report.php?request=$num[id]'><img style=\"padding-left: 5px;\" src=images/requests/report1.png width='14' title='".T_("REPORT")."' alt='".T_("REPORT")."'></a></td>");							
if ((get_user_class($CURUSER) > 5 ) || ($num["userid"] == $CURUSER["id"] && !$pro['username'])) {
		print("<td class='table_col1' align='center'><a href='reqall.php?Section=Do_Edit&reqid=$id'><img src='images/requests/edit.png' title=".T_("EDIT")." alt=".T_("EDIT")."></a></td>");
}
if ($num["filled"] == NULL) {
if (!$pro['username']) {
		print ("<td class='table_col1' align='center'><a href='reqall.php?Section=vote&id=$id'><img style=\"padding: left3px;\" src=images/requests/vote.png width=16 title=".T_("VOTE")." alt=".T_("VOTE")."></a></td></tr>");
}
		print ("</table>\n");


		print ("<center><table class='table_table' width='100%' border='1' cellspacing='0' cellpadding='3'><br />\n");
		print ("<form method='get' action='reqall.php'><input type='hidden' name='Section' value='Filled'>");
	
if($num["profilled"] && $CURUSER["id"]==$num["profilled"]){
		print ("<tr>
			<td class='table_col1' align='right' width='20%'><b>".T_('TO_FILL_REQUEST1')."</b></td>
			<td class='table_col1'>".T_("TO_FILL_REQUEST2")."</td>
			</tr>");
		print ("<tr><td class='table_col1' colspan='2' align='center'>");
		print ("Torrent ID:&nbsp;<input id='foo' type='text' size='5' name='tid'>\n");
		print ("&nbsp;&nbsp;&nbsp;Torrent URL:&nbsp;<input type='text' size='50' name='filledurl' value=".$site_config['SITEURL']."/torrents-details.php?id=>\n");
		print ("<input type='hidden' value='$id' name='requestid'>");
		print ("<input type='submit' value='Fill Request' >\n</form>");
?>
<script type="text/javascript">	
$(function(){
    var $foo = $('#foo');
    var $bar = $('#bar');
    function onChange() {
        $bar.val($foo.val());
    };
    $('#foo')
        .change(onChange)
        .keyup(onChange);
});
						</script>
<?php
}else if (!$CURUSER["id"]==!$num["profilled"]){
$respro = SQL_Query_exec("SELECT u.id, u.username from users u where u.id=" . $num['profilled']);
$pro = mysqli_fetch_assoc($respro);

	$onthejob = "<tr><td class='table_col1' colspan='2' align='center'><br /><bground>&nbsp;".T_("IS_ON_THE_JOB")."&nbsp;</bground><br /><br /></td></tr>";
	$pro['username'] = $onthejob;

		
		print ("$onthejob");								
}else{
if($CURUSER["can_upload"] == "yes"){
		print ("<tr>
			<td class='table_col1' colspan='2' align='center'><br /><div align=\"center\"><b>".T_("NOT_FILLED_YET")."</b>&nbsp;&nbsp;&nbsp;<a href='reqall.php?Section=takejobin&uid=".(isset($CURUSER['uid']))."&id=$id'><img src='images/requests/take.gif' border='0' alt='Request' title='".T_("TAKE_JOB")."' ></a>
			&nbsp;&nbsp;<b><font color='red'>".T_("BE_CAREFUL")."</font></b></div>");
		print ("<p><hr></p>");
		print ("<form method='get' action='reqall.php#add'><b>".T_("OR")."</b>&nbsp;:&nbsp;<input type='hidden' name='Section' value='Request'><input type='submit' value=\"".T_("MAKE_REQUEST")."\"></form></center></td></tr>");
} 
} 
		print ("</table><br />");
} else {
		print ("<table width='100%'><br /><tr><td colspan='4' class='table_col1' align='center'><br /><bground>&nbsp;".T_("REQ_URL").": <a href='$filled' target'=_self'>$filled</a>&nbsp;</bground><br /><br /></td></tr></table><br />");
}
		
end_frame();


				    #########################
#################### - REQUEST DETAILS END - ####################
				    #########################

					
				  ############################
################## - REQUEST COMMENTS START - ###################
				  ############################


begin_frame("" . T_("COMMENT[1]") . "");
		print("<br />");
	$commentbar = "<p align='center'><a class='index' href='reqcomment.php?action=add&amp;tid=$id'><img style=\"padding: left3px;\" src=images/requests/comment.png width=32 title='".T_("ADD_A_COMMENT")."' alt='".T_("ADD_A_COMMENT")."'></a></p>&nbsp;\n";
	$subres = SQL_Query_exec("SELECT COUNT(*) FROM comments WHERE req = $id");
	$subrow = mysqli_fetch_array($subres);
	$count = $subrow[0];
		
if (!$count) {
		print("<center>".T_("NO_COMMENTS")."</center>");
}else{
	list($pagertop, $pagerbottom, $limit) = pager(10, $count, "reqall.php?Section=Request_Details&id=$id&", array('lastpagedefault => 1'));  // How many comments per page
	$subres = SQL_Query_exec("SELECT comments.id, text, user, comments.added, editedby, editedat, avatar, warned, privacy, ". "username, title, class, donated FROM comments LEFT JOIN users ON comments.user = users.id WHERE req = " . "$id ORDER BY comments.id $limit");
	$allrows = array();
while ($subrow = mysqli_fetch_array($subres))
	$allrows[] = $subrow;
		print($commentbar);
		print($pagertop);
		print("<br />");
	reqcommenttable($allrows);
		print($pagerbottom);
}
		print("<br />");
		print($commentbar);
		
end_frame();
break;


				   ##########################
################### - REQUEST COMMENTS END - ###################
				   ##########################

				   
				   #########################
################### - VIEW REQUESTS START - ####################
				   #########################
				   

case 'View_Requests':
case '':
default:
if (get_user_class() < 1){ //min class for viewing requests
		show_error_msg(T_("ERROR")."!","".T_("ERROR_MSG1")."", 1);
}

begin_frame("" . T_("REQ_TABLE") . "");

if($site_config["REQUESTSON"]){

if (get_user_class() >= 5)
		print("<br /><a style='text-decoration:none; padding-left:1px;' href=reqall.php?Section=Request><input type='button' value='".T_("MAKE_REQUEST")."' title='".T_("MAKE_REQUEST")."' alt='".T_("MAKE_REQUEST")."'></a> 
		<a style='text-decoration:none' href='reqall.php?Section=my_requests&requestorid=$CURUSER[id]'><input type='button' value='".T_("MY_REQUESTS")."' title='".T_("MY_REQUESTS")."' alt='".T_("MY_REQUESTS")."'></a>
		<a style='text-decoration:none' class='altlink' href=". $_SERVER['PHP_SELF'] ."?category=" . (int)(isset($_GET['CATEGORY'])) . "&sort=" . (isset($_GET['SORT'])) . "&filter=false><input type='button' value='".T_("ONLY_FILLED")."' title='".T_("ONLY_FILLED")."' alt='".T_("ONLY_FILLED")."'></a><br /><br />");
		else
		print("<br /><a style='text-decoration:none; padding-left:1px;' href=reqall.php?Section=Request><input type='button' value='".T_("MAKE_REQUEST")."' title='".T_("MAKE_REQUEST")."' alt='".T_("MAKE_REQUEST")."'></a> 
		<a style='text-decoration:none' href='reqall.php?Section=my_requests&requestorid=$CURUSER[id]'><input type='button' value='".T_("MY_REQUESTS")."' title='".T_("MY_REQUESTS")."' alt='".T_("MY_REQUESTS")."'></a><br /><br />");	
	
	$categ = (int)(isset($_GET["category"]) ? $_GET["category"] : '');
	$requestorid = (int)(isset($_GET["requestorid"]) ? $_GET["requestorid"] : '');
	$sort = (isset($_GET["sort"]) ? $_GET["sort"] : '');
	$search = (isset($_GET["search"]) ? $_GET["search"] : '');
	$filter = (isset($_GET["filter"]) ? $_GET["filter"] : '');

	$search = " AND requests.request like '%$search%' ";

if ($sort == "votes")
	$sort = " order by hits ";
else if ($sort == "request")
	$sort = " order by request ";
else if ($sort == "username")
	$sort = " order by username ";
else if ($sort == "filledby")
	$sort = " order by filledby ";
else if ($sort == "profilled")
	$sort = " order by profilled ";
else if ($sort == "cat")
	$sort = " order by cat ";
else if ($sort == "parent_cat")
	$sort = " order by parent_cat ";
else if ($sort == "filled")
	$sort = " order by filled ";
else if ($sort == "comments")
	$sort = " order by comments ";
else if ($sort == "added")
	$sort = " order by added DESC";
else
	$sort = " order by added DESC";


if ($filter == "false")
	$filter = " AND requests.filled != 'yes' ";
else
	$filter = "";

if ($requestorid <> NULL)
{
if (($categ <> NULL) && ($categ <> 0))
	$categ = "WHERE requests.cat = " . $categ . " AND requests.userid = " . $requestorid;
else
	$categ = "WHERE requests.userid = " . $requestorid;
}

else if ($categ == 0)
	$categ = '';
else
	$categ = "WHERE requests.cat = " . $categ;


	$res = SQL_Query_exec("SELECT count(requests.id) FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  $categ $filter $search ");
	$row = mysqli_fetch_array($res);
	$count = $row[0];
	$param = (isset($param));
	
	list($pagertop, $pagerbottom, $limit) = pager(10, $count, $_SERVER['PHP_SELF']."?$param" . "category=" . (isset($_GET["category"]) ? $_GET["category"] : "") . "&sort=" . (isset($_GET["sort"]) ? $_GET["sort"] : "") . "&", array('firstpagedefault => 1') );		### change how many request you want to show per page // default 10
	
if ($site_config["REQ_SUB_IMAGE"]) {
	$res = SQL_Query_exec("SELECT users.downloaded, users.uploaded, users.username, users.privacy, requests.filled, requests.comments,
	requests.filledby, requests.id, requests.userid, requests.request, requests.done, requests.profilled, requests.added, requests.hits, categories.name as cat,
	categories.parent_cat as parent_cat, categories.image as catpic, categories.image_sub as cat_pic_sub, categories.id AS cat_id, categories.name AS cat_name
	FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  $categ
	$filter $search $sort $limit");

}else{
	
	$res = SQL_Query_exec("SELECT users.downloaded, users.uploaded, users.username, users.privacy, requests.filled, requests.comments,
	requests.filledby, requests.id, requests.userid, requests.request, requests.done, requests.profilled, requests.added, requests.hits, categories.name as cat,
	categories.parent_cat as parent_cat, categories.image as catpic, categories.id AS cat_id, categories.name AS cat_name
	FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  $categ
	$filter $search $sort $limit");

}

	$num = mysqli_num_rows($res);
	$param="Section=".$Section."&";
		print("<table class='table_table' border='0' width='100%' align='center' cellspacing='0' cellpadding='3'>
		<tr><td align='center' class='table_head' colspan='2'><b>".T_("SEARCH_REQUESTS")."</b></td></tr>
		<tr><td class='table_col1' width='50%' align='center'>");
		print("<form method='get' action='reqall.php'><input type='hidden' name='Section' value='View_Requests'>");
		print("<input style='padding-left:5px;' type='text' size='30' name='search' placeholder='".T_("SEARCH_REQUESTS")."'>");
		print("&nbsp;&nbsp;<input type='submit' align='center' value='" . T_("SEARCH") . "'></td>\n");
		print("<td class='table_col1' align='center'>");
		print("<input type='hidden' name='Section' value='View_Requests'>");
		print("<select name='category'>");
		print("<option value='0'>" . T_("SHOW_ALL") . "</option>");



	$cats = genrelist();
	$catdropdown = "";
	foreach ($cats as $cat) {
	$catdropdown .= "<option value=\"" . $cat["id"] . "\"";
	$catdropdown .= ">" . htmlspecialchars($cat["parent_cat"]) . ": " . htmlspecialchars($cat["name"]) . "</option>\n";
}


?>
<?php echo $catdropdown; ?>
</select>
<?php

		print("&nbsp;<input type='submit' align='center' value=" . T_("DISPLAY") . ">\n");
		print("</form></td></tr>");
print ("</tr></table>");
		
		if($CURUSER["can_upload"] == "yes"){
		print("<br /><table width='100%'><tr><td class='table_col1' align='center' colspan='2'><br /><bground>&nbsp;<b><font color='red'>".T_("BE_CAREFUL")."</font></b>&nbsp;</bground><br /><br /></table>");
}
		
if($CURUSER["can_upload"] == "yes"){
	$top="<th  width='80'><linkbackground><a href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=profilled><b>".T_("TAKE_JOB")."</b></font></a></center></linkbackground></th>";
	}else{
	$top="";
}
		print("<br />");
		
if ($site_config["REQ_SUB_IMAGE"]) {		
		$subimage = "<th width='60'><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=cat>" . T_("GENRE") . "</b></a></linkbackground></th>";
}
		echo $pagertop;
if (get_user_class($CURUSER) >= 1){  // min class for viewing request with mod rights
		print("<br /><form method='post' action='reqall.php?Section=Delete'>");
		print("<table width='100%' align='center' cellspacing='0' cellpadding='3' class='ttable_headinner'>\n");
		print("<thead><tr class='ttable_head'>".$top."
			<th  width='60'><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=parent_cat>" . T_("REQ_TYPE") . "</a></linkbackground></th>
			".(isset($subimage) ? $subimage : "")."																																																		
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=request><b>" . T_("REQUESTED_FILE") . "</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=added><b>".T_("REQ_DATE_ADDED")."</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=username><b>" . T_("REQUEST_BY") . "<!-- </a></linkbackground><small>(" . T_("Ratio") . ")</small>--></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=filled><b>" . T_("FILLED") . "</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=filledby><b>" . T_("FILLED_BY") . "</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=votes><b>" . T_("VOTES") . "</b></a></linkbackground></th>
			<th><a style='text-decoration:none'; href=". $_SERVER['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=comments><img src='images/requests/comment-icon.png'></a></th>\n");
}			
			if (get_user_class($CURUSER) >= 5){  // min class for viewing request with mod rights
			print("<th><img src=images/requests/delete.png title=".T_("_DEL_")." alt=".T_("_DEL_")."></th></tr></thead>\n");
}
	
	for ($i = 0; $i < $num; ++$i)
{
	$arr = mysqli_fetch_assoc($res);
	$privacylevel = $arr["privacy"];


if ($arr["downloaded"] > 0) {
	$ratio = number_format($arr["uploaded"] / $arr["downloaded"], 2);
	$ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
}
else if ($arr["uploaded"] > 0)
	$ratio = "Inf.";
else
	$ratio = "----";


	$res2 = SQL_Query_exec("SELECT username from users where id=" . $arr['filledby']);
	$arr2 = mysqli_fetch_assoc($res2);

if ($arr2['username'])
	$filledby = $arr2['username'];
else
	$filledby = " ";

	$respro = SQL_Query_exec("SELECT username from users where id=" . $arr['profilled']);
	$pro = mysqli_fetch_assoc($respro);

//if ($pro['username'])
//if ($site_config["REQ_CLASS_USER"]) {
	$profill = '<font color=green>'.T_('ITS_TAKEN').'</font>';																													
	

if ($privacylevel == "strong"){
if (get_user_class() <= 5){
	$addedby = "<td class='table_col2' align='center'>".T_("ANONYMOUS")."</td>";
}else{
	if ($site_config["REQ_CLASS_USER"]) {
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>".class_user($arr['username'])."</a> (----)</td>";		 	
	}else{
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>$arr[username]</a> (----)</td>";							
	}
	}	
}else{
	if ($site_config["REQ_CLASS_USER"]) {
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>".class_user($arr['username'])."</a> ($ratio)</td>";		
	}else{
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>$arr[username]</a> ($ratio)</td>";							
	}
	}

	$filled = $arr['filled'];
if ($filled){
	$filled = "<a href='torrents-details.php?&id=$arr[id]'><font color='limegreen'><b>".T_("YES")."</b></font></a>";
	if ($site_config["REQ_CLASS_USER"]) {
	$filledbydata = "<a href='account-details.php?id=$arr[filledby]'>".class_user($arr2['username'])."</a><br /><small>$arr[done]</small>";						
	}else{
	$filledbydata = "<a href='account-details.php?id=$arr[filledby]'>$arr2[username]</a><br /><small>$arr[done]</small>";										
	}
	}
else{
	$filled = "<a href='reqall.php?Section=Request_Details&id=$arr[id]'><font color='red'><b>".T_("NO")."</b></font></a>";
	$filledbydata  = "<i>nobody</i>";
}
if($CURUSER["can_upload"]=="yes"){
if($arr["profilled"]=="0" && $arr["filledby"]=="0"){
	$table="<td class='table_col1' align='center'><center><a href='reqall.php?Section=takejob&uid=$CURUSER[id]&id=$arr[id]'><img src='images/requests/take.gif' border='0' alt='Request' title='".T_("TAKE_JOB_NOW")."'></a></center></td>";
}else{
	$table="<td class='table_col1' align='center'><center><b>".$profill."<br /></td>";
}
}else{
	$table="";
}
if($CURUSER["can_upload"]=="yes"){
if($arr["profilled"] >0 && $arr["filledby"] >0){
$table="<td class='table_col1' align='center'><b><font color='red'>".T_("FILLED_REQUEST")."</font></b><br /></td>";
}}
	$char1 = 25; //cut length
	$shortname = CutName(htmlspecialchars($arr["request"]), $char1);
if ($site_config["REQ_SUB_IMAGE"]) {
	$subimage = "<td class='table_col2' align='center'><img border='0' src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["catpic"] . "\" alt=\"" . $arr["parent_cat"] . "\" title=\"" . $arr["parent_cat"] . "\" /></td>".																			
	"<td class='table_col1' align='center'><img border='0' width='40' src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["cat_pic_sub"] . "\" alt=\"" . $arr["cat_name"] . "\" title=\"" . $arr["cat_name"] . "\" /></td>";																
}else{
	$subimage = "<td class='table_col1'><img border='0' src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["catpic"] . "\" alt=\"" . $arr['parent_cat'] . ":&nbsp;" . $arr['cat_name'] . "\" title=\"" . $arr["parent_cat"] . ": " . $arr["cat_name"] . "\" /></td>";						
} 
		
		
		print("<tr>".$table."
			$subimage
			<td class='table_col2' align='left'><a style='padding-left:5px;' title='".$arr["request"]."' href=reqall.php?Section=Request_Details&id=$arr[id]><b>".$shortname."</b></a></td>
			<td align='center' class='table_col1'>".date("jS M \\".T_("A")."\\".T_("T")."\\: g:ia", utc_to_tz_time($arr["added"]))."</td>
			$addedby
			<td class='table_col1'><center>$filled</center></td>
			<td class='table_col2'><center>$filledbydata</center></td>");

if (!$arr['profilled']) {
		print("<td class='table_col1'><center><a href='reqall.php?Section=View_Votes&requestid=$arr[id]'><b>$arr[hits]</a></center></td>");
}else{                                     
		print("<td class='table_col1'><center><img src='images/requests/bad.png' title='".T_("CANT_VOTE")."' alt='".T_("CANT_VOTE")."'></center></td>");	
}
		print("<td class='table_col2' align='center'><center><a href='reqall.php?Section=Request_Details&id=$arr[id]'><b>" . $arr["comments"] ."");

if($CURUSER["control_panel"]=="yes"){
	$link="<a href='reqall.php?Section=Reset&requestid=$arr[id]'>".T_("RESETREQUEST")."</a>";

}else{
	$link="";
}

if (get_user_class($CURUSER) >=5){ //min class for showing del checkbox
		print("<td class='table_col1'><center><input type=\"checkbox\" name=\"delreq[]\" value=\"" . $arr['id'] . "\" /><br />$link</center></td>");
} 
		print("</tr>\n");
}
		print("</table>\n");

if (get_user_class($CURUSER) >=5 ){	//min class for deleting
		print("<br /><p style='text-decoration:none; padding-right:1px;' align='right'><input type='submit' value=" . T_("_DEL_") . "></p>");
}
		print("</form><br />");

		echo $pagerbottom;
		print("<br />");
}
end_frame();
break;

				      #######################
###################### - VIEW REQUESTS END - ####################
				      #######################

				   
				          ############
########################## - FILLED - ###########################
				          ############

case 'Filled':
begin_frame("Request Filled");


	$filledurl = $_GET["filledurl"];
	$requestid = (int)$_GET["requestid"];

if(!preg_match('#^'.$site_config["SITEURL"].'\/(.*)id=([0-9]*)$#i',$_GET["filledurl"])){
	show_error_msg(T_("ERROR")."!","".T_("INVALID_URL")."! <br /><a href='javascript:history.back();'>".T_("BACK")."</a>",1);
end_frame();
die();
}else{
	$res = SQL_Query_exec("SELECT users.username, requests.userid, requests.request FROM requests inner join users on requests.userid = users.id where requests.id = $requestid");
	$arr = mysqli_fetch_assoc($res);
	$tid=(int)$_GET['tid'];
if(empty($tid)){
	show_error_msg(T_("ERROR")."!","".T_("NO_TORRENT_ID")." <br /><a href='javascript:history.back();'>".T_("BACK")."</a>",1);
end_frame();
die();
}else{
	$ping=SQL_Query_exec("SELECT owner, id FROM torrents WHERE id=".$tid);
	$find = mysqli_fetch_assoc($ping);

	$upper=unesc($find["owner"]);
	$subject = "".T_("REQUEST_BEEN_FILLED")."";

	$res2 = SQL_Query_exec("SELECT username, privacy FROM users where id =" . $find["owner"]);
	$arr2 = mysqli_fetch_assoc($res2);
	$privacylevel = $arr2["privacy"];
	$id = (isset($id));

if ($privacylevel == "strong") {
		$msg = "".T_("REQ_MSG1").", [url=$site_config[SITEURL]/reqall.php?Section=Request_Details&id=" . $requestid . "][b]" . $arr['request'] . "[/b][/url], ".T_("REQ_MSG2")." [b]" . T_("ANONYMOUS") . "[/b]. ".T_("REQ_MSG3")."  [url=" . $filledurl. "][b]" . $filledurl. "[/b][/url].  ".T_("REQ_MSG4")." [url=$site_config[SITEURL]/reqall.php?Section=Reset&requestid=" . $requestid . "][b]".T_("REQ_MSG5")."[/b][/url].  ".T_("REQ_MSG6")."";
}else
		$msg = "".T_("REQ_MSG1").", [url=$site_config[SITEURL]/reqall.php?Section=Request_Details&id=" . $requestid . "][b]" . $arr['request'] . "[/b][/url], ".T_("REQ_MSG2")." [url=$site_config[SITEURL]/account-details.php?id=$id" . $upper . "][b]" . $arr2['username'] . "[/b][/url].  ".T_("REQ_MSG3")."  [url=" . $filledurl. "][b]" . $filledurl. "[/b][/url].  ".T_("REQ_MSG4")." [url=$site_config[SITEURL]/reqall.php?Section=Reset&requestid=" . $requestid . "][b]".T_("REQ_MSG5")."[/b][/url].  ".T_("REQ_MSG6")."";
	
	SQL_Query_exec ("UPDATE requests SET filled = '$filledurl', filledby = $upper, done=NOW() WHERE id = $requestid");

	SQL_Query_exec("INSERT INTO messages (sender, receiver, added, subject, msg) VALUES(0, $arr[userid], '" . utc_to_tz(isset($arr["editedat"])) . "', " . sqlesc($subject) . ", " . sqlesc($msg) . ")");

		write_log("".T_("THE_REQUEST")." ([b]" . $arr['request'] . "[/b]) ".T_("FILLED_WITH")." " . $CURUSER["username"] . "");

		print("<br /><br /><div align='left'>".T_("THE_REQUEST")." $requestid ".T_("FILLED_WITH")." <a href='$filledurl'>$filledurl</a>.  ".T_("PMD").", <a href='reqall.php?Section=Reset&requestid=$requestid'>".T_("CLICK_HERE")."</a> ".T_("AS_UNFILLED")."<br /><br /></div>");

		print("<br /><br />".T_("THANK_YOU_FOR_FILLING")." :)<br /><br /><a href='reqall.php?Section=View_Requests'>".T_("VIEW_MORE")."</a>");
end_frame();
}
}
break;

				         ################
######################### - FILLED END - ########################
				         ################


				        #################
######################## - RESET START - ########################
				        #################

case 'Reset':
begin_frame("Reset");

	$requestid = (int)$_GET["requestid"];

	$res = SQL_Query_exec("SELECT userid, filledby FROM requests WHERE id =$requestid");
	$arr = mysqli_fetch_assoc($res);

if (($CURUSER['id'] == $arr['userid']) || (get_user_class() >= 4) || ($CURUSER['id'] == $arr['filledby']))
{

	@SQL_Query_exec("UPDATE requests SET filled=NULL, done='0', filledby=0, profilled=0 WHERE id =$requestid");
		//print("".T_("REQUEST")." $requestid ".T_("SUCCESSFULLY_RESET")."<br /><a href='reqall.php'>".T_("BACK")."</a>");
	autolink("reqall.php", "".T_("REQUEST")." $requestid ".T_("SUCCESSFULLY_RESET")."");
}
		else{
		//print("".T_("CANNOT_RESET")." <br /><a href='reqall.php'>".T_("BACK")."</a>");
	autolink("reqall.php", "".T_("CANNOT_RESET")."");
		}
	end_frame();
break;

				         ###############
######################### - RESET END - #########################
				         ###############


				       ###################
####################### - REQUEST START - #######################
				       ###################

case 'Request':

	$res = SQL_Query_exec('SELECT id FROM requests WHERE filledby = 0 AND userid = '.$CURUSER['id']);
	$count = mysqli_num_rows($res);

if (get_user_class() < 3 && $count >= 2) // lowest class is 2, largest number of requests for that class 
	show_error_msg(T_("ERROR")."!", " ".T_("ERROR_MSG4")."", 1);

if (get_user_class() < 1){   // min class for viewing request
	show_error_msg(T_("ERROR")."!", " ".T_("ERROR_MSG1")."", 1);}

begin_frame("" . T_("MAKE_REQUEST") . "");
	

if ($site_config["REQUESTSON"]) {

	$where = "WHERE userid = " . $CURUSER["id"] . "";
	$res2 = SQL_Query_exec("SELECT * FROM requests $where");
	$num2 = mysqli_num_rows($res2);

		print("<br /><a style='text-decoration:none; padding-left:1px;' href='reqall.php'><input type='button' value='".T_("REQ_TABLE")."' title='".T_("REQ_TABLE")."' alt='".T_("REQ_TABLE")."'></a> <a style='text-decoration:none' href='reqall.php?Section=my_requests&requestorid=$CURUSER[id]'><input type='button' value='".T_("MY_REQUESTS")."' title='".T_("MY_REQUESTS")."' alt='".T_("MY_REQUESTS")."'></a><br /><br />");
?>
			<!--<center><big><b><font color=red>If this is abused, it will be for VIP only!</font></b></big><br />-->
		<table class=table_table align=center border=1 width=100% cellspacing=0 cellpadding=3>
			<tr><td colspan=2 align=center class=table_head><b><?php echo T_("REQUEST_INFO"); ?></b></td></tr>
			<tr><td colspan=2 class=table_col1><font size='3' color=red><b><object align='middle'>*</object></b></font>&nbsp;<?php echo T_("REQUEST_INFO1"); ?><br />
			<font size='3' color=red><object align='middle'>*</object></font>&nbsp;<?php echo T_("REQUEST_INFO2"); ?><br />
	<!--		<font size='3' color=red><object align='middle'>*</object></font>&nbsp;<?php echo T_("REQUEST_INFO3"); ?><br /> -->
	<!--		<font size='3' color=red><object align='middle'>*</object></font>&nbsp;<?php echo T_("REQUEST_INFO4"); ?> --></td></tr>
			<!--<tr><td class=colhead align=center><?php print("" . T_("SEARCH") . " " . T_("TORRENT") . ""); ?></td></tr>-->
			<tr><td colspan=2 class=table_col1 align=center><form method="get" action=torrents-search.php><input style="padding-left:5px;" type="text" name="search" id="searchinput" size="30" placeholder="<?php echo T_("SEARCH_TORRENTSTS"); ?>" value="<?php echo stripslashes(htmlspecialchars((isset($searchstr)))); ?>" />&nbsp;<?php print(T_("IN")); ?>&nbsp;<select name="cat"><option value="0"><?php echo "(".T_("ALL_TYPES").")";?></option>

<?php
	$cats = genrelist();
	$catdropdown = "";
	foreach ($cats as $cat) {
	$catdropdown .= "<option value=\"" . $cat["id"] . "\"";
if ($cat["id"] == (int)$_GET["cat"])
    $catdropdown .= " selected=\"selected\"";
	$catdropdown .= ">" . htmlspecialchars($cat["parent_cat"]) . ": " . htmlspecialchars($cat["name"]) . "</option>\n";
}
?>
    <?php echo  $catdropdown ?>
    </select>
    <select name="incldead">
    <option value="0"><?php echo T_("ACTIVE_TRANSFERS"); ?></option>
    <option value="1" <?php if ($_GET["incldead"] == 1) echo "selected='selected'"; ?>><?php echo T_("INC_DEAD"); ?></option>
    <option value="2" <?php if ($_GET["incldead"] == 2) echo "selected='selected'"; ?>><?php echo T_("ONLY_DEAD"); ?></option>
    &nbsp;</select>&nbsp;
    <select name="freeleech">
    <option value="0"><?php echo T_("ALL"); ?></option>
    <option value="1" <?php if ($_GET["freeleech"] == 1) echo "selected='selected'"; ?>><?php echo T_("NOT_FREELEECH"); ?></option>
    <option value="2" <?php if ($_GET["freeleech"] == 2) echo "selected='selected'"; ?>><?php echo T_("ONLY_FREELEECH"); ?></option>
     &nbsp;</select>&nbsp;

    <?php if ($site_config["ALLOWEXTERNAL"]){?>
	<select name="inclexternal">
	<option value="0"><?php echo T_("LOCAL_EXTERNAL"); ?></option>
	<option value="1" <?php if ($_GET["inclexternal"] == 1) echo "selected='selected'"; ?>><?php echo T_("LOCAL_ONLY"); ?></option>
	<option value="2" <?php if ($_GET["inclexternal"] == 2) echo "selected='selected'"; ?>><?php echo T_("EXTERNAL_ONLY"); ?></option>
	&nbsp;</select>&nbsp;
    <?php } ?>

    
    <?php echo  (isset($langdropdown)) ?>
    </select>
    &nbsp;<input type="submit" value="<?php print T_("SEARCH"); ?>" />
			</form>
<?php
		print("</table><br />\n");

		print ("<form name='up' method='post' action='reqall.php?Section=Take'><a name='add' id='add'></a>\n");
		print ("<center><table class='table_table' border='0' width='100%' cellspacing='0' cellpadding='3'>\n");
		print ("<tr><td colspan='2' class='table_head' align='center'><b>" . T_("MAKE_A_REQUEST") . "</b></a></td></tr>\n");
		print ("<tr><td class='table_col1' align='right'><font size='3' color=red><b> * </b></font><input style='padding-left:5px;' type='text' size='40' placeholder='".T_("SCENE_RELEASE_NAME")."' name='requesttitle'></td><td class='table_col1'>");

print ("<font size='3' color=red><b> * </b></font>");
?>
<select name="category">
<option value="0"><?php echo "(".T_("CHOOSE_TYPE").")"; ?></option>
<?php

$res2 = SQL_Query_exec("SELECT id, name,parent_cat FROM categories  order by parent_cat");
$num = mysqli_num_rows($res2);
$catdropdown2 = "";
for ($i = 0; $i < $num; ++$i)
{
$cats2 = mysqli_fetch_assoc($res2);
$catdropdown2 .= "<option value=\"" . $cats2["id"] . "\"";
$catdropdown2 .= ">" . htmlspecialchars($cats2["parent_cat"]) . ": " . htmlspecialchars($cats2["name"]) . "</option>\n";
}

?>
<?php echo $catdropdown2 ?>
</select>

<?php
$settings["descr"] = (isset($settings["descr"]));

		print ("</td></tr><br /> \n");
		print ("<tr><td align=right class=table_col1><input style='padding-left:5px;' type=text name=picture size=40 placeholder='".T_("DIRECT_LINK")."'></td><td class=table_col1>".T_("IMAGE_INFO")."</td></tr>");
		print ("<tr><td colspan=2 class=table_col1 align=center><br /><font size='3' color=red><b><object align='top'> * </object></b></font>".T_("BE_GENEROUS")."</b><br />\n"); //översätta---------------------------------------
if ($site_config["BBCODE_WITH_PREVIEW"]) {
		$dossier = $CURUSER['bbcode'];
		print ("".textbbcode("up","descr",$dossier, htmlspecialchars(isset($arr["descr"])))."<br /></td></tr>\n");
		}else{
		print ("".textbbcode("up","descr",htmlspecialchars($content=$settings["descr"]))."</td></tr>\n");
		
		}
		print ("</table><br /> \n");
		print ("<center><input type=submit value='" . T_("SUBMIT") . "' style='height: 22px'></center>\n");
		print ("</form> \n");
} else {
		print ("<b><font color=red>Sorry, requests are currently disabled.<br /><br />"); //översätta---------------------------------------
}
end_frame();
break;

				        #################
######################## - REQUEST END - ########################
				        #################


				       ##################
####################### - DELETE START - ########################
				       ##################

case 'Delete':
begin_frame("Delete");

if (get_user_class($CURUSER) > 5){
if (empty($_POST["delreq"])){
		print("<center>".T_("AT_LEAST_ONE")."</center>");
end_frame();
stdfoot();
die;
}

	foreach($_POST["delreq"] as $selected=>$msg){
	$reqname=@mysqli_fetch_array(@SQL_Query_exec("SELECT request FROM requests WHERE id=\"$msg\""));

if ($site_config["REQ_CLASS_USER"]) {
	write_log(class_user($CURUSER['username'])." ".T_("DELETED_THE_REQUEST")." ".unesc($reqname["request"])." ($msg)");		
}else{
	write_log($CURUSER['username']." ".T_("DELETED_THE_REQUEST")." ".unesc($reqname["request"])." ($msg)");					
}
	$do="DELETE FROM requests WHERE id=\"$msg\"";
	$do2="DELETE FROM addedrequests WHERE requestid=\"$msg\"";
	$res2=SQL_Query_exec($do2);
	$res=SQL_Query_exec($do);
}
//		print ("<center><br />".T_("REQUEST_DELETED_OK")."<br /><a href='reqall.php?Section=View_Requests'>".T_("BACK")."</a></center>");
		autolink ("reqall.php?Section=View_Requests", "<b><font color='#ff0000'>".T_("REQUEST_DELETED_OK")."....</font></b>");
		print ("<br /><br />");
} else {
	foreach ($_POST[delreq] as $del_req){
	$delete_ok = checkRequestOwnership($CURUSER[id],$del_req);

if ($delete_ok){
	$do="DELETE FROM requests WHERE id IN ($del_req)";
	$do2="DELETE FROM addedrequests WHERE requestid IN ($del_req)";
	$res2=SQL_Query_exec($do2);
	$res=SQL_Query_exec($do);
		print("<center>".T_("REQUEST_ID")." $del_req ".T_("DELETED")."</center>");
} else {
		print("<center>".T_("NO_PERMISSION")." $del_req</center>");
}
}
}
end_frame();

function checkRequestOwnership ($user, $delete_req){
	$query = SQL_Query_exec("SELECT * FROM requests WHERE userid=$user AND id = $delete_req");
	$num = mysqli_num_rows($query);

if ($num > 0)
	return(true);
else
	return(false);
}
break;

				        ################
######################## - DELETE END - ########################
				        ################

						
				    ########################
#################### - DELETE MY REQUESTS - #####################
				    ########################

case 'Delete_my_requests':
begin_frame("".T_("DELETE_MY_REQUESTS")."");

if (get_user_class($CURUSER) > 5){
if (empty($_POST["delmyreq"])){
		print ("<center>".T_("AT_LEAST_ONE")."</center>");
end_frame();
stdfoot();
die;
}

	foreach($_POST["delmyreq"] as $selected=>$msg){
	$reqname=@mysqli_fetch_array(@SQL_Query_exec("SELECT request FROM requests WHERE id=\"$msg\""));
if ($site_config["REQ_CLASS_USER"]) {
		write_log(class_user($CURUSER['username'])." ".T_("DELETED_THE_REQUEST")." ".unesc($reqname["request"])." ($msg)");		
}else{
		write_log($CURUSER['username']." ".T_("DELETED_THE_REQUEST")." ".unesc($reqname["request"])." ($msg)");					
}
	$do="DELETE FROM requests WHERE id=\"$msg\"";
	$do2="DELETE FROM addedrequests WHERE requestid=\"$msg\"";
	$res2=SQL_Query_exec($do2);
	$res=SQL_Query_exec($do);
}
		print("<center><br />".T_("REQUEST_DELETED_OK")."<br /><a href='reqall.php?Section=my_requests&requestorid=$CURUSER[id]'>".T_("BACK")."</a></center>");

		echo "<br /><br />";
} else {
	foreach ($_POST[delreq] as $del_req){
	$delete_ok = checkRequestOwnership($CURUSER[id],$del_req);
if ($delete_ok){
	$do="DELETE FROM requests WHERE id IN ($del_req)";
	$do2="DELETE FROM addedrequests WHERE requestid IN ($del_req)";
	$res2=SQL_Query_exec($do2);
	$res=SQL_Query_exec($do);
		print ("<center>".T_("REQUEST_ID")." $del_req ".T_("DELETED")."</center>");
} else {
		print ("<center>".T_("NO_PERMISSION")." $del_req</center>");
}
}
}

end_frame();

function checkRequestOwnership ($user, $delete_req){
$query = SQL_Query_exec("SELECT * FROM requests WHERE userid=$user AND id = $delete_req");
	$num = mysqli_num_rows($query);

if ($num > 0)
return(true);
else
return(false);
}
break;

				   ############################
################### - DELETE MY REQUESTS END - ####################
				   ############################


				      #####################
###################### - TAKE EDIT START - ########################
				      #####################										

case'Take_Edit':

	$request = unesc($_POST["title"]);
if(isset($_SERVER['HTTP_REFERER'])) {
	$previous = $_SERVER['HTTP_REFERER']; 
	}
	
if (!$request)
		//show_error_msg(T_("ERROR"), "<font color='red'>Do we have a bad day? Did you fill in <i><u>all</u></i> necessary fields?&nbsp;&nbsp;I don't think so...&nbsp;&nbsp;You forgot to name your request ROFL....&nbsp;&nbsp;</font><input type='button'  value=".T_("BACK")." onClick=history.back()>", 1); //översätta---------------------------------------
	    autolink("$previous", "".T_("AUTOLINK2")."");
	$descr = unesc($_POST["descr"]);

if (!$descr)
		//show_error_msg(T_("ERROR"), "<font color='red'>Hmm... too <i><u>small</u></i> description ... <i><u>NOT</u></i> good, &nbsp;<i><u>bad</u></i> description ... <i><u>certainly</u></i> <i><u>NOT</u></i> good, &nbsp;but <i><u>NONE!!?</u></i> description at all... <u><i>are you serious</u></i>. Lol....&nbsp;&nbsp;</font><input type='button'  value=".T_("BACK")." onClick='history.back()'>", 1);
		autolink("$previous", "".T_("AUTOLINK1")."");

if (!empty($_POST['picture'])){
	$picture = unesc($_POST["picture"]);
if(!preg_match("/^(http|https):\/\/[^\s'\"<>]+\.(jpg|jpeg|gif|png)$/i", $picture))
		show_error_msg("".T_("ERROR")."", "".T_("IMAGE_INFO")."", 1);
	$pic = "[img]".$picture."[/img]\n";
}

	$poster = (isset($pic) ? $pic : "");
	$poster = sqlesc($poster);
	$cat = (int) $_POST["category"];
	$request = sqlesc($request);
	$descr = sqlesc($descr);
	$cat = sqlesc($cat);
	$id = (int)$_GET["id"];

	SQL_Query_exec("UPDATE requests SET cat=$cat, request=$request, descr=$descr, poster=$poster WHERE id = $id");

pingto("reqall.php?Section=Request_Details&id=$id");
break;

				       ###################
####################### - TAKE EDIT END - #######################
				       ###################


				        ################
######################## - TAKE START - #########################
				        ################

case 'Take':

	$name = str_replace("'", "", (isset($name)));
	$userid = (int)(isset($_POST["userid"]) ? $_POST["userid"] : '');
	$requestartist = (isset($_POST["requestartist"]) ? $_POST["requestartist"] : '');
	$requesttitle = (isset($_POST["requesttitle"]) ? $_POST["requesttitle"] : '');
	$request = $requestartist . "" . $requesttitle;

if (!$request)
		autolink("reqall.php?Section=Request", "".T_("AUTOLINK2")."");

	$descr = $_POST["descr"];

if (!$descr)
		autolink("reqall.php?Section=Request", "".T_("AUTOLINK1")."");

	$cat = (int)$_POST["category"];

if (!is_valid_id($cat))
		autolink("reqall.php?Section=Request", "".T_("AUTOLINK3")."");

if (!empty($_POST['picture'])){
	$picture = unesc($_POST["picture"]);

if(!preg_match("/^(http|https):\/\/[^\s'\"<>]+\.(jpg|jpeg|gif|png)$/i", $picture))
	show_error_msg("".T_("ERROR")."", "".T_("IMAGE_INFO")."", 1);
	$pic = "[img]".$picture."[/img]\n";
}
	$poster = (isset($pic) ? $pic : "");
	$poster = sqlesc($poster);
	$userid = sqlesc($userid);
	$request = sqlesc($request);
	$descr = sqlesc($descr);
	$cat = sqlesc($cat);

	SQL_Query_exec("INSERT INTO requests (hits, userid, cat, request, descr, poster, added) VALUES(1,".$CURUSER["id"].", $cat, $request, $descr, $poster, '" . get_date_time() . "')");
	$id = mysqli_insert_id($GLOBALS["DBconnector"]);
	@SQL_Query_exec("INSERT INTO addedrequests (requestid,userid) VALUES($id, $CURUSER[id])");
	$sbmessage = "[color=red]".T_("NEW_REQUEST")."[/color] for [b][url=".$site_config['SITEURL']."/reqall.php?Section=Request_Details&id=".$id."]".$requesttitle."[/url][/b] ".T_("HAS_BEEN_MADE")."";
	if (!$site_config["AJSHOUTBOX"])
	{
	SQL_Query_exec("INSERT INTO shoutbox (msgid, user, message, date, userid) VALUES (NULL, '".T_("REQUEST_SHOUTBOX_USER_INSERT")."', ".sqlesc($sbmessage).", '".get_date_time()."', '100')");
	}
	else
	{ 
	SQL_Query_exec("INSERT INTO ajshoutbox (date,name,text,uid) VALUES ('".get_date_time()."', '".T_("REQUEST_SHOUTBOX_USER_INSERT")."', ".sqlesc($sbmessage).",  '0')");
	}
pingto("reqall.php?Section=View_Requests");
break;

				         ##############
######################### - TAKE END - ##########################
				         ##############

				     ######################
##################### - VIEW VOTES START - ######################
				     ######################

case 'View_Votes':

	$requestid = (int)$_GET['requestid'];
	$res2 = SQL_Query_exec("select count(addedrequests.id) from addedrequests inner join users on addedrequests.userid = users.id inner join requests on addedrequests.requestid = requests.id WHERE addedrequests.requestid =$requestid");
	$row = mysqli_fetch_array($res2);
	$count = $row[0];
	$perpage = 20;
	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] ."?" );
	
	$res = SQL_Query_exec("select users.id as userid, users.username, users.privacy, users.downloaded, users.uploaded, requests.id as requestid, requests.request from addedrequests inner join users on addedrequests.userid = users.id inner join requests on addedrequests.requestid = requests.id WHERE addedrequests.requestid =$requestid $limit");
	$res2 = SQL_Query_exec("select request, profilled from requests where id=$requestid");
	$arr2 = mysqli_fetch_assoc($res2);

begin_frame("" . T_("REQ_VOTES_FOR") . "<a href='reqall.php?Section=Request_Details&id=$requestid'> $arr2[request]</a>");

if ($arr2['profilled']) {
	$reqvote = "<br /><br /><center><p><bground>&nbsp;".T_("YOU_CAN_NOT_VOTE")."&nbsp;</bground></p></center>";
}else{
	$reqvote = "<a style='text-decoration:none;' href='reqall.php?Section=vote&id=$requestid'><input type='submit' class='button' value='".T_("REQ_VOTE")."' title='".T_("REQ_VOTE")."' alt='".T_("REQ_VOTE")."'></a>";
}

		print ("<br /><a style='text-decoration:none; padding-left:1px;' href='reqall.php?Section=Request'><input type='button' value='".T_("MAKE_REQUEST")."' title='".T_("MAKE_REQUEST")."' alt='".T_("MAKE_REQUEST")."'></a>
			<a style='text-decoration:none; padding-left:1px;' href='reqall.php'><input type='button' value='".T_("REQ_TABLE")."' title='".T_("REQ_TABLE")."' alt='".T_("REQ_TABLE")."'></a> 
			<a style='text-decoration:none' href='reqall.php?Section=my_requests&requestorid=$CURUSER[id]'><input type='button' value='".T_("MY_REQUESTS")."' title='".T_("MY_REQUESTS")."' alt='".T_("MY_REQUESTS")."'></a> $reqvote<br /><br />");



if (mysqli_num_rows($res) == 0)
		print("<p align='center'><b>" . T_("NOTHING_FOUND") . "</b></p>\n");
	else
{
		print ("<table width='100%' align='center' cellspacing='0' cellpadding='3' class='ttable_headinner'>\n");
		print ("<thead><tr class='ttable_head'><th>" . T_("USERNAME") . "</th>
			<th>" . T_("UPLOADED") . "</th>
			<th>" . T_("DOWNLOADED") . "</th>
			<th>" . T_("RATIO") . "</th></thead>\n");

 while ($arr = mysqli_fetch_assoc($res))
 {

if ($arr["downloaded"] > 0)
{
	$ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
	$ratio = "<ratiobackground ><font color=" . get_ratio_color($ratio) . ">$ratio</font></ratiobackground >";
}else

if ($arr["uploaded"] > 0)
	$ratio = "Inf.";
	else
	$ratio = "---";
	$uploaded =mksize($arr["uploaded"]);
	$downloaded = mksize($arr["downloaded"]);
	$privacylevel = $arr["privacy"];

if (get_user_class($CURUSER) <= 5 ){
if ($privacylevel == "strong") {
	$ratio = "---";
	$uploaded = "---";
	$downloaded = "---";
	$reqstrong = "".T_("ANONYMOUS")."";	
}	
	else	
	if ($site_config["REQ_CLASS_USER"]) {
$reqstrong = "<a href='account-details.php?id=$arr[userid]'>".class_user($arr['username'])."</a>";										
	}else{
	$reqstrong = "<a href='account-details.php?id=$arr[userid]'>".$arr['username']."</a>";														
	}
	}
	else	
	if ($site_config["REQ_CLASS_USER"]) {
$reqstrong = "<a href='account-details.php?id=$arr[userid]'>".class_user($arr['username'])."</a>";										
	}else{
	$reqstrong = "<a href='account-details.php?id=$arr[userid]'>".$arr['username']."</a>";															
	}

		print("<tr>
			<td align='left' class='table_col1'>$reqstrong</td>	         				
				<td align='left' class='table_col1'>$uploaded</td>
				<td align='left' class='table_col1'>$downloaded</td>
				<td align='left' class='table_col1'>$ratio</td>
			</tr>\n");
}
		print("</table><br /><br />\n");
}

end_frame();
break;

				      ####################
###################### - VIEW VOTES END - #######################
				      ####################


				       ###################
####################### - DO EDIT START - #######################
				       ###################

case 'Do_Edit':

global $site_config, $CURUSER, $THEME, $LANGUAGE;

	$rid=(int)$_GET["reqid"];

	$settings=@mysqli_fetch_array(@SQL_Query_exec("SELECT r.*, c.name as catname, c.parent_cat as pcat FROM requests r LEFT JOIN categories c ON c.id=r.cat WHERE r.id=".$rid));

if($settings["id"]==$rid){
	$char1 = 50; //cut length
	$shortname = CutName(htmlspecialchars($settings["request"]), $char1);

begin_frame("Edit Request ".$shortname."");
	
		print ("<form name='req' method='post' action='reqall.php?Section=Take_Edit&id=$rid'>");
		print ("<table width='690px' align='center' cellpadding='0' cellspacing='0' border='0'><br /></br />
			<tr>
				<td class='table_col1' width='10%' align='right'><b>".T_("TITLE")."&nbsp;:&nbsp;</b></td><td class='table_col1' align='left'><input style='padding-left:5px;' type='text' name='title' value='".htmlspecialchars($settings["request"])."' size='35'></td><td class='table_col1' align='right'><b>".T_("GENRE")."&nbsp;:&nbsp;</b></td><td class='table_col1'>
				<select name=\"category\">
				<option value=\"".$settings["cat"]."\">" . htmlspecialchars($settings["pcat"]) . ": " . htmlspecialchars($settings["catname"]) . "</option>");
$res2 = SQL_Query_exec("SELECT id, name, parent_cat FROM categories  order by parent_cat");
$num = mysqli_num_rows($res2);
	$catdropdown2 = "";
	for ($i = 0; $i < $num; ++$i)
{
$cats2 = mysqli_fetch_assoc($res2);  
	$catdropdown2 .= "<option value=\"" . $cats2["id"] . "\"";
	$catdropdown2 .= ">" . htmlspecialchars($cats2["parent_cat"]) . ": " . htmlspecialchars($cats2["name"]) . "</option>\n";
}
		echo $catdropdown2;
		print ("</select></td></tr>");
	  //print ("<tr><td align='right' class='table_col1'><b>".T_("POSTER")."&nbsp;:&nbsp;</b></td><td colspan='3' align='left' class='table_col1'><input style='padding-left:5px;' type='text' name='picture' value='".htmlspecialchars($picture)."' size='35' placeholder='".T_("DIRECT_LINK")."'>&nbsp;".T_("IMAGE_INFO")."</td></tr>");	
		print ("<tr><td align='right' class='table_col1'><b>".T_("POSTER")."&nbsp;:&nbsp;</b></td><td colspan='3' align='left' class='table_col1'><input style='padding-left:5px;' type='text' name='picture' size='35' placeholder='".T_("DIRECT_LINK")."'>&nbsp;".T_("IMAGE_INFO")."</td></tr>");
		if ($site_config["BBCODE_WITH_PREVIEW"]) {
		$dossier = $CURUSER['bbcode'];
		print ("<tr><td colspan='4' class='table_col1'>");
		print ("".textbbcode("req","descr",$dossier,htmlspecialchars($content=$settings["descr"]))."</td></tr> \n");
		}else{
		print ("<tr><td colspan='4' class='table_col1'>");
//		print ("<textarea name=message cols=50 rows=1 > ".$filled." </textarea></td></tr> \n");
		print ("".textbbcode("req","descr",htmlspecialchars($content=$settings["descr"]))."</td></tr> \n");
		}
		print ("</table><br /> \n");
		print("<center><input type='submit' value=".T_("SAVE")."></center></form><br />");
	
	
end_frame();
}else{
show_error_msg("".T_("ERROR")."","".T_("BAD_ID")."", 1);
exit();
}
break;

				        #################
######################## - DO EDIT END - ########################
				        #################


				       ###################
####################### - TAKEJOB START - #######################
				       ###################

case 'takejob':

$id=(int)$_GET["id"];
if($CURUSER["can_upload"]=="yes"){
SQL_Query_exec("UPDATE requests SET profilled=$CURUSER[id] WHERE id=$id");
}
pingto("reqall.php?Section=View_Requests");
break;

					    #################
######################## - TAKEJOB END - ########################
				        #################


                      #####################
###################### - TAKEJOBIN START - ######################
				      #####################

case 'takejobin':
	$id=(int)$_GET["id"];
	SQL_Query_exec("UPDATE requests SET profilled=$CURUSER[id] WHERE id=$id");
pingto("reqall.php?Section=Request_Details&id=$id");
break;

                       ###################
####################### - TAKEJOBIN END - #######################
				       ###################

                      ######################
###################### - MY REQUEST START - #####################
				      ######################
					   				
case 'my_requests':

if (get_user_class() < 1){ //min class for making a request
		show_error_msg(T_("ERROR")."!","".T_("ERROR_MSG1")."", 1);}

begin_frame("" . T_("MY_REQUEST") . "");

if($site_config["REQUESTSON"]){
		print("<br /><p><a style='text-decoration:none' href='reqall.php'><input type='button' value='".T_("REQ_TABLE")."' title='".T_("REQ_TABLE")."' alt='".T_("REQ_TABLE")."'></a>
        <a style='text-decoration:none; padding-left:1px;' href=reqall.php?Section=Request><input type='button' value='".T_("MAKE_REQUEST")."' title='".T_("MAKE_REQUEST")."' alt='".T_("MAKE_REQUEST")."'></a></p><br />");                

	$_SERVERmy['PHP_SELF'] = "reqall.php?Section=my_requests&requestorid=$CURUSER[id]=$'id'&";
	$categ = (int)(isset($_GET["category"]) ? $_GET["category"] : '');
	$requestorid = (int)(isset($_GET["requestorid"]) ? $_GET["requestorid"] : '');
	$sort = (isset($_GET["sort"]) ? $_GET["sort"] : '');
	$search = (isset($_GET["search"]) ? $_GET["search"] : '');
	$filter = (isset($_GET["filter"]) ? $_GET["filter"] : '');

$search = " AND requests.request like '%$search%' ";

if ($sort == "votes")
	$sort = " order by hits ";
else if ($sort == "request")
	$sort = " order by request ";
else if ($sort == "username")
	$sort = " order by username ";
else if ($sort == "filledby")
	$sort = " order by filledby ";
else if ($sort == "profilled")
	$sort = " order by profilled ";
else if ($sort == "cat")
	$sort = " order by cat ";
else if ($sort == "parent_cat")
	$sort = " order by parent_cat ";
else if ($sort == "filled")
	$sort = " order by filled ";
else if ($sort == "comments")
	$sort = " order by comments ";
else if ($sort == "added")
	$sort = " ORDER BY added DESC";
else
	$sort = " ORDER BY added DESC";

if ($filter == "true")
	$filter = " AND requests.filledby = 0 ";
else
	$filter = "";

if ($requestorid <> NULL)
{
if (($categ <> NULL) && ($categ <> 0))
	$categ = "WHERE requests.cat = " . $categ . " AND requests.userid = " . $requestorid;
else
	$categ = "WHERE requests.userid = " . $requestorid;
}

else if ($categ == 0)
	$categ = '';
else
	$categ = "WHERE requests.cat = " . $categ;


	$res = SQL_Query_exec("SELECT count(requests.id) FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  $categ $filter $search");
	$row = mysqli_fetch_array($res);
	$count = $row[0];
	$param = (isset($param));
	
	list($pagertop, $pagerbottom, $limit) = pager(10, $count, $_SERVERmy['PHP_SELF']."?$param" . "category=" . (isset($_GET["category"])) . "&sort=" . (isset($_GET["sort"])) . "&", array('firstpagedefault => 1') );		### change how many request you want to show per page // default 10

	
if ($site_config["REQ_SUB_IMAGE"]) {
	$res = SQL_Query_exec("SELECT users.downloaded, users.uploaded, users.username, users.privacy, requests.filled, requests.comments,
	requests.filledby, requests.id, requests.userid, requests.request, requests.done, requests.profilled, requests.added, requests.hits, categories.name as cat,
	categories.parent_cat as parent_cat, categories.image as catpic, categories.image_sub as cat_pic_sub, categories.id AS cat_id, categories.name AS cat_name
	FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  $categ
	$filter $search $sort $limit");

}else{
	
	$res = SQL_Query_exec("SELECT users.downloaded, users.uploaded, users.username, users.privacy, requests.filled, requests.comments,
	requests.filledby, requests.id, requests.userid, requests.request, requests.done, requests.profilled, requests.added, requests.hits, categories.name as cat,
	categories.parent_cat as parent_cat, categories.image as catpic, categories.id AS cat_id, categories.name AS cat_name
	FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  $categ
	$filter $search $sort $limit");

}

	$num = mysqli_num_rows($res);
	$param="Section=".$Section."&";

if ($site_config["REQ_SUB_IMAGE"]) {
	$subimage = "<th width=60><linkbackground><a style='text-decoration:none'; href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=cat>" . T_("GENRE") . "</b></a></linkbackground></th>";
}else{
	$subimage = "";	
}	
	
	
		echo $pagertop;

if (get_user_class($CURUSER) >= 1){
		print("<br /><form method='post' action='reqall.php?Section=Delete_my_requests'>");
		print("<table width='100%' align='center' cellspacing='0' cellpadding='3' class='ttable_headinner'>\n");
		print("<thead><tr class='ttable_head'>
			<th width='60'><linkbackground><a style='text-decoration:none'; href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=parent_cat><b>" . T_("REQ_TYPE") . "</b></a></linkbackground></th>
			$subimage
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=request><b>" . T_("REQUESTED_FILE") . "</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none; padding-left:5px; right:5px;' href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=added><b>".T_("REQ_DATE_ADDED")."</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=filled><b>" . T_("FILLED") . "</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=filledby><b>" . T_("FILLED_BY") . "</b></a></linkbackground></th>
			<th><linkbackground><a style='text-decoration:none'; href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=votes><b>" . T_("VOTES") . "</b></a></linkbackground></th>
			<th><a style='text-decoration:none'; href=". $_SERVERmy['PHP_SELF'] ."?".$param."category=" . (isset($_GET['CATEGORY'])) . "&filter=" . (isset($_GET['FILTER'])) . "&sort=comments><img src=images/requests/comment-icon.png></a></th></tr></thead>\n");	

}
	for ($i = 0; $i < $num; ++$i)
{

	$arr = mysqli_fetch_assoc($res);

	$privacylevel = $arr["privacy"];

if ($arr["downloaded"] > 0)
{
     $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 2);
     $ratio = "<font color=" . get_ratio_color($ratio) . "><b>$ratio</b></font>";
}
else if ($arr["uploaded"] > 0)
       $ratio = "Inf.";
else
       $ratio = "---";


	$res2 = SQL_Query_exec("SELECT username from users where id=" . $arr['filledby']);
	$arr2 = mysqli_fetch_assoc($res2);
if ($arr2['username'])
	$filledby = $arr2['username'];
else
	$filledby = " ";

	$respro = SQL_Query_exec("SELECT username from users where id=" . $arr['profilled']);
	$pro = mysqli_fetch_assoc($respro);
if ($pro['username'])
if ($site_config["REQ_CLASS_USER"]) {
	$profill = class_user($pro['username']);
}else{
	$profill = $pro['username'];
}
else
	$profill = " ";

if ($privacylevel == "strong"){
if (get_user_class() >= 5){
if ($site_config["REQ_CLASS_USER"]) {
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>".class_user($arr['username'])."</a><!-- <br />[$ratio]--></td>";			
}else{
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'><b>$arr[username]</b></a><!-- <br />[$ratio]--></td>";						
}
}else{
if ($site_config["REQ_CLASS_USER"]) {
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>".class_user($arr['username'])."</a><!-- <br />[----]--></td>";				
}else{
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>$arr[username]</a><!-- <br />[----]</td>";								
}
}	
}else{
if ($site_config["REQ_CLASS_USER"]) {
	$addedby = "<td class='table_col2' align=center><a href='account-details.php?id=$arr[userid]'>".class_user($arr['username'])."</a><!-- <br />[$ratio]--></td>";			
}else{
	$addedby = "<td class='table_col2' align='center'><a href='account-details.php?id=$arr[userid]'>$arr[username]</a><!-- <br />[$ratio]--></td>";								
}
}

	$filled = $arr['filled'];
if ($filled){
	$filled = "<a href='torrents-details.php?&id=$arr[id]'><font color='limegreen'><b>".T_("YES")."</b></font></a>";
if ($site_config["REQ_CLASS_USER"]) {
	$filledbydata = "<a href='account-details.php?id=$arr[filledby]'><b>".class_user($arr2['username'])."</b></a><br /><small>$arr[done]</small>";								
}else{
	$filledbydata = "<a href='account-details.php?id=$arr[filledby]'><b>$arr2[username]</b></a><br /><small>$arr[done]</small>";												
}
}
else{
	$filled = "<a href='reqall.php?Section=Request_Details&id=$arr[id]'><font color='red'><b>".T_("NO")."</b></font></a>";
	$filledbydata  = "<i>nobody</i>";
}

	$char1 = 40; //cut name length
	$smallname = htmlspecialchars(CutName($arr["request"], $char1));
	$dispname = "<b>".$smallname."</b>";

if ($site_config["REQ_SUB_IMAGE"]) {
	$subimage = "<td class='table_col2' align='center'><img border='0' src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["catpic"] . "\" alt=\"" . $arr["parent_cat"] . "\" title=\"" . $arr["parent_cat"] . "\" /></td>".																			
	"<td class='table_col1' align='center'><img border='0' width='40' src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["cat_pic_sub"] . "\" alt=\"" . $arr["cat_name"] . "\" title=\"" . $arr["cat_name"] . "\" /></td>";																
}else{
	$subimage = "<td class='table_col1'><img border='0' src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["catpic"] . "\" alt=\"" . $arr["parent_cat"] . ":&nbsp;" . $arr["cat_name"] . "\" title=\"" . $arr["parent_cat"] . ": " . $arr["cat_name"] . "\" /></td>";						
} 
		print("<tr>
		$subimage
		<td class='table_col2' align='left'><a style='padding-left:5px;' title='".$arr["request"]."' href=reqall.php?Section=Request_Details&id=$arr[id]><b>".$smallname."</b></a></td>" . "
		<td width='80' align='center' class='table_col1'>".date("jS M \\".T_("A")."\\".T_("T")."\\: g:ia", utc_to_tz_time($arr["added"]))."</td>
		<td class='table_col2'><center>$filled</center></td>
		<td class='table_col1'><center>$filledbydata</center></td>");
if (!$arr['profilled']) {
		print("<td class='table_col2'><center><a href='reqall.php?Section=View_Votes&requestid=$arr[id]'><b>$arr[hits]</a></center></td>");
	}else{
		print("<td class='table_col2'><center><img src='images/requests/bad.png' title='".T_("CANT_VOTE")."' alt='".T_("CANT_VOTE")."'></center></td>");	
}
		print("<td class='table_col1' align='center'><center><a href='reqall.php?Section=Request_Details&id=$arr[id]'>" . $arr["comments"] ."");
if (get_user_class($CURUSER) >= 5) {
	$link="<a href='reqall.php?Section=Reset&requestid=$arr[id]'>Reset</a>";
}else{
	$link="";
}

/*
if($CURUSER["control_panel"]=="yes"){
		print("<td class='table_col1'><center><input type=\"checkbox\" name=\"delmyreq[]\" value=\"" . $arr[id] . "\" /><br />$link</center></td>");

} 
*/	
		print("</tr>\n");

}

		print("</table>\n");
/*
if (get_user_class($CURUSER) > 5) {	
		print("<br /><p style='text-decoration:none; padding-right:1px;' align='right'><input type='submit' value=" . T_("_DEL_") . "></p>");
}
*/
		print("</form><br />");
		echo $pagerbottom;
		print("<br />");
}
end_frame();
break;

                       ####################
####################### - MY REQUEST END - ######################
				       ####################

}
stdfoot();
?>