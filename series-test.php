<?php
require_once("backend/functions.php");
dbconn(false);
stdhead("Add new series " . $_POST['show']);

$url="http://www.thetvdb.com/api/GetSeries.php?seriesname=";
$xml=simplexml_load_file($url . $_POST['show']);
$series=$xml->Series[0];
//print_r($xml);
if($series->seriesid!='') {
    $result=SQL_Query_exec("select id from series where tvdbid = " . $series->seriesid);
    if(mysqli_num_rows($result)<1) { //Series not in our db, insert it!
        $tvdbid=$series->seriesid;
        $name=mysqli_real_escape_string($series->SeriesName);
        $overview=mysqli_real_escape_string($series->Overview);
        $firstaired=$series->FirstAired;
        $imdbid=$series->IMDB_ID;
        $banner=mysqli_real_escape_string($series->banner);
        //$query = "insert into series(tvdbid,name,overview,firstaired,imdbid) values($tvdbid,'$name','$overview','$firstaired','$imdbid')";
        //print $query;
        $res=SQL_Query_exec("insert into series(tvdbid,name,overview,firstaired,imdbid,banner) values($tvdbid,'$name','$overview','$firstaired','$imdbid','$banner')");
        
        //Upload Banner
                if($series->banner!='') {
                $cont=file_get_contents("http://thetvdb.com/banners/" . $series->banner);
                $filename="series/banners/" . $series->banner;
                $file=fopen($filename, 'w');
                fwrite($file, $cont);
                fclose($file);
                } 
        
        //Get Episode Info for series
        $url2="http://thetvdb.com/api/1DAE7A9823E16F0D/series/";
        $xml=simplexml_load_file($url2 . $tvdbid . "/all/en.xml");
        $series=$xml->Series;
        $dayofweek=$series->Airs_DayOfWeek;
        $airtime=$series->Airs_Time;
        $genre=$series->Genre;
        $status=$series->Status;
        $rating=$series->Rating;
        $res=SQL_Query_exec("update series set dayofweek='$dayofweek',airtime='$airtime',genre='$genre',status='$status',rating='$rating' where tvdbid=$tvdbid");
        foreach($xml->Episode as $episode) {
            $epname=mysqli_real_escape_string($episode->EpisodeName);
            $guests=mysqli_real_escape_string($episode->GuestStars);
            $overview=mysqli_real_escape_string($episode->Overview);
            $airdate=mysqli_real_escape_string($episode->FirstAired);
            $id=$episode->id;
            $season=$episode->SeasonNumber;
            $seriesid=$episode->seriesid;
            $episode=$episode->EpisodeNumber;
            
            $query="insert into episodes(tvdbid,seriesid,name,number,season,gueststars,overview,firstaired) values ($id,$seriesid,'$epname'," . (string) $episode . "," . (string) $season . ",'$guests','$overview','$airdate')";
            //print $query."<br>";
            $res=SQL_Query_exec($query);
        }
        show_error_msg("Done", "Show $name entered into the database. Head to <a href=\"series-add.php\">Series Page</a> to add more, or click <a href=\"series.php\">HERE</a> to go back", 1);
    } else {
        show_error_msg("Error", "Show already in our Database", 1);
    }
} else {
    show_error_msg("Error", "Show " . $_POST['show'] . " Not found or errored out!! Head to <a href=\"series-add.php\">Series Page</a> to try again!", 1);
}

stdfoot();
?>