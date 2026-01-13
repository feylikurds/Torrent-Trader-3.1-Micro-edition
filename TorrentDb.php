<?php

/**
* @package TorrentTrader
* @version v2.08
* @author Lee Howarth
*/

class TorrentDb
{
         private $_tid;
        
         public function __construct( $tid = 0 )
         {
                 $this -> _tid = 0 + $tid;
         }
        
         public function getTorrent()
         {
                 $res = SQL_Query_exec("SELECT `data` FROM `tfile` WHERE `tid` = " . sqlesc( $this -> _tid ) );
                
                 if ( ! ( $row = mysqli_fetch_object( $res ) ) )
                 {
                         return false;
                 }
                
                 return unserialize( $row -> data );
         }
        
         public function setTorrent( $data = null )
         {
                 SQL_Query_exec("REPLACE INTO `tfile` SET `tid` = " . sqlesc( $this -> _tid ) . ", `data` = " . sqlesc( serialize( $data ) ) );
         }
}