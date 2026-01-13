<?php
require_once("backend/functions.php");
dbconn();
    if ($site_config["MEMBERSONLY"])
    {
        loggedinonly();
        if ($CURUSER["view_torrents"] == "no")
            show_error_msg(T_("ERROR"), T_("NO_TORRENT_VIEW"), 1);
    }
    $id = (int) $_GET["id"];
    $res = SQL_Query_exec("SELECT name, external, banned FROM torrents WHERE id = $id");
    $row = mysqli_fetch_assoc($res);
    if ((!$row) || ($row["banned"] == "yes" && $CURUSER["edit_torrents"] == "no"))
        show_error_msg(T_("ERROR"), T_("TORRENT_NOT_FOUND"), 1);
    if ($row["external"] == "yes")
        show_error_msg(T_("ERROR"), T_("THIS_TORRENT_IS_EXTERNALLY_TRACKED"), 1);
    $res = SQL_Query_exec("SELECT users.id, users.username, users.uploaded, users.downloaded, users.privacy, completed.date FROM users LEFT JOIN completed ON users.id = completed.userid WHERE users.enabled = 'yes' AND completed.torrentid = '$id'");
    if (mysqli_num_rows($res) == 0)
        show_error_msg(T_("ERROR"), T_("NO_DOWNLOADS_YET"), 1);
    $title = sprintf(T_("COMPLETED_DOWNLOADS"), CutName($row["name"], 70));
stdhead($title);
begin_frame($title);
?>
    <br />
    <table cellpadding="5" cellspacing="0" align="center" class="table_table" width="99%">
        <tr>
            <td class="table_head"> <b><?php echo T_("USERNAME"); ?></b> | <b><?php echo T_("RATIO"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("STARTED"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("COMPLETED"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("LAST_ACTION"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("UPLOADED"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("DOWNLOADED"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("RATIO"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("SEED_TIME"); ?></b> </td>
            <td class="table_head" align="center"> <b><?php echo T_("SEEDING"); ?></b> </td>
            <td class="table_head" align="center"> <font color="#FF1200"><b>H</b><small>&</small><b>R</b></font> </td>
        </tr>
        <?php
        while ($row = mysqli_fetch_assoc($res))
        {
            if (($row["privacy"] == "strong") && ($CURUSER["edit_users"] == "no"))
            continue;
            if ($row['downloaded'] > 0)
            {
                $ratio = $row['uploaded'] / $row['downloaded'];
                $ratio = number_format($ratio, 2);
                $color = get_ratio_color($ratio);
                if ($color)
                    $ratio = "<font color=$color>$ratio</font>";
            } else if ($row['uploaded'] > 0)
                $ratio = 'Inf.';
            else
                $ratio = '---';
            $comdate = date("d.M.Y<\\b\\r><\\s\\m\\a\\l\\l>H:i</\\s\\m\\a\\l\\l>", utc_to_tz_time($row["date"]));
            $peers = (get_row_count("peers", "WHERE torrent = '$id' AND userid = '$row[id]'")) ? "<font color='#27B500'><b>".T_("YES")."</b></font>" : "<font color='#FF1200'><b>".T_("NO")."</b></font>";
            $res2 = SQL_Query_exec("SELECT uload, dload, stime, utime, ltime, hnr FROM snatched WHERE tid = '$id' AND uid = '$row[id]'");
            $row2 = mysqli_fetch_assoc($res2);
            if ($row2['dload'] > 0)
            {
                $tratio = $row2['uload'] / $row2['dload'];
                $tratio = number_format($tratio, 2);
                $color = get_ratio_color($tratio);
                if ($color)
                    $tratio = "<font color=$color>$tratio</font>";
            } else if ($row2['uload'] > 0)
                $tratio = 'Inf.';
            else
                $tratio = '---';
            $startdate = utc_to_tz(get_date_time($row2['stime']));
            $lastaction = utc_to_tz(get_date_time($row2['utime']));
            $upload = "<font color='#27B500'><b>".mksize($row2["uload"])."</b></font>";
            $download = "<font color='#FF1200'><b>".mksize($row2["dload"])."</b></font>";
            $seedtime = $row2['ltime'] ? mkprettytime($row2['ltime']) : '---';
            if ($row2['hnr'] != "yes") { $hnr = "<font color='#27B500'><b>".T_("NO")."</b></font>";  } else { $hnr = "<font color='#FF1200'><b>".T_("YES")."</b></font>"; }
            ?>
            <tr>
                <td class="table_col1"><a href="account-details.php?id=<?php echo $row["id"]; ?>"><b><?php echo $row["username"]; ?></b></a> | <b><?php echo $ratio; ?></b></td>
                <td class="table_col2" align="center"><?php echo date('d.M.Y<\\b\\r>H:i', sql_timestamp_to_unix_timestamp($startdate));?></td>
                <td class="table_col1" align="center"><?php echo $comdate; ?></td>
                <td class="table_col2" align="center"><?php echo date('d.M.Y<\\b\\r>H:i', sql_timestamp_to_unix_timestamp($lastaction));?></td>
                <td class="table_col1" align="center"><?php echo $upload; ?></td>
                <td class="table_col2" align="center"><?php echo $download; ?></td>
                <td class="table_col1" align="center"><b><?php echo $tratio; ?></b></td>
                <td class="table_col2" align="center"><?php echo $seedtime; ?></td>
                <td class="table_col1" align="center"><?php echo $peers; ?></td>
                <td class="table_col2" align="center"><b><?php echo $hnr; ?></b></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <div style="margin-top:10px; margin-bottom:10px" align="center">
        <a href="torrents-details.php?id=<?php echo $id; ?>"><?php echo "<input type='submit' value='".T_("BACK_TO_DETAILS")."'>"; ?></a>
    </div>
<?php
end_frame();
stdfoot();
?>