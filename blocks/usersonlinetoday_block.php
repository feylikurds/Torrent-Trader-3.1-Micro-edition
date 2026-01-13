<?php

begin_block(T_("VISITORS_TODAY"));

$expires = 900; // Cache time in seconds


if (($rows = $TTCache->Get("usersonlinetoday_block", $expires)) === false) {
$res = SQL_Query_exec("SELECT id, username, class, donated, warned FROM users WHERE enabled = 'yes' AND status = 'confirmed' AND privacy !='strong' AND UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(users.last_access) <= 86400");

$rows = array();
while ($row = mysqli_fetch_assoc($res)) {
$rows[] = $row;
}

$TTCache->Set("usersonlinetoday_block", $rows, $expires);
}

if (!$rows) {
echo T_("NO_USERS_ONLINE");
} else {
echo "<div id='uOnline' class='bMenu'><ul>\n";;
for ($i = 0, $cnt = count($rows), $n = $cnt - 1; $i < $cnt; $i++) {
$row = &$rows[$i];



$warned = null;

if ( $row['warned'] == 'yes' )
{
$warned = '<img src="images/warned.png" alt="Warned" title="Warned" border="0" />';
}

$donated = null;

if ($row['donated'] > 0)
{
$donated = '<img src="images/star.png" alt="Donated" title="Donated" border="0" />';
}


echo "<li><a href='account-details.php?id=$row[id]'><font style='color: ".$color."'>" . class_user($row["username"]) . "</font> $warned $donated</a>".($i < $n ? "" : "")."</li>\n";;
}
echo "</ul></div>\n";;
}

end_block();
?>