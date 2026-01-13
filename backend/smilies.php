<?php
//
//  TorrentTrader v2.x
//      $LastChangedDate$
//      $LastChangedBy$
//
//      http://www.torrenttrader.org
//
//

 require_once("functions.php");  
 
 $smilies = array
 (
 ":senile:"       =>  "to_become_senile.gif",
   ":)"       =>  "smile.gif",
   ":]"      =>   "smiley.gif",
   ":D"       =>   "grin.gif",
   ":lol:"     =>   "lol.gif",
   ":rofl:"    =>   "rofl.gif",
   ":S"       =>   "sarcastic.gif",
   ":w00t:"    =>   "w00t.gif",
   ":-/"       =>   "confused.gif",
   ":|"       =>   "noexpression.gif",
   ":("       =>   "sad.gif",
   ":P"       =>   "tongue.gif",
   ":8)"      =>   "cool.gif",
   ":wave:"    =>   "wave.gif",
   ":ok:"      =>   "ok.gif",
   ":bad:"     =>   "bad.gif",
   ":evil:"    =>   "evil.gif",
   ":rant:"    =>   "rant.gif",
   ":love:"    =>   "love.png",
   ":idea:"    =>   "idea.png",
   ":quest:"   =>   "question.gif",
   ":!:"       =>   "important.png",
   ":fbd:"     =>   "forbidden.png",
   ":warn:"    =>   "warn.png",
   ":dis:"     =>   "disable.gif",
   ":bomb:"    =>   "bomb.png",
   ":+y:"      =>   "afirmative.gif",
   ":-n:"      =>   "negative.gif",
   ":angry:"   =>   "angry.gif",
   ":shit:"    =>   "shit.gif",
   ":weep:"    =>   "weep.gif",
   ":crazy:"   =>   "crazy.gif",
   ":ilv:"     =>   "in-love.gif",
   ":secret:"  =>   "secret.gif",
   ":geek:"    =>   "geek.gif",
   ":yahoo:"   =>   "yahoo.gif",
   ":tease:"   =>   "tease.gif",
   ":moon:"    =>   "mooning.gif",
   ":good:"    =>   "good.gif",
   ":read:"    =>   "read.gif",
   ":scratch:" =>   "scratch.gif",
   ":victory:" =>   "victory.gif",
   ":whistle:" =>   "whistle.gif",
   ":pardon:"  =>   "pardon.gif",
   ":punish:"  =>   "punish.gif",
   ":gamer:"   =>   "gamer.gif",
   ":dance:"   =>   "dance.gif",
   ":mail:"    =>   "mail.gif",
   ":resent:"  =>   "resent.gif",
   ":t-up:"    =>   "thumbsup.gif",
   ":t-down:"  =>   "thumbsdown.gif",
   ":hmm:"     =>   "hmm.gif",
   ":shoot:"   =>   "shooting.gif",
   ":hunter:"  =>   "hunter.gif",
   ":rroule:"  =>   "russian-roulette.gif",
   ":suicid:"  =>   "suicide.gif",
   ":dash:"    =>   "dash.gif",
   ":vip:"     =>   "vip.gif",
   ":bdance:"  =>   "bananadance.gif",
   ":heat:"    =>   "heat.gif",
   ":fishing:" =>   "fishing.gif",
   ":clapp:"   =>   "clapping.gif",
   ":popcorm:" =>   "popcorm.gif",
   ":pepsi:"   =>   "pepsi.gif",
   ":pimp:"    =>   "pimp.gif",
   ":sponge:"  =>   "alcoholic.gif",
   "drinks"    =>   "drinks.gif",
   ":friends:" =>   "friends.gif",
   ":happy:"   =>   "happy.gif",
   ":santa:"   =>   "santa.gif",
   ":yard:"    =>   "construction.gif",
   ":helpme:"  =>   "help-me.gif",
   ":hbd:"     =>   "hbd.gif",
   ":party:"   =>   "party.gif",
   ":google:"  =>   "google.gif",
   ":please:"  =>   "please.gif",
   ":sorry:"   =>   "sorry.gif",
   ":oops:"    =>   "oops.gif",
   ":spam:"    =>   "spam.gif",
   ":otp:"     =>   "offtopic.gif",
   ":super:"   =>   "super.gif",
   ":rofl:"    =>   "rofl.gif",
   ":wacko:"   =>   "wacko.gif",
   ":sheep1:"  =>   "sheep1.gif",
   ":newyear:" =>   "newyear.gif",
   ":xmas:"    =>   "xmas.gif",
 );


// New (TorrentialStorm)
function insert_smilies_frame() {
	GLOBAL $site_config, $smilies;

	echo "<table><tr><td>Type...</td><td>To make a...</td></tr>";
	foreach ($smilies as $code => $url) {
		echo "<tr><td>$code</td><td><a href=\"javascript:window.opener.SmileIT('$code', '".cleanstr($_GET["form"])."', '".htmlspecialchars($_GET["text"])."')\"><img src=\"$site_config[SITEURL]/images/smilies/$url\" alt=\"$code\" title=\"$code\" border=\"0\"></a></td></tr>";
	}
	echo "</table>";
}

if ($_GET['action'] == "display"){
	insert_smilies_frame();
}

?>