<?php

/**
* @package TorrentTrader
* @version v2.08
* @author Mostvotedplaya
*/

require_once('backend/functions.php');
dbconn();
loggedinonly();

$tid = ( int ) $_GET['tid'];

$tor = SQL_Query_exec('SELECT `name` FROM `torrents` WHERE `id` = \'' . $tid . '\' AND `banned` = \'no\' AND `external` = \'no\' AND `freeleech` = \'0\'');

if ( ! ( $torrent = mysqli_fetch_row( $tor ) ) )
{
         autolink('index.php', 'o_O What was you expecting?');
}

$count = get_row_count('snatched', 'WHERE `tid` = \'' . $tid . '\'');

list($header, $footer, $limit) = pager(20, $count, 'snatchlist.php?tid=' . $tid . '&amp;');

$qry = "SELECT
                         users.id,
                         users.username,
                         users.class,
                         snatched.uid as uid,
                         snatched.tid as tid,
                         snatched.uload,
                         snatched.dload,
                         snatched.stime,
                         snatched.utime,
                         snatched.ltime,
                         snatched.completed,
                         (
                                         SELECT seeder
                                         FROM peers
                                         WHERE torrent = tid AND userid = uid LIMIT 1
                         ) AS seeding                   
                 FROM
                         snatched
                 INNER JOIN users ON snatched.uid = users.id
                 WHERE
                         users.enabled = 'yes' AND users.status = 'confirmed' AND
                         snatched.tid = '$tid' $limit";
                        
                 $res = SQL_Query_exec($qry);
                
                 $title = sprintf( T_("SNATCHLIST_FOR"), htmlspecialchars( $torrent[ 0 ] ) );
                
                 stdhead( $title );
                 begin_frame( $title );
                
                 if ( mysqli_num_rows($res) > 0 ): ?>
                 <table border="0" class="table_table" cellpadding="3" cellspacing="3" width="100%">
                 <tr>
                         <th class="table_head"><?php echo T_("USERNAME") ?></th>
                         <th class="table_head"><?php echo T_("CLASS") ?></th>
                         <th class="table_head"><?php echo T_("UPLOADED") ?></th>
                         <th class="table_head"><?php echo T_("DOWNLOADED") ?></th>
                         <th class="table_head"><?php echo T_("STARTED") ?></th>
                         <th class="table_head"><?php echo T_("LAST_ACTION") ?></th>
                         <th class="table_head"><?php echo T_("COMPLETED") ?></th>
                         <th class="table_head"><?php echo T_("SEEDING") ?></th>
                         <th class="table_head"><?php echo T_("SEED_TIME") ?></th>
                 </tr>
    <?php while ( $row = mysqli_fetch_row( $res ) ):
				 $start_date_sl = utc_to_tz(get_date_time($row[ 7 ]));
				 $last_action_date_sl = utc_to_tz(get_date_time($row[ 8 ]));
?>
                 <tr align="center">
                         <td class="table_col1"><a href="account-details.php?id=<?php echo $row[ 0 ]; ?>"><?php echo htmlspecialchars( $row[ 1 ] ); ?></a></td>
                         <td class="table_col2"><?php echo get_user_class_name( $row[ 2 ] ); ?></td>
                         <td class="table_col1"><?php echo mksize( $row[ 5 ] ); ?></td>
                         <td class="table_col2"><?php echo mksize( $row[ 6 ] ); ?></td>
         <td class="table_col1"><?php echo date( 'D dS F Y g:i a', utc_to_tz_time($start_date_sl) ); ?></td>
         <td class="table_col2"><?php echo date( 'D dS F Y g:i a', utc_to_tz_time($last_action_date_sl) ); ?></td>
                         <td class="table_col1"><?php echo ( $row[ 10 ] ) ? 'yes' : 'no'; ?></td>
                         <td class="table_col2"><?php echo ( $row[ 11 ] ) ? $row[ 11 ] : 'no'; ?></td>
                         <td class="table_col1"><?php echo ( $row[ 9 ] ) ? seedtime( $row[ 9 ] ) : '-'; ?></td>
                 </tr>
                 <?php endwhile; ?>
                 </table>
                 <?php else: ?>
                 <div align="center"><b><?php echo T_("WAIT_FOR_STATS"); ?></b></div>
                 <?php endif;

                 if ( $count > 20 ) echo $footer;
                
                 end_frame();
                 stdfoot();

?>