<?php

require_once("backend/functions.php");

dbconn();
global $CURUSER;
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );
header("Content-Type: text/html; charset=UTF-8");

if (!$lastID) {
	$lastID = 0;
}
getData($lastID);

function getData($lastID) {
$path=dirname(__FILE__);
  require_once($path."/backend/ping.php"); # getting connection data

global $CURUSER;
if ($CURUSER["view_users"]!="yes") {
die("Sorry, Shoutbox is not available...");
}
    $sql =  "SELECT c.* FROM ajshoutbox c left join users u on c.uid=u.id left join groups g on g.group_id=u.class WHERE c.id > ".$lastID." ORDER BY c.id DESC LIMIT 10";
    $conn = getDBConnection(); # establishes the connection to the database
    $results = SQL_Query_exec($sql, $conn);  
    while ($row = mysqli_fetch_array($results)) {
        $id   = $row['id'];
        $uid  = $row['uid'];
        $time = date('M jS Y', utc_to_tz_time($row['date']));
		$hour = date('g:ia', utc_to_tz_time($row['date']));
        //$time = date('d-m-y, H:i:s', utc_to_tz_time($row['date']));
        $name = $row['name'];
        $text = $row['text'];
            
if ($CURUSER['id'] == $row['uid'] ) {   
   $mid=$row['id'];
	
if ($CURUSER["edit_users"]=="yes") {
   $edit="<a href='javascript:editup($mid,$CURUSER[id]);' style='font-size: 8px'><img src='images/ajshoutbox/shout_edit.png' border='0' title=".T_("EDIT")."\n></a><a href='javascript:delup($mid);' style='font-size: 8px'><img src='images/ajshoutbox/shout_delete.png' border='0' title=".T_("_DEL_")."\n></a>";
   }
//   if ($CURUSER["edit_users"]=="yes") {
//   $edit="<a href='javascript:editup($mid,$CURUSER[id]);' style='font-size: 8px'><img id='EditSwap' src='images/ajshoutbox/shout_edit_gray.gif' border='0' title=".T_("EDIT")."\n></a><a href='javascript:delup($mid);' style='font-size: 8px'><img id='DeleteSwap' src='images/ajshoutbox/shout_delete_gray.png' border='0' title=".T_("_DEL_")."\n></a>";
//   }
   }else
   if ($CURUSER['id']) {
//   $edit="<a href='mailbox.php?compose&id=$uid'><img src='images/ajshoutbox/pm.png' border='0' width='22' height='22' title='Send a private message' alt='Send a private message'></a><a href=javascript:Reply_code('&#10148;".$row['name'].":','chatForm','chatbarText')><img src='images/ajshoutbox/reply.png' border='0' width='22' height='22' style='padding: 0px 0px 0px 3px;' title='Reply to this shout' alt='Reply to this shout'\n></a>";
   $edit="<a href='mailbox.php?compose&id=$uid'><img src='images/ajshoutbox/pm.png' border='0' width='22' height='22' title='Send a private message' alt='Send a private message'></a>";
   }

      $a ="SELECT class FROM `users` WHERE id = ".$row['uid'];
      $can = getDBConnection();
      $res = SQL_Query_exec($a, $can);
	  $ab = mysqli_fetch_row($res);
     
switch ($ab[0]) {
case 8: // Site owner:
$name = "<font color=#00ffbf>".$row['name']."</font>";
break;
case 7: // Administrator:
$name = "<font color=red>".$row['name']."</font>";
break;
case 6: // Super Moderator
$name = "<font color=#009AFF>".$row['name']."</font>";
break;
case 5: // Moderator
$name = "<font color=pink>".$row['name']."</font>";
break;
case 4: // Uploader
$name = "<font color=purple>".$row['name']."</font>";
break;
case 3: // VIP
$name = "<font color=dimgray>".$row['name']."</font>";
break;
case 2: // Power User
$name = "<font color=brown>".$row['name']."</font>";
break;
case 1: // User
$name = "<font color=green>".$row['name']."</font>";
break;
case 0: // System
$name = "<font color=red>".$row['name']."</font>";
}
//mysqli_free_result($a);
$ol3 = mysqli_fetch_array(SQL_Query_exec("SELECT u.warned, u.donated FROM users u WHERE u.id=".$row["uid"]));
$don=$ol3['donated'];       
$warn=$ol3['warned']; 
if($don>0){
$don="<img src=images/star.gif alt=donor width=11 height=11 title=donor border=0>";
}else{
$don="";
}     
if($warn=="yes"){
$warn="<img src=images/warned.png alt=warned width=11 height=11 title=warned border=0>";
}else{
$warn="";
}
//mysqli_free_result($ol3);
$ol3 = mysqli_fetch_array(SQL_Query_exec("SELECT u.avatar FROM users u WHERE u.id=".$row["uid"]));
$av=$ol3['avatar'];
if(!empty($av)){
$av="<img src='".$ol3['avatar']."' alt='my_avatar' width='27' height='27'>";
}else{
$av="<img src='images/default_avatar.png' alt='my_avatar' width='27' height='27'>";
}
if($uid==0){
$av="<img src='images/0_avatar.png' alt='0_avatar' width='27' height='27'>";
}
//mysqli_free_result($ol3);
		
		
     ///This code USES the class_user mod (comment out or delete if you are not using it)
//      $chatout = "
//                  <!--<li><span class='name'><a href=account-details.php?id=".$uid." onmouseover=\" return overlib('<img src=".$av." width=50 border=0>', CENTER);\" onmouseout=\"return nd();\">".class_user($name)."</a>&nbsp;".$don."".$warn."&nbsp;|&nbsp;".$time."</span></li>-->
//                 <!--<li><span class='name'><a href=account-details.php?id=".$uid." onmouseover=\" return overlib('<img src=". $av." width=150 border=0>, CENTER);\" onmouseout=\"return nd();\"></a>".class_user($name)."</span></li>-->
//                 <!--<li><span class='name'>".$hour."&nbsp;".$av."&nbsp;<a href=account-details.php?id=".$uid.">".class_user($name)."</a>&nbsp;".$don."".$warn."&nbsp;|&nbsp;".$time."</span></li>-->
//                 <li><span class='name'>".$av."&nbsp;<a href=account-details.php?id=".$uid.">".class_user($name)."</a>&nbsp;&nbsp;".$hour."&nbsp;".$time."&nbsp;".$edit."&nbsp;&nbsp;".$don."".$warn."</span></li> 
//               <!--<div class='lista' style='text-align:right;
//                                      margin-top:-20px;
//                                    margin-bottom:0px;
//                                   /* color: #006699;*/
//                          '>-->
//                          </div>
//                 <!-- # chat output -->
//                 <div class='chatoutput'>".format_comment($text)."</div>
//                 ";
//         echo $chatout;
	/// End code WITH class_user mod
		
	/// This code does NOT have class_user mod (comment out or delete if you are not using it)
		      $chatout = "
                  <!--<li><span class='name'><a href=account-details.php?id=".$uid." onmouseover=\" return overlib('<img src=".$av." width=50 border=0>', CENTER);\" onmouseout=\"return nd();\">".($name)."</a>&nbsp;".$don."".$warn."&nbsp;|&nbsp;".$time."</span></li>-->
                 <!--<li><span class='name'><a href=account-details.php?id=".$uid." onmouseover=\" return overlib('<img src=". $av." width=150 border=0>, CENTER);\" onmouseout=\"return nd();\"></a>".($name)."</span></li>-->
                 <!--<li><span class='name'>".$hour."&nbsp;".$av."&nbsp;<a href=account-details.php?id=".$uid.">".($name)."</a>&nbsp;".$don."".$warn."&nbsp;|&nbsp;".$time."</span></li>-->
                 <li><span class='name'>".$av."&nbsp;<a href=account-details.php?id=".$uid.">".($name)."</a>&nbsp;&nbsp;".$hour."&nbsp;".$time."&nbsp;".$edit."&nbsp;&nbsp;".$don."".$warn."</span></li> 
               <!--<div class='lista' style='text-align:right;
                                      margin-top:-20px;
                                    margin-bottom:0px;
                                   /* color: #006699;*/
                          '>-->
                          </div>
                 <!-- # chat output -->
                 <div class='chatoutput'>".format_comment($text)."</div>
                 ";
         echo $chatout;
///End code WITHOUT class_user mod
}
    }
  function execcommand_message ($message = '<div style="background: #000000; border: 1px solid #EA5F00; padding-left: 5px; color:orangered;">Your command has been executed. (Results may be shown in next refresh!)</div>', $forcemessage = false)
  {
    if ((mysqli_affected_rows ($conn) OR $forcemessage))
    {
      echo $message;
    }
  }
  function execcommand_clean ($Data)
  {
    $Data = trim ($Data[0][1]);
    if (empty ($Data))
    {   
      (SQL_Query_exec ("TRUNCATE ajshoutbox") OR sqlerr (__FILE__, 284));    
      execcommand_message ();
    }
    else
    {  
      $query = SQL_Query_exec ("SELECT id FROM users WHERE username = " . sqlesc ($Data));
      if (0 < mysqli_num_rows ($query))
      {
        $Userid = mysqli_result ($query, 0, 'id');   
        (SQL_Query_exec ("delete from ajshoutbox where uid = " . sqlesc ($Userid)) OR sqlerr (__FILE__, 293));
        execcommand_message ();
      }
    }
    return true;
  }
  function execcommand_noclean ($Data)
  {    
    (SQL_Query_exec ("delete from ajshoutbox WHERE text='/clean'") OR sqlerr (__FILE__, 284));
     
   }   
?>
