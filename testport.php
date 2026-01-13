<?php
require_once("backend/functions.php");
dbconn();
loggedinonly();
stdhead('testport');
begin_frame("".T_("PORTTEST_FOR")." <a href=userdetails.php?id=$CURUSER[id]>".class_user($CURUSER['username'])."</a>");

if ($CURUSER)
{

$ip=$CURUSER['ip'];

if ($_SERVER["REQUEST_METHOD"] == "POST")
$port = $_POST["port"];
else
$port=$_GET['port'];
if ($port)
{
$fp = @fsockopen ($ip, $port, $errno, $errstr, 10);
if (!$fp) {
print ("<table class=table_table align=center border=1 cellspacing=5 cellpadding=5 width=40%>");
print ("<tr><td class=table_head align=center><b>".T_("RESULTS")."</b></td></tr>
<tr><td class=table_col1><br><center><b>".T_("FOR_IP")."  $ip ".T_("IS_PORT")."$port ".T_("IS")."<font color=red>".T_("NOT_OPEN")."</font></b></center><br></td></tr>
<br><tr><td class=table_col1><center><form><input type=\"button\" value=\"".T_("NEW_PORTTEST")."\" onclick=\"window.location.href='testport.php'\"></form></center></td></tr></table><br />");

echo "<center><em><img src=images/testport/giphy.gif></br></br><b>".T_("NOTWORKING")."</b></em></center><br />";
} else {

print ("<table align=center border=1 cellspacing=5 cellpadding=5 width=40%>");
print ("<tr><td class=table_head align=center><b>".T_("RESULTS")."</b></td></tr>
<tr><td class=table_col1><br><center><b>".T_("FOR_IP")." $ip ".T_("IS_PORT")."$port ".T_("IS")."<font color=green>".T_("OPEN")."</font></b></center><br></td></tr>
<br><tr><td class=table_col1><center><form><input type=\"button\" value=\"".T_("NEW_PORTTEST")."\" onclick=\"window.location.href='testport.php'\"></form></center></td></tr></table><br />");
echo "<center><em><img src=images/testport/giphy2.gif></br></br><b>".T_("WORKING")."</b></em></center><br />";
}
}

else
{
print ("<table align=center border=1 cellspacing=5 cellpadding=5 width=40%>");
print ("<tr><td  class=table_head align=center><b>".T_("TEST_PORT")."</b></td><br></tr>");
print ("<form method=post action=testport.php>");
print ("<tr><td align=center class=ttable_col1>".T_("PORT").": <input type=text name=port></td></tr>");
print ("<tr><td  class=ttable_col1><center><input type=submit class=btn value='".T_("TEST")."'></center></td></tr>");
print ("</form>");
print ("</table><br />");


}
}

end_frame();

stdfoot();
?>