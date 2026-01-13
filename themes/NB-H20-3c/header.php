<?php
/*
+ ----------------------------------------------------------------------------+
|     �Nikkbu 2012
|     Site: http://nikkbu.info
|     eMail: nikkbu@nikkbu.info
|     Theme: NB-H20-3c -- 1.0.0
|     TT Version: v2 svn
|     TT Revision: 1108
|     Date: 17/10/2012
|     Author: Nikkbu
+----------------------------------------------------------------------------+
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<script type="text/javascript" src="/js/overlib.js"></script>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="refresh" content="1200">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $site_config["CHARSET"]; ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="X-UA-Compatible" content="IE=9" />
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta name="author" content="Nikkbu, TorrentTrader" />
<meta name="generator" content="TorrentTrader <?php echo $site_config['ttversion']; ?>" />
<meta name="description" content="TorrentTrader is a feature packed and highly customisable PHP/MySQL Based BitTorrent tracker. Featuring intergrated forums, and plenty of administration options. Please visit www.torrenttrader.org for the support forums. " />
<meta name="keywords" content="http://nikkbu.info, http://www.torrenttrader.org" />
<!-- CSS -->
<!-- Theme css -->
<link rel="shortcut icon" href="<?php echo $site_config["SITEURL"]; ?>/themes/NB-H20-3c/images/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/themes/NB-H20-3c/theme.css" />
<!-- JS -->
<script type="text/javascript" src="<?php echo $site_config["SITEURL"]; ?>/backend/java_klappe.js"></script>
	<script src="<?= $site_config["SITEURL"]; ?>/backend/ajaxonline.js" language="JavaScript" type="text/javascript"></script>
<!--[if lte IE 6]>
    <script type="text/javascript" src="<?php echo $site_config["SITEURL"]; ?>/themes/NB-H20-3c/js/pngfix/supersleight-min.js"></script>
<![endif]-->
</head>
<?php
	$page = $_SERVER['REQUEST_URI'];
	$page = str_replace("/","",$page);
	$page = str_replace(".php","",$page);
	$page = str_replace("svn","",$page);  //-- name if tracker installed in a sub-dir 
	$page = str_replace("?search=","",$page);
	$page = $page ? $page : 'index'
?>
<body>
<div id='wrapper'>
  <div id='header'>
    <div id='infobar'>
<!-- START INFOBAR -->
    <div class="fltLeft">
    <?php
//        if ($CURUSER["control_panel"]=="yes") {
////	shitty unfinished admin CP		print("<a class='admincp' href=/admincp/>AdminCP</a> ");
//    
//            print("<a class='admincp' href=admincp.php>AdminCP</a> ");
//    
//        }
    ?>
    </div>
    <div class="fltRight">
    <?php
    if (!$CURUSER){
        echo "[<a href=\"account-login.php\">".T_("LOGIN")."</a>]<B> ".T_("OR")." </B>[<a href=\"account-signup.php\">".T_("SIGNUP")."</a>]";
    
    }else{
    
    print (T_("LOGGED_IN_AS").": ".$CURUSER["username"]. "");
    $userdownloaded = mksize($CURUSER["downloaded"]);
    $useruploaded = mksize($CURUSER["uploaded"]);
    
    if ($CURUSER["uploaded"] > 0 && $CURUSER["downloaded"] == 0)
    $userratio = "Inf.";
    elseif ($CURUSER["downloaded"] > 0)
    $userratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 2);
    else
    $userratio = "---";
    
    print (",  <img src='themes/NB-H20-3c/images/downloaded.png' border='none' height='20' width='20' alt='Downloaded' title='Downloaded'> <font color='#CC0000'>$userdownloaded </font> <img src='themes/NB-H20-3c/images/uploaded.png' border='none' height='20' width='20' alt='Uploaded' title='Uploaded'> <font color='#009900'>$useruploaded</font> <img src='themes/NB-H20-3c/images/ratio.png' border='none' height='20' width='20' alt='Ratio' title='Ratio'> $userratio");
    
    echo " <a class='profile' href='account.php'><img src='themes/NB-H20-3c/images/account.png' border='none' height='20' width='20' alt='Your account' title='Your account'></a> <a class='account' href='account-details.php?id=$CURUSER[id]'><img src='themes/NB-H20-3c/images/profile.png' border='none' height='20' width='20' alt='Profile' title='Profile'></a> <a class='logout' href=\"account-logout.php\"><img src='themes/NB-H20-3c/images/logout.png' border='none' height='20' width='20' alt='Logout' title='Logout'></a> ";
        
    //check for new pm's
    
    $res = mysqli_query($GLOBALS["DBconnector"],"SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " and unread='yes' AND location IN ('in','both')") or print(mysqli_error($GLOBALS["DBconnector"]));
    
    $arr = mysqli_fetch_row($res);
    
    $unreadmail = $arr[0];
    
    if ($unreadmail){
    
        print("<a class='mail_n' href=mailbox.php?inbox><img src='themes/NB-H20-3c/images/mail_new.png' border='none' height='20' width='20' alt='New PM' title='($unreadmail) New PM'S'><font color='red'>($unreadmail)</font></a>&nbsp;");
    
    }else{
    
        print("<a class='mail' href=mailbox.php><img src='themes/NB-H20-3c/images/mail.png' border='none' height='20' width='20' alt='My Messages' title='My Messages'></a>&nbsp;");
    
    }
    
    //end check for pm's
    
    }
    
    ?>
    
    </div>
<!-- END INFOBAR -->
    </div>
    <div class='header'>
      <div id='logo'><a href='index.php'><img src='themes/NB-H20-3c/images/blank.gif' width='360' height='64' /></a></div>
    </div>
    <div id='menu'>
    
      <!-- START NAVIGATION -->
      	<?php if ($CURUSER){ ?>

      <ul class='menu' id='<?php echo $page ?>'>
      
        <li class='myLink1'><a href='index.php'><span><?php echo T_("HOME");?></span></a></li>
        <li class='myLink2'><a href='forums.php'><span><?php echo T_("FORUMS");?></span></a></li>
        <li class='myLink3'><a href='torrents-upload.php'><span><?php echo T_("UPLOAD_TORRENT");?></span></a></li>
        <li class='myLink4'><a href='torrents.php'><span><?php echo T_("BROWSE_TORRENTS");?></span></a></li>
<!--        <li class='myLink5'><a href='torrents-today.php'><span><?php echo T_("TODAYS_TORRENTS");?></span></a></li> -->
        <li class='myLink6'><a href='torrents-search.php'><span><?php echo T_("SEARCH_TORRENTS");?></span></a></li>
        <li class='myLink7'><a href='faq.php'><span><?php echo T_("FAQ");?></span></a></li>
        <li class='myLink8'><a href='jw-cinema.php'><span><?php echo T_("ONLINE_MOVIES");?></span></a></li>
        <li class='myLink9'><a href='reqall.php'><span><?php echo T_("REQ_TABLE");?></span></a></li>
        <li class='myLink10'><a href='catalog.php'><span><?php echo T_("CATALOG");?></span></a></li>
	<!--	<li class='myLink11'><a href='search.php'><span><?php echo T_("SEARCH_ENGINE");?></span></a></li> -->

      </ul>
      <?php } ?>
      <!-- END NAVIGATION -->
    </div>
    
  </div>
  
  <div class='myTable'>
    <div class='myTrow'>
      <div class='shad-l'><img src='themes/NB-H20-3c/images/blank.gif' width='9px' height='9px' /></div>
      <div class='main'>
        <table width='100%' border='0' cellspacing='10' cellpadding='0'>
          <tr>
          
            <!-- START LEFT COLUM -->
            <?php if ($site_config["LEFTNAV"]){?>
            <td width='180' valign='top'><?php leftblocks();?>
            </td>
            <?php } //LEFTNAV ON/OFF END?>
            <!-- END LEFT COLUM -->
            <td valign='top'>
            <!-- START MAIN COLUM -->
<link rel="stylesheet" href="<?php echo $site_config["SITEURL"]; ?>/themes/NB-H20-3c/accordion/jquery-ui.css">
<script src="<?php echo $site_config["SITEURL"]; ?>/js/jquery-2.2.3.min.js"></script>
<script src="<?php echo $site_config["SITEURL"]; ?>/themes/NB-H20-3c/accordion/jquery-ui.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/js/YouTubePopUp.css">
<script src=></script>
<script type="text/javascript" src="/js/YouTubePopUp.jquery.js"></script>
<script type="text/javascript">

	jQuery(function(){
		jQuery("a.youtube").YouTubePopUp();
		jQuery("a.bla-2").YouTubePopUp( { autoplay: 0 } ); // Disable autoplay
		});
</script>

<link rel="stylesheet" href="/js/prettyphoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<script src="/js/prettyphoto/js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
  $(document).ready(function(){
    $("a[rel^='prettyPhoto']").prettyPhoto({
		animation_speed: 'normal', /* fast/slow/normal */
		theme: 'facebook', /* pp_default / light_rounded / dark_rounded / light_square / dark_square / facebook */
		autoplay: true, /* Automatically start videos: True/False */
				default_width: 854,
		default_height: 480,
		deeplinking: false,
		show_title: false,
		});
  });
</script>
<script src="/js/jquery.goup.js"></script>
<script>
$(document).ready(function(){
  jQuery.goup();
});
</script>
<script>
jQuery.goup({
  location: 'left',
  goupSpeed: 'fast',
  locationOffset: 40,
  bottomOffset: 20,
  containerSize: 60,
  title: 'Back to top',
  titleAsText: true,
  containerColor: '#000',
  arrowColor: '#fff'
});
</script>
					<?php
	if ($site_config["MIDDLENAV"]){
		middleblocks();
	} //MIDDLENAV ON/OFF END
	?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>