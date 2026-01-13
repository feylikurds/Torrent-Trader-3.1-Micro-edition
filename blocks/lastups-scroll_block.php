<?php
if ($CURUSER) {
begin_block("HTk Images");
?>

<style type="text/css">
#marqueecontainertorrents{
position: relative;
width: 160px; /*marquee width */
height: 200px; /*marquee height */
background-color: white;
overflow: hidden;
border: 0px;
padding: 1px;
padding-left: 1px;
padding-right: 1px;
}
</style>

<script type="text/javascript">
var delayb4scroll=2000 //Specify initial delay before marquee starts to scroll on page (2000=2 seconds)
var marqueespeed=1 //Specify marquee scroll speed (larger is faster 1-10)
var pauseit=1 //Pause marquee onMousever (0=no. 1=yes)?

////NO NEED TO EDIT BELOW THIS LINE////////////

var copyspeed=marqueespeed
var pausespeed=(pauseit==0)? copyspeed: 0
var actualheight=''

function scrollmarquee() {
if (parseInt(cross_marquee.style.top)>(actualheight*(-1)+8))
	cross_marquee.style.top=parseInt(cross_marquee.style.top)-copyspeed+"px"
else
	cross_marquee.style.top=parseInt(marqueeheight)+8+"px"
}

function initializemarquee() {
cross_marquee=document.getElementById("vmarqueetorrents")
cross_marquee.style.top=0
marqueeheight=document.getElementById("marqueecontainertorrents").offsetHeight
actualheight=cross_marquee.offsetHeight
if (window.opera || navigator.userAgent.indexOf("Netscape/7")!=-1){ //if Opera or Netscape 7x, add scrollbars to scroll and exit
cross_marquee.style.height=marqueeheight+"px"
cross_marquee.style.overflow="scroll"
return
}
setTimeout('lefttime=setInterval("scrollmarquee()",30)', delayb4scroll)
}

if (window.addEventListener)
	window.addEventListener("load", initializemarquee, false)
else 
if (window.attachEvent)
	window.attachEvent("onload", initializemarquee)
else 
if (document.getElementById)
window.onload=initializemarquee
</script>

<div id="2">
<div id="marqueecontainertorrents" onMouseover="copyspeed=pausespeed" onMouseout="copyspeed=marqueespeed">
<div id="vmarqueetorrents" style="position: absolute; width: 100%;">

    <!--YOUR SCROLL CONTENT HERE-->
    
<?php
    $news = SQL_Query_exec("SELECT id, name, added, image1, image2 FROM torrents WHERE banned = 'no' AND visible='yes'");

if (mysqli_num_rows($news) > 0) {
    print("<table align=center cellpadding=0 cellspacing=0 width=100% border=0>");

while ($row2 = mysqli_fetch_array($news, mysqli_NUM)) {
    $tor = $row2['0'];
    $altname = $row2['1'];
    $date_time=get_date_time(time()-(3200*25)); // the 24 is the hours you want listed change by whatever you want
    $orderby = "ORDER BY torrents.id DESC"; //Order

    $limit = "LIMIT 15"; //Limit

    $where = "WHERE banned = 'no' AND visible='yes' AND torrents.id='$tor'";
    $res = SQL_Query_exec("SELECT torrents.id, torrents.image1, torrents.image2, torrents.added, categories.name AS cat_name FROM torrents LEFT JOIN categories ON torrents.category = categories.id $where AND torrents.added >='$date_time' $orderby $limit");
    $row = mysqli_fetch_array($res);
    $cat = $row['cat_name'];
    $img1 = "<a href='$site_config[SITEURL]/torrents-details.php?id=$row[id]'><img border='0' src='uploads/images/$row[image1]' alt=\"$altname / $cat\" width='160' align'center'></a>";

if ($row["image1"] != "") {
    print("<tr><td align=center>". $img1 ."<BR></td></tr>");
}
}
    print("</table>");
}
?>
</div>
</div>
</div>
        
<?php
end_block();
}
?>