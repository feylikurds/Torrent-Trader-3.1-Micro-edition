<?php
//      http://www.torrenttrader.org
require_once("backend/functions.php");
dbconn(false);
loggedinonly();

stdhead("porn");

begin_frame("porn");

print($pagertop);
		if ($CURUSER["age"] >= "18") {
print ('<BR /><table class="ttable_headinner" align="center" border="0" cellpadding="0" cellspacing="0" width="70%">');
print ('<tr><td class="ttable_head" width="100%" colspan="2">'.$arr["name"].'</td></tr>');
print ('<td align="left" width="20%">');
print "<iframe src='http://www.xhamster.com' allowFullScreen='true' name='plex' scrolling='auto' frameborder='no' align='center' height = '1000px' width = '1200px'></iframe>";

print ('</td><td width="100%">'.$arr["info"].'</td></tr></table><BR />');
		}
		        else
             print ("<p align='center'>".T_("BE_18_OR_OLDER")."</p>");

    print($pagerbottom);

end_frame();
stdfoot();
?>