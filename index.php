<?php
//
//  TorrentTrader v2.x
//      $LastChangedDate: 2011-11-17 00:13:07 +0000 (Thu, 17 Nov 2011) $
//      $LastChangedBy: dj-howarth1 $
//
//      http://www.torrenttrader.org
//
//

require_once("backend/functions.php");
dbconn(true);
loggedinonly();
stdhead(T_("HOME"));

//check
if (file_exists("check.php") && $CURUSER["class"] >= 7){
	show_error_msg("WARNING", "Check.php still exists, please delete or rename the file as it could pose a security risk<br /><br /><a href='check.php'>View Check.php</a> - Use to check your config!<br /><br />",0);
}

if ( isset( $_SERVER['HTTP_REFERER'] ) )
{
   $parse = parse_url($_SERVER['HTTP_REFERER']);
                
   if ($parse['host'] != $_SERVER['HTTP_HOST'])
           SQL_Query_exec("INSERT INTO `referer` (`referer`, `added`, `count`) VALUES ('".$parse['scheme'] . '://' . $parse['host']."', '".gmtime()."', '1') ON DUPLICATE KEY UPDATE `count` = `count` + 1");
}

//Site Notice
if ($site_config['SITENOTICEON']){
	begin_frame(T_("NOTICE"));
	echo $site_config['SITENOTICE'];
	end_frame();
}
?>

<?php
//torrent image scroller 5-21-15
/*
begin_frame(T_("LATEST_TORRENTS_SCROLLER"));  
echo '<IFRAME  src="'.$site_config["SITEURL"].'/carroussel.php" frameborder="0" marginheight="0" marginwidth="0" width="99%" height="160"  scrolling="no" align="middle"></IFRAME>';
end_frame();
//end torrent image scroller
*/
//STOCK SHOUTBOX////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($site_config['SHOUTBOX'] && (!$site_config["AJSHOUTBOX"]) && !($CURUSER['hideshoutbox'] == 'yes')){ 

	begin_frame(T_("SHOUTBOX"));
	echo '<iframe name="shout_frame" src="shoutbox.php" frameborder="0" marginheight="0" marginwidth="0" width="99%" height="270" scrolling="no" align="middle"></iframe>';
	printf(T_("SHOUTBOX_REFRESH"), 2)."<br />";
	end_frame();
}
//END STOCK SHOUTBOX////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//AJAX SHOUTBOX/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     
    if ($site_config['SHOUTBOX'] && ($site_config["AJSHOUTBOX"]) && !($CURUSER['hideshoutbox'] == 'yes')){
           
            require_once("backend/smilies.php");   
            require_once("shoutfun_new.php");
    begin_frame("".$site_config['SITENAME']." Shoutbox");
                   
      function smile() {
     
            print "<div align='center'><table cellpadding='3' cellspacing='3'><tr>";
     
            global $smilies, $count;
            reset($smilies);
     
            while ((list($code, $url) = thisEach($smilies)) && $count<16) {
                      print("\n<td><a href=\"javascript: SmileIT('".str_replace("'","\'",$code)."')\">
                                    <img border=\"0\" src=\"images/smilies/$url\" alt=\"$code\" /></a></td>");
                               
                      $count++;
            }
     
            print '<br /><td>&nbsp<a href="javascript:show_hide(\'sextra\');"><img src=./images/ajshoutbox/down.gif title=more border=0></a></td></tr></table></div>';
      }
    function smileextra() {
     
      global $smilies;
      reset($smilies);
     
            # getting smilies
            while (list($code, $url) = thisEach($smilies)) {
                    print("\n<a href=\"javascript: SmileIT('".str_replace("'","\'",$code)."')\">
                               <img border=\"0\" src=\"images/smilies/$url\" alt=\"$code\" /></a>");
     
                   // $count++;
            }
     
     }
     
    ?>
     
    <script src="js/ajshoutbox.js" language="Javascript" type="text/javascript"></script>
     
    <br />
        <table align=center class=ajshoutboxbackground width=98%><tr>
        <td align=center>
    <div align='center'><b><font color=green>Member</font> | <font color=brown>Power User</font> | <font color=dimgray>VIP</font> | <font color=purple>Uploader</font> |<font color=pink>Moderator</font> | <font color=#009AFF>Super Moderator</font> | <font color=red>Administrator</font>| <font color=#00ffbf>Site Owner</font></b></br></br>
                  <p class="microsoft marquee"><span><?php echo "".T_("SHOUTINFO").""; ?></span></p></div></div>

             <div id="shoutheader">
             
            <form id="chatForm" name="chatForm" onsubmit="return false;" action="">
           
              <input type="hidden" name="name" id="name" value="<?php echo $CURUSER["username"] ?>" />
              <input type="hidden" name="uid" id="uid" value="<?php echo $CURUSER["id"] ?>" />

                      <table align=center width=98%><tr>
                      <td align=center>
                      <div align=center><?php echo smile();?></div><br />
                      <input type="text" size="105" maxlength="500"  placeholder=<?php echo"'".T_('SHOUT_HERE')."'" ;?> name="chatbarText" id="chatbarText" onblur="checkStatus('');" onfocus="checkStatus('active');" />
              <input onclick="sendComment();" type="submit" id="submit" name="submit" value="Confirm" />
              &nbsp;
              <a href="javascript: PopMoreSmiles('chatForm','chatbarText');">
              <img src="images/ajshoutbox/smile.gif" border="0" class="form" title="smilies" align="top" alt="" /></a>
     
              <a href="javascript: Pophistory()">
              <img src="images/ajshoutbox/quote.gif" width="24" height="24" border="0" class="form" title="History/Moderate" align="top" alt="" /></a>
                      <br /><br />
              <div style="display: none;" id="sextra"><?php echo shoutfun('chatForm','chatbarText');?></div><div style="display: none;" id="sextra1"><br><?php echo smileextra();?></div>
              <br />
        </td>
        </tr>
    </table><br />
    </form>
    </div>

     <div id="chat">
     
      <div id="chatoutput">
     
              <ul id="outputList">
     
                    <li>
                      <span class="name"><b><?php echo $site_config['SITENAME'];?> Shoutbox:</b></span><h2 style='padding-left:20px;'>WELCOME</h2>
                     
                            <center><div class="loader"></div></center>
     
                      </li>
     
              </ul>
     
      </div>
           
    </div>
    </td>
        </tr>
    </table><br /><br />
     
    <!--<script language="Javascript">
    $(".toggle").on("click", function () {
        $(".marquee").toggleClass("microsoft");
    })
    </script>-->
    <script language="Javascript">
    function show_hide(sextra)
    {
      if(document.getElementById(sextra))
      {
            if(document.getElementById(sextra).style.display == 'none')
            {
              document.getElementById(sextra).style.display = 'inline';
            }
            else
            {
              document.getElementById(sextra).style.display = 'none';
            }
      }
    }
    function show_hide(sextra1)
    {
      if(document.getElementById(sextra1))
      {
            if(document.getElementById(sextra1).style.display == 'none')
            {
              document.getElementById(sextra1).style.display = 'inline';
            }
            else
            {
              document.getElementById(sextra1).style.display = 'none';
            }
      }
    }
    </script>
    </center>
    <?php
    end_frame();
    }                   
     
    //END_AJAX_SHOUTBOX////////////////////////////////////////////////////////////////////////////////////////////////////////


