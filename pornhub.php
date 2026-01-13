<?php

/**
* @package TorrentTrader
* @version v2.08
* @author Mostvotedplaya
*/

require_once('backend/functions.php');
require_once('backend/pornhubbackend.php');
dbconn();
loggedinonly();

$Porn = new Pornhub();
$Pornhub = $Porn->get();

sort( $Pornhub, SORT_ASC );

if ( is_numeric( $_GET['vid'] ) && isset( $Pornhub[ $_GET[ 'vid' ] ] ) )
{
         $vid = $Pornhub[ $_GET[ 'vid' ] ];

         show_error_msg($vid[ 'Title' ], '<iframe src="http://www.pornhub.com/embed/' . $vid[ 'Video' ] . '" frameborder=0 height="481" width="608" scrolling="no" name="ph_embed_video"></iframe><br /><br /><a href="pornhub.php">Go Back</a>', 1);
}

stdhead('XXX');
begin_frame('XXX');
?>

<table border="0" width="100%">
<tr>
<?php
$x = 0;

for ( $i = 0; $i < count($Pornhub); $i++ )
{
         if (($x % 4 == 0)) echo '</tr><tr>';
        
         echo '<td valign="top" align="center"><a href="pornhub.php?vid=', $i ,'"><img src="', $Pornhub[ $i ][ 'Image' ] ,'" border="0" alt="" title="', $Pornhub[ $i ][ 'Title' ] ,'" /></a><br />', $Pornhub[ $i ][ 'Title' ] ,'</td>';
        
         $x++;
}

?>
</tr>
</table>

<?php
end_frame();
stdfoot();
?>