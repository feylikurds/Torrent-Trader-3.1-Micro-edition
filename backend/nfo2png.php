<?php
/*
 * File    : nfo2png.php
 * Created : 14 March 2004
 * By      : Stefan Gräfe
 *
 * NFO2PNG - Small NFO to PNG render library
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
 
function buildNFO($nfotext, $footer = "", $fg = "cc6600") {
  // Write Headers for PNG
  header("Content-Type: image/png");
  header("Content-Disposition: inline; filename=\"nfo2png.sf.net.png\"");
  
  if(!strlen($nfotext)) $nfotext = "Empty String submitted.";

  $nfo = explode("\n", $nfotext);
  
  // Load the Bitmap-Font
  $fnt = imageloadfont("backend/nfo2pngfont");
             
  // Check for empty lines
  $fillers = strlen($nfo[1])+strlen($nfo[3])+strlen($nfo[5])+strlen($nfo[7])<9?1:0;
  
  $nxo = array();
  $xmax = 0;
  
  // Reformat each line
  foreach($nfo as $key=>$line){
    $line = chop($line);
    if($xmax < strlen($line)) $xmax = strlen($line);
    if($fillers and ($key & 1)) continue;
    array_push($nxo,$line);
  }
  
  // Show footer
  if(strlen($footer)) {
    array_push($nxo,"");
    $fill = str_repeat(" ",($xmax - strlen($footer)>>1));
    array_push($nxo,$fill.$footer);
  }
  
  // Linecount
  $ymax = count($nxo);
  
  // Set foreground color
  $color = array(0, 0, 0);
  if(strlen($fg) == 6) {
    $color[0] = intval(substr($fg,0,2), 16);
    $color[1] = intval(substr($fg,2,2), 16);
    $color[2] = intval(substr($fg,4,2), 16);
  }
                      
  // Render NFO
  $im = ImageCreate($xmax*8,$ymax*16);
  ImageInterlace($im,1); 
  $background_color = ImageColorTransparent($im, ImageColorAllocate ($im, 254, 254, 126));
  $text_color = ImageColorAllocate ($im, $color[0], $color[1], $color[2]);
  
  foreach($nxo as $y=>$line)
    ImageString($im, $fnt, 0, $y*16, $line, $text_color);
  ImagePNG($im);
}
?>