/*
    ###########################
    #################### - LATEST REQUESTS START - ####################
                   ###########################
     
     
    if (get_user_class() >= 2) {
    if($site_config["REQUESTSON"]){
    }
    begin_frame("".T_("REQUEST")."");
    error_reporting(E_ALL);
       $categ = (int)(isset($_GET["category"]) ? $_GET["category"] : null);
       $requestorid = (int)(isset($_GET["requestorid"]) ? $_GET["requestorid"] : null);
       $sort = (isset($_GET["sort"]) ? $_GET["sort"] : null);
       $search = (isset($_GET["search"]) ? $_GET["search"] : null);
       $filter = (isset($_GET["filter"]) ? $_GET["filter"] : null);
       $search = " AND requests.request like '%$search%' ";
     
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
     
     
    if ($categ == 0)
       $categ = 'WHERE requests.cat > 0 ';
       else
       $categ = "WHERE requests.cat = " . $categ;
     
     
       $res = SQL_Query_exec("SELECT count(requests.id) FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  $categ $filter $search");
       $row = mysqli_fetch_array($res);
       $count = $row[0];
     
     
     
    if($count>0){
     
    $res = SQL_Query_exec("SELECT users.downloaded, users.uploaded, users.username, users.privacy, requests.filled, requests.comments,
      requests.filledby, requests.id, requests.userid, requests.request, requests.done, requests.profilled, requests.added, requests.hits, categories.name as cat,
      categories.parent_cat as parent_cat, categories.image as catpic, categories.id AS cat_id, categories.name AS cat_name
      FROM requests inner join categories on requests.cat = categories.id inner join users on requests.userid = users.id  ORDER BY requests.added DESC LIMIT 10");
       $num = mysqli_num_rows($res);
     
    if ($site_config["REQ_SUB_IMAGE"]) {
       $subimage = "<th>" . T_("GENRE") . "</th>";
       }else{
       $subimage = "";   
       }
     
       print("<br /><center><a href='reqall.php'>".T_("REQ_TABLE")."</a> - <a href='reqall.php?Section=Request'>".T_("REQ_TABLE2")."</a> - <a style='text-decoration:none' href='reqall.php?Section=my_requests&requestorid=$CURUSER[id]'>".T_("MY_REQUESTS")."</a></center><br />");
       echo (isset($pagertop));
       print("<table class=ttable_headinner width=100% border=0 cellspacing=0 cellpadding=3>\n");
       print("<thead><tr class=ttable_head>
         <th>" . T_("REQ_TYPE") . "</th>                                                                                               
         $subimage
         <th>" . T_("REQUESTED_FILE") . "</th>
         <th>" . T_("REQ_DATE_ADDED") . "</th>
         <th>" . T_("REQUEST_BY") . "<!-- <small>(ratio)</small>--></th>
         <th>" . T_("FILLED") . "</th>
         <th>" . T_("FILLED_BY") . "</th>
         <th>" . T_("VOTES") . "</th>
         </tr></thead>\n");
     
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
       $ratio = "----";
       $res2 = SQL_Query_exec("SELECT username from users where id=" . $arr['filledby']);
       $arr2 = mysqli_fetch_assoc($res2);
     
    if ($arr2['username'])
     
       $filledby = $arr2['username'];
       else
       $filledby = "";
     
    if ($privacylevel == "strong"){
     
    if (get_user_class() <= 5){
     
       $addedby = "<td class=table_col2 align=center>".T_("ANONYMOUS")."</td>";
       }else{
       if ($site_config["REQ_CLASS_USER"]) {
       $addedby = "<td class=table_col2 align=center><a href=account-details.php?id=$arr[userid]>".class_user($arr['username'])." (----)</a></td>";           
       }else{
       $addedby = "<td class=table_col2 align=center><a href=account-details.php?id=$arr[userid]>$arr[username] </a> (----)</td>";                       
       }
       }
       }else{
       if ($site_config["REQ_CLASS_USER"]) {
       $addedby = "<td class=table_col2 align=center><a href=account-details.php?id=$arr[userid]>".class_user($arr['username'])." ($ratio)</a></td>";     
       }else{
       $addedby = "<td class=table_col2 align=center><a href=account-details.php?id=$arr[userid]>$arr[username] </a> ($ratio)</td>";                 
       }
       }
       $filled = $arr['filled'];
     
    if ($filled){
     
       $filled = "<a href=$filled><font color=green><b>".T_("YES")."</b></font></a>";
       if ($site_config["REQ_CLASS_USER"]) {
       $filledbydata = "<a href=account-details.php?id=$arr[filledby]><b>".class_user($arr2['username'])."</b></a>";                               
       }else{
       $filledbydata = "<a href=account-details.php?id=$arr[filledby]>$arr2[username]</a>";                                                     
       }
       }else{
       $filled = "<a href=reqall.php?Section=Request_Details&id=$arr[id]><font color=red><b>".T_("NO")."</b></font></a>";
       $filledbydata  = "<i>". T_("REQ_NOBODY") ."</i>";
       }
       $char1 = 40; //cut name length
       $smallname = htmlspecialchars(CutName($arr["request"], $char1));
       $dispname = "<b>".$smallname."</b>";
     
    if ($site_config["REQ_SUB_IMAGE"]) {
       $subimage = "<td class=table_col2 align=center><img border=0 src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["catpic"] . "\" alt=\"" . $arr["parent_cat"] . "\" title=\"" . $arr["parent_cat"] . "\" /></td>".                                                       
       "<td class=table_col1 align=center><img border=0 width=40 src=\"" . $site_config['SITEURL'] . "/images/categories/" . (isset($arr["cat_pic_sub"])) . "\" alt=\"" . $arr["cat_name"] . "\" title=\"" . $arr["cat_name"] . "\" /></td>";                                               
       }else{
       $subimage = "<td class=table_col1><img border=0 src=\"" . $site_config['SITEURL'] . "/images/categories/" . $arr["catpic"] . "\" alt=\"" . $arr["parent_cat"] . ": " . $arr["cat_name"] . "\" title=\"" . $arr["parent_cat"] . ": " . $arr["cat_name"] . "\" /></td>";                 
       }
       
       
       print("<tr>
      $subimage
      <td class=table_col2 align=left><a style='padding-left:5px;' title='".$arr["request"]."' href=reqall.php?Section=Request_Details&id=$arr[id]>$dispname</a></td>
      <td align=center class=table_col1>".date("d-m-Y \\".T_("A")."\\".T_("T")."\\:H:i:s", utc_to_tz_time($arr["added"]))."</td>$addedby
      <td class=table_col1><center>$filled</center></td>
      <td class=table_col2><center>$filledbydata</center></td>
      <td class=table_col1><center><a href=reqall.php?Section=View_Votes&requestid=$arr[id]><b>$arr[hits]</b></a></center></td>");
       print("</tr>\n");
       }
       print("</table><br />\n");
       print("</form>");
       }else{
       echo"<center><b>No requests yet! - </b> <a href='reqall.php?Section=Request'>Add new</a>";
       }
       echo (isset($pagerbottom));
     
    end_frame();
     
       }else{
    if (get_user_class() >= 2) {
    begin_frame("".T_("REQUEST")."");
     
       echo "<center><b><font color=red>".T_("ERROR_MSG2")."</font></b></center>";
     
    end_frame();
    }
    }
     
                   #########################
    #################### - LATEST REQUESTS END - ####################
                   #########################
*/


