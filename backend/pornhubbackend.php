<?php
 
  /**
  * @package TorrentTrader
  * @version v2.08
  * @author  Most voted & wanted Playa !
  */
 
  class Pornhub
  {
          public function get($_path = 'http://www.pornhub.com/')
          {
                  if (($_data = $this->_request($_path)) && ($_data = $this->_parse($_data)))
                  {
                           return $_data;
                  }
                  
                  return ( bool ) false;
          }
          
          private function _request($path)
          {
                  $ch = curl_init();
                  
                  if ( is_resource($ch) )
                  {
                           curl_setopt($ch, CURLOPT_URL, $path);
                           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                           curl_setopt($ch, CURLOPT_HEADER, false);
                           curl_setopt($ch, CURLOPT_REFERER, 'http://www.pornhub.com/');
                           curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                           curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                           $_data = curl_exec($ch);
                           curl_close($ch);
                  }                                                        
                          
                  return empty( $_data ) ? false : $_data;
          }
          
          private function _parse($_data)
          {
                  if ( preg_match_all( '#videos_(\d+)" id="">(.*?)</li>#msi', $_data, $m ) )
                  {
                           return array_map(array($this, '_subParse'), $m[ 2 ]);
                  }
                  
                  return ( bool ) false;
          }
          
          private function _getVideo($vid)
          {
                  if (($_data = $this->_request("http://www.pornhub.com/view_video.php?viewkey=$vid")) && (preg_match('#/embed/(\d+)#', $_data, $m)))
                  {
                           return $m[ 1 ];
                  }
                  
                  return null;
          }
          
          private function _subParse($_data)
          {
                  preg_match( '#viewkey=(\d+)#msi', $_data, $v );
                  preg_match( '#data-smallthumb="(.*?)" target="" title="(.*?)"#msi', $_data, $m );
                  
                  $ret = Array(
                        'Title' => $m[ 2 ],
                        'Image' => $m[ 1 ],
                        'Video' => $this->_getVideo( $v[ 1 ] )
                  );
                        
                  return $ret;
          }
  }