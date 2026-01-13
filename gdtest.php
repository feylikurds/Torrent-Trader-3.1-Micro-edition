<?php
$im = imagecreatefromjpeg('http://www.drwayneandersen.com/wp-content/uploads/2014/03/certificate-60.jpg') or die("Cannot Initialize new GD image stream");

header('Content-Type: image/jpeg');

imagejpeg($im);
imagedestroy($im);
?>