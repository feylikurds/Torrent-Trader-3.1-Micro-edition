<?php
$timezone = date_default_timezone_get();
echo "The current server timezone is: " . $timezone;
$date = date('m/d/Y h:i:s a', time());
echo " and the current time is: " . $date;
?>