// latest torrents
begin_frame(T_("LATEST_TORRENTS"));
//print ("<br /><center><input type='image' src='/images/button_clear-new-tag.png' onclick='location.reload()' value='".T_("CLEAR_NEW_TAG")."' title='".T_("CLEAR_NEW_TAG")."' alt='".T_("CLEAR_NEW_TAG")."'></center>");
print("<br /><center><a href='torrents.php'>".T_("BROWSE_TORRENTS")."</a> - <a href='torrents-search.php'>".T_("SEARCH_TORRENTS")."</a></center><br />");

if ($site_config["MEMBERSONLY"] && !$CURUSER) {
	echo "<br /><br /><center><b>".T_("BROWSE_MEMBERS_ONLY")."</b></center><br /><br />";
} else {
	$query = "SELECT torrents.sticky, torrents.imdb, torrents.tube, torrents.trailers, torrents.id, torrents.anon, torrents.uplreq, torrents.announce, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.image AS cat_pic, categories.parent_cat AS cat_parent, users.username, users.privacy, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE visible = 'yes' AND banned = 'no' ORDER BY sticky ASC, id DESC LIMIT 25";
	
	//reordering test
//	foreach (genrelist() as $cat) {
//	$query = "SELECT torrents.sticky, torrents.imdb, torrents.tube, torrents.trailers, torrents.id, torrents.anon, torrents.uplreq, torrents.announce, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.image AS cat_pic, categories.parent_cat AS cat_parent, users.username, users.privacy, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE visible = 'yes' AND banned = 'no' AND category = ".$cat['id']." ORDER BY sticky ASC, id DESC LIMIT 10";
	///end test
	
	$res = SQL_Query_exec($query);
	if (mysqli_num_rows($res)) {
		///reordering test
	//	echo $cat['name'];
		///end test
		torrenttable($res);
	}else {
        
     print("<div class='f-border'>");
     print("<div class='f-cat' width='100%'>".T_("NOTHING_FOUND")."</div>");
     print("<div>");
     print T_("NO_UPLOADS");
     print("</div>");
     print("</div>");

	}
	if ($CURUSER)
		SQL_Query_exec("UPDATE users SET last_browse=".gmtime()." WHERE id=$CURUSER[id]");

}
end_frame();



