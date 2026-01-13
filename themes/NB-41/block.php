<?php
//BEGIN FRAME
function begin_frame($caption = "-", $align = "justify"){
    global $THEME, $site_config;
    print("<table class='NB-frame' width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr>
<td valign='top' align='center' width='100%'>
<table class='NB-fhead' width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr>
<td width='20' height='22' class='NB-ftl'><img src='".$site_config['SITEURL']."/themes/NB-41/images/blank.gif' style='display: block;' height='22' width='20'></td>
<td width='100%' align='left' height='22' class='NB-ftr'><table border='0' align='left' cellpadding='0' cellspacing='0'>
<tr>
<td class='NB-fc' height='22'>$caption</td>
<td class='NB-fcr' width='54' height='22'><img src='".$site_config['SITEURL']."/themes/NB-41/images/blank.gif' width='54' height='22'></td>
</tr>
</table></td>
</tr>
</table>
<table class='NB-fbody' width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr>
<td valign='top' width='100%' class='NB-fmm'>");
}


//END FRAME
function end_frame() {
    global $THEME, $site_config;
    print("</td>
</tr>
</table></td>
</tr>
</table>
<BR>");
}

//BEGIN BLOCK
function begin_block($caption = "-", $align = "justify"){
    global $THEME, $site_config;
    print("<table class='NB-block' width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr>
<td valign='top' width='100%'>
<table class='NB-bhead' width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr>
<td width='17' height='22' class='NB-btl'><img src='".$site_config['SITEURL']."/themes/NB-41/images/blank.gif' style='display: block;' height='22' width='17'></td>
<td width='100%' height='22' class='NB-btm'>$caption</td>
<td width='10' height='4' class='NB-btr'><img src='".$site_config['SITEURL']."/themes/NB-41/images/blank.gif' style='display: block;' height='22' width='4'></td>
</tr>
</table>
<table class='NB-bbody' width='100%' border='0' cellspacing='0' cellpadding='0'>
<tr>
<td valign='top' width='100%' class='NB-bmm'>");
}

//END BLOCK
function end_block(){
    global $THEME, $site_config;
    print("</td>
</tr>
</table></td>
</tr>
</table>
<BR>");
}

function begin_table(){
    print("<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"ttable_headouter\" width=\"100%\"><tr><td><table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"ttable_headinner\" width=\"100%\">\n");
}

function end_table()  {
    print("</table></td></tr></table>\n");
}

function tr($x,$y,$noesc=0) {
    if ($noesc)
        $a = $y;
    else {
        $a = htmlspecialchars($y);
        $a = str_replace("\n", "<br />\n", $a);
    }
    print("<tr><td class=\"heading\" valign=\"top\" align=\"right\">$x</td><td valign=\"top\" align=left>$a</td></tr>\n");
}
?>