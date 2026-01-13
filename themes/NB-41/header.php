<!DOCTYPE html>

<script type="text/javascript" src="/js/overlib.js"></script>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script> -->
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $site_config["CHARSET"]; ?>" />
<!-- CSS -->
<!-- Theme css -->

<link rel="shortcut icon" href="<?php echo $site_config["SITEURL"]; ?>/themes/NB-41/images/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/themes/NB-41/theme.css" />
<!--[if IE]>
    <link rel="stylesheet" type="text/css" href="<?php echo $site_config["SITEURL"]; ?>/themes/NB-41/css/ie.css" />
<![endif]-->
<!-- JS -->
<script type="text/javascript" src="<?php echo $site_config["SITEURL"]; ?>/backend/java_klappe.js"></script>
	<script src="<?= $site_config["SITEURL"]; ?>/backend/ajaxonline.js" language="JavaScript" type="text/javascript"></script>
<!--[if lte IE 6]>
    <script type="text/javascript" src="<?php echo $site_config["SITEURL"]; ?>/themes/NB-41/js/pngfix/supersleight-min.js"></script>
<![endif]-->
</head>
<?php
	$page = $_SERVER['REQUEST_URI'];
	$page = str_replace("/","",$page);
	$page = str_replace(".php","",$page);
	$page = str_replace("v2","",$page);
	$page = str_replace("?search=","",$page);
	$page = $page ? $page : 'index'
?>

<body id="NB-body">
<table id="container" width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td class="NB-left"><img src="themes/NB-41/images/blank.gif" width="14" height="200" /></td>
    <td valign="top" id="container2"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td height="152"><a href="index.php"><div id="NB-logo"></div></a>
            <div id="infobar">
			<?php
                if (!$CURUSER){
                echo "[<a href=\"account-login.php\">".T_("LOGIN")."</a>]<b> ".T_("OR")." </b>[<a href=\"account-signup.php\">".T_("SIGNUP")."</a>]";
                }else{
                print (T_("LOGGED_IN_AS").": ".$CURUSER["username"].""); 
                echo " [<a href='account.php' title='Your account'>".T_("ACCOUNT")."</a>]  [<a href=\"account-logout.php\">".T_("LOGOUT")."</a>] ";
                if ($CURUSER["control_panel"]=="yes") {
                    print("[<a href='admincp.php'>".T_("STAFFCP")."</a>] ");
                }
            
                //check for new pm's
                $res = SQL_Query_exec("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " and unread='yes' AND location IN ('in','both')");
                $arr = mysqli_fetch_row($res);
                $unreadmail = $arr[0];
                if ($unreadmail){
                    print("[<b><a href='mailbox.php?inbox'>".T_("NEW_MESSAGE")." <font color='#ff0000'>$unreadmail</font></a></b>] ");
                }else{
                    print("[<a href='mailbox.php'>".T_("YOUR_MESSAGES")."</a>] ");
                }
                //end check for pm's
            }
            ?>
            </div>
          </td>
        </tr>
      </table>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
        
          <td width="27" height="30"><img src="themes/NB-41/images/NB-nav-l.png" width="27" height="30" /></td>
          <td height="30" background="themes/NB-41/images/NB-nav-m.png"><!-- START NAV CODE -->
          <?php if ($CURUSER){ ?>
<!--          <div id='sticker'> -->
            <div id="nav">
            
              <ul id="<?php echo $page ?>">
                <li class="index"><a href="index.php"><span><?php echo T_("HOME");?></span></a></li>
                <li class="forums"><a href="forums.php"><span><?php echo T_("FORUMS");?></span></a></li>
                <li class="torrents-upload"><a href="torrents-upload.php"><span><?php echo T_("UPLOAD_TORRENT");?></span></a></li>
                <li class="torrents"><a href="torrents.php"><span><?php echo T_("BROWSE_TORRENTS");?></span></a></li>
                <li class="torrents-today"><a href="torrents-today.php"><span><?php echo T_("TODAYS_TORRENTS");?></span></a></li> 
                <li class="torrents-search"><a href="torrents-search.php"><span><?php echo T_("SEARCH_TORRENTS");?></span></a></li>
<!--            <li class="online-movies"><a href="jw-cinema.php"><span><?php echo T_("ONLINE_MOVIES");?></span></a></li> -->
               <li class="requests"><a href="reqall.php"><span><?php echo T_("REQ_TABLE");?></span></a></li>
               <li class="catalog"><a href="catalog.php"><span><?php echo T_("CATALOG");?></span></a></li>
              </ul>
            </div>
            <?php } ?>
            <!-- END NAV CODE -->
          </td>
          <td width="27" height="30"><img src="themes/NB-41/images/NB-nav-r.png" width="27" height="30" /></td>
        </tr>
      </table>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <TBODY>
          <tr>
            <td><TABLE cellSpacing="8" cellPadding="0" width="100%" border="0" >
                <TBODY>
                  <TR>
                    <?php if ($site_config["LEFTNAV"]){ ?>
                    <TD vAlign="top" width="180"><?php leftblocks(); ?>
                    </TD>
                    <?php } //LEFTNAV ON/OFF END ?>
                    <TD vAlign="top"><!-- MAIN CENTER CONTENT START -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>    
<link rel="stylesheet" href="<?php echo $site_config["SITEURL"]; ?>/themes/NB-41/accordion/jquery-ui.css">

<script src="<?php echo $site_config["SITEURL"]; ?>/themes/NB-41/accordion/jquery-ui.js"></script>



<link rel="stylesheet" href="/js/prettyphoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<script src="/js/prettyphoto/js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
  $(document).ready(function(){
    $("a[rel^='prettyPhoto']").prettyPhoto({
		animation_speed: 'normal', /* fast/slow/normal */
		theme: 'dark_rounded', /* pp_default / light_rounded / dark_rounded / light_square / dark_square / facebook */
		autoplay: true, /* Automatically start videos: True/False */
		default_width: 854,
		default_height: 480,
		deeplinking: false,
		});
  });
</script>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
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