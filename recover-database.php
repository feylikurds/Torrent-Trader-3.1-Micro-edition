<?php

	//
	//  TorrentTrader v2.x
	//      $LastChangedDate: $
	//      $LastChangedBy: BigSisma $
	//
	//      http://www.torrenttrader.org
	//
	//
	
	require_once("backend/mysql.php");
	require_once("backend/functions.php");
	dbconn();
	
	if (!$CURUSER || $CURUSER["control_panel"]!="yes"){
		 show_error_msg(T_("ERROR"), T_("SORRY_NO_RIGHTS_TO_ACCESS"), 1);
	}

	$filename = $_GET["filename"];
	
	stdhead(T_("DATABASE RECOVER"));
	
	begin_frame("DATABASE RECOVER");
	header( "refresh:5;url=admincp.php?action=backups" ); 
	echo("<br><center><b>WIP - Work In Progress<br><br>Recover File: $filename</b></center><br><br><br>");
	echo("<center>You'll be redirected in about 5 secs. If not, click <a href='admincp.php?action=backups'>here</a></center>");
	
	end_frame();
	stdfoot();
?>