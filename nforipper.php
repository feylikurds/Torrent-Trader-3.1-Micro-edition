<?php
#================================#
#  TorrentTrader v2.08+          #
#  http://www.torrenttrader.org  #
#--------------------------------#
#  Page created by BigMax        #
#================================#

require_once("backend/functions.php");
dbconn(false);
loggedinonly();

//--| Get Stylesheet (to use this script in a pop-up window)
$th = @mysqli_fetch_array(@sql_query_exec("select uri from stylesheets where id=" . $CURUSER["stylesheet"]));
if ($th) $THEME = $th[uri];
?><link rel="stylesheet" type="text/css" href="<?php echo $site_config['SITEURL'];?>/themes/<?php echo $THEME;?>/theme.css" />
<script type="text/javascript" src="backend/java_klappe.js"></script><?php
//--| End Get Stylesheet

	if (get_user_class() >= 1)
	{
		$action = $_POST["action"];

		if ($action != "rip") { echo "<div style='margin-top:15px; margin-bottom:10px; font:bold 12px Verdana' align='center'>".T_("EXTRACT_TEXT")."</div>"; }
		if ($action == "rip")
		{
			echo "<br />";
			echo "<table class='table_table' align='center' cellpadding='10'>";
			echo "<tr><td class='table_head'><div style='font:bold 12px Verdana' align='center'>".T_("EXTRACTED")."</div></td></tr>";
			echo "<tr><td class='table_col1'><div style='font:normal 12px Verdana'>";
				
			$cleaned = preg_replace('/&#\d{0,10000};/', ' ',($_POST['nfo'])); 
			$cleaned = preg_replace('/([^\w\d\s\-\:\.\/]+)/', '', $cleaned);
			$cleaned = preg_replace('/[ ]*(\r{0,1}\n)[ ]*/', '$1', $cleaned);
			$cleaned = preg_replace('/(ripper)/i', '[b]$1[/b]', $cleaned); 
			echo "<pre>".$cleaned."</pre>";

			echo "</div></td></tr></table>";
			echo "<br />";
			exit;
		}
			
		?>
		<form action="nforipper.php" method="post">
		<input type="hidden" name="action" value="rip">
		<div align="center"><textarea name="nfo" cols="90" rows="45"></textarea></div>
		<div style="margin-top:10px; margin-bottom:10px" align="center"><input type="submit" value="&nbsp;<?php echo T_("RIP_NFO"); ?>&nbsp;"></div>
		<?php
	}
?>