if ($site_config['DISCLAIMERON']){
	begin_frame(T_("DISCLAIMER"));
	echo T_("DISCLAIMERTXT");
	end_frame();
}
/**********************************************************************************************/
/*               JQUERY ACCORDEON MOD                    */
/**********************************************************************************************/
if ($site_config['NEWSON'] && $CURUSER['view_news'] == "yes"){
begin_frame(T_("NEWS"));
$res = SQL_Query_exec("SELECT news.id, news.title, news.added, news.body, users.username FROM news LEFT JOIN users ON news.userid = users.id ORDER BY added DESC LIMIT 10");
if (mysqli_num_rows($res) > 0){
print('<div id="accordion">');
$news_flag = 0;
while($array = mysqli_fetch_assoc($res)){
                         if (!$array["username"]) $array["username"] = T_('SYSTEM');
$numcomm = get_row_count("comments", "WHERE news='".$array['id']."'");
// Show first 2 items expanded
if ($news_flag < 2) {
$disp = "block";
$pic = "minus";
} else {
$disp = "none";
$pic = "plus";
}

print("<h3><a href=\"#\">".$array['title']."</a></h3>");
print("<div class=\"accordion-content\"><br />&nbsp;".format_comment($array["body"])."<br /><br /><hr class=\"accordion-hr\"/><p class=\"accordion-left\">".T_("POSTED")." ".T_("BY")." ".$array['username']." at ". date("g:i a", utc_to_tz_time($array['added'])) . " on ".date("F d, Y", utc_to_tz_time($array['added']))."</p><p class=\"accordion-right\"><a href='comments.php?type=news&amp;id=".$array['id']."'>".T_("COMMENTS")." (".number_format($numcomm).")</a></p><br /></div>");
$news_flag++;
}
print('</div>');
}else echo "<br /><b>".T_("NO_NEWS")."</b>";

end_frame();
}

?>
<script>
$( "#accordion" ).accordion({
active: false, collapsible: true, heightStyle: "content", alwaysOpen: false
});
</script>
<?php



stdfoot();
?>
