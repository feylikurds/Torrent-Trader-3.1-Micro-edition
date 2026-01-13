<?php
require_once("backend/functions.php");
dbconn(true);
loggedinonly();

$id = $_GET["id"];
if (!is_valid_id($id))
$id = $CURUSER["id"];

$res = SQL_Query_exec("SELECT * FROM users WHERE status = 'confirmed' AND invited_by = $id ORDER BY username");
$num = mysqli_num_rows($res);

stdhead("Invite Tree for ".$id."");


$invitees = number_format(get_row_count("users", "WHERE status = 'confirmed' && invited_by = $id"));
if ($invitees == 0)
show_error_msg("Nothing to see here!", "<div style='margin-top:10px; margin-bottom:10px' align='center'><font size=2>This member has no invitees</font></div>
<div style='margin-bottom:10px' align='center'>[<a href=account-details.php?id=$id>Go Back to User Profile</a>]</div>");

if ($id != $CURUSER["id"]) {
begin_frame("Invite Tree for [<a href=account-details.php?id=$id>".$id."</a>]");
} else {
begin_frame("You have $invitees invitees ".class_user($CURUSER["username"])."");
}
print("<br />"); //one small space here!

print("<table class=table_table border=1 cellspacing=0 cellpadding=5 align=center>\n");
print("<tr>
<td class=table_head><b>Invited&nbsp;Members</b></td>
<td class=table_head><b>Class</b></td>
<td class=table_head align=center><b>Registered</b></td>
<td class=table_head align=center><b>Last&nbsp;access</b></td>
<td class=table_head align=center><b>Downloaded</b></td>
<td class=table_head align=center><b>Uploaded<b></td>
<td class=table_head align=center><b>Ratio</b></td>
<td class=table_head align=center><b>Warned</b></td>
</tr>\n");

for ($i = 1; $i <= $num; $i++) {

$arr = mysqli_fetch_assoc($res);

if ($arr["invited_by"] != $CURUSER['id'] && $CURUSER["class"] < 5) {
print("<tr><td class=table_col1 align=center colspan=8><font color=red><b>Access Denied</b>.</font>&nbsp; You don't have permission to view the invitees of other users!</td></tr>\n");
print("</table>\n");
print("<br />"); //one small space here!
end_frame();
stdfoot();
}

if ($arr['added'] == '0000-00-00 00:00:00')
$arr['added'] = '---';

if ($arr['last_access'] == '0000-00-00 00:00:00')
$arr['last_access'] = '---';

if($arr["downloaded"] != 0){
$ratio = number_format($arr["uploaded"] / $arr["downloaded"], 2);
} else {
$ratio="---";
}
$ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";

if ($arr["warned"] !== "yes"){
$warned = "<font color=limegreen><b>No</b></font>";
} else {
$warned = "<font color=red><b>Yes</b></font>";
}

$class=get_user_class_name($arr["class"]);
$added = substr($arr['added'],0,10);
$last_access = substr($arr['last_access'],0,10);
$downloaded = mksize($arr["downloaded"]);
$uploaded = mksize($arr["uploaded"]);

print("<tr><td class=table_col1 align=left><a href=account-details.php?id=$arr[id]><b>".class_user($arr[username])."</b></a></td>
<td class=table_col2 align=left>$class</td>
<td class=table_col1 align=center>$added</td>
<td class=table_col2 class=table_col1 align=center>$last_access</td>
<td class=table_col1 align=center><font color=orangered>$downloaded</font></td>
<td class=table_col2 align=center><font color=limegreen>$uploaded</font></td>
<td class=table_col1 align=center>$ratio</td>
<td class=table_col2 align=center>$warned</td>
</tr>\n");
}
print("</table>\n");

if ($arr["invited_by"] != $CURUSER['id']) {
print("<div style='margin-top:10px' align='center'>[<a href=account-details.php?id=$id><b>Go Back to User Profile</b></a>]</div>");
}
print("<br />"); //one small space here!

end_frame();
stdfoot();
?>