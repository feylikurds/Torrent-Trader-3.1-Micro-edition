<?php

  /*
  * @package TorrentTrader
  * @version v2.08
  */

  require_once('backend/functions.php');
  dbconn();
  loggedinonly();

  if (($news = $TTCache->Get('TorrentFreak', 1800)) === false)
  {
          $data = simplexml_load_file("http://feed.torrentfreak.com/Torrentfreak/");

          $news = array();
          foreach ($data->channel->item as $item)
          {
                  $news[] = array(
                   'title'  => strip_tags( $item->title ),
                   'date'   => date( 'Y-m-d H:i:s', strtotime( $item->pubDate ) ),
                   'link'   => htmlspecialchars( $item->link ),
                   'description' => strip_tags( $item->description )
                   );
          }
          
          $TTCache->Set('TorrentFreak', $news, 1800);
  }

  stdhead( T_( 'TORRENTFREAK' ) );

  foreach ($news as $post)
  {
          begin_frame( $post['title'] );
          echo CutName( $post['description'], strlen($post['description']) ) . '<br /><br />';
          echo 'Posted On:' . utc_to_tz( $post['date'] ) . ' <a href="' . $post['link'] . '" target="_blank">' . T_('READMORE') . '</a> ';
          end_frame();
  }

  stdfoot();

?>