<?php
$site_config = array();
$site_config['ttversion'] = '3.1ME';		//DONT CHANGE THIS!

// Site API keys
// Ignore these settings
$site_config['JWPLAYER'] = 'key_here'; //JWPlayer API key here for site videos (jw-view-cinema.php line 43)
$site_config['SITEKEY'] = 'key_here'; //Google robot login captcha site key (account-login.php line 85)
$site_config['SECRET'] = 'key_here'; //Google robot login captcha secret key (account-login.php line 39)
$site_config['TRAILERADDICT'] = 'key_here'; //TrailerAddict.com API key goes here (torrent-details.php line 316)
$site_config['YOUTUBEAPIKEY'] = 'key_here'; //YouTube API Key here for site videos to have titles, images, and description added automagically, go to https://developers.google.com/youtube/v3/getting-started for a key, or just google it

//OMDB API key goes in backend/TTIMDB.php file//

// Main Site Settings
$site_config['SITENAME'] = '';					//Site Name
$site_config['TORRENTCOMMENT'] = '';					//Just something stupid to add. Found in torrents-upload around line 253
$site_config['SITEEMAIL'] = '';		//Emails will be sent from this address, or appear as tho they did if using gmail and pear
//if($_SERVER['SERVER_PORT'] != '443') 			//settings for SSL if you have a certificate
    $site_config['SITEURL'] = '';  //Main Site URL without HTTPS
//    else
//    $site_config['SITEURL'] = ''; //Main Site URL for HTTPS
$site_config['default_language'] = "1";						//DEFAULT LANGUAGE ID
$site_config['default_theme'] = "1";						//DEFAULT THEME ID
$site_config['CHARSET'] = "utf-8";						//Site Charset
$site_config['announce_list'] = "https://domain/announce.php,http://tracker.opentrackr.org:1337/announce"; //seperate via comma
$site_config['MEMBERSONLY'] = true;							//MAKE MEMBERS SIGNUP
$site_config['MEMBERSONLY_WAIT'] = false;					//ENABLE WAIT TIMES FOR BAD RATIO
$site_config['ALLOWEXTERNAL'] = true;		//Enable Uploading of external tracked torrents
$site_config['UPLOADERSONLY'] = false;		//Limit uploading to uploader group only
$site_config['INVITEONLY'] = false;			//Only allow signups via invite
$site_config['ENABLEINVITES'] = true;		//Enable invites regardless of INVITEONLY setting //for private site, set true
$site_config['CONFIRMEMAIL'] = true;		//Enable / Disable Signup confirmation email //for private site, set true
$site_config['ACONFIRM'] = false;			//Enable / Disable ADMIN CONFIRM ACCOUNT SIGNUP //for private site, set true
$site_config['ANONYMOUSUPLOAD'] = false;	//Enable / Disable anonymous uploads
$site_config['CLASS_USER'] = true ; 		// only used for site catalog. Leave TRUE. Does not turn off class user colors. Im just lazy as hell ha ha. 
$site_config['new_member_upload_ratio'] = 2147483648; //2147483648 = 2GB of upload and inf. ratio to help get started
$site_config['new_member_invites'] = 0 ; 	// Every new user will get 0 invites to start with. Change this to what you need. Does not affect the regular giving of invites set by the you or the system


// Maximum accounts using the same IP
$site_config["ipcheck"] = true; // Enable/disable
$site_config["accountmax"] = 10; // max number of accounts

//Shoutbox selection
$site_config["AJSHOUTBOX"] = true; //True = Ajax shoutbox, False = Stock TT.2.08 shoutbox.

/// START UPLOAD AVATAR ///
$site_config['AVATARUPLOAD'] = true;      // Enable / Disable upload avatar
$site_config['avatar_dir'] = '/avatars';   // Dir where will be stored avatars. chmod 777

$site_config['PASSKEYURL'] =  "$site_config[SITEURL]/announce.php?passkey=%s"; // Announce URL to use for passkey
$site_config['UPLOADSCRAPE'] = true; // Scrape external torrents on upload? If using mega-scrape.php you should disable this
$site_config['FORUMS'] = true; // Enable / Disable Forums
$site_config['FORUMS_GUESTREAD'] = false; // Allow / Disallow Guests To Read Forums
$site_config["OLD_CENSOR"] = true; // Use the old change to word censor set to true otherwise use the new one.   

$site_config['maxusers'] = 5000; // Max # of enabled accounts
$site_config['maxusers_invites'] = $site_config['maxusers'] + 1000; // Max # of enabled accounts when inviting

$site_config['currency_symbol'] = '$'; // Currency symbol (HTML allowed)

//AGENT BANS (MUST BE AGENT ID, USE FULL ID FOR SPECIFIC VERSIONS, Separate by comma if banning multiple versions)
$site_config['BANNED_AGENTS'] = "-AZ21, -BC, LIME"; // Example: Ban utorrent 2.2.1 agent ID is -UT2210-. Find agent ID in ACP/Torrent clients used. First 8 characters

//PATHS, ENSURE THESE ARE CORRECT AND CHMOD TO 777 (ALSO ENSURE TORRENT_DIR/images is CHMOD 777)
$site_config['torrent_dir'] = getcwd().'/uploads';
$site_config['nfo_dir'] = getcwd().'/uploads';
$site_config['blocks_dir'] = getcwd().'/blocks';

// Image upload settings
$site_config['image_max_filesize'] = 524288; // Max uploaded image size in bytes (Default: 512 kB)
$site_config['avatar_max_filesize'] = 2097152; // Max uploaded image size in bytes (Default: 2 MB)
$site_config['allowed_image_types'] = array(
					// "mimetype" => ".ext",
					"image/gif" => ".gif",
					"image/pjpeg" => ".jpg",
					"image/jpeg" => ".jpg",
					"image/jpg" => ".jpg",
					"image/png" => ".png"
				);

$site_config['SITE_ONLINE'] = true;									//Turn Site on/off
$site_config['OFFLINEMSG'] = 'Site is down for Maintenance, but we will be back soon';	

$site_config['WELCOMEPMON'] = true;			//Auto PM New members
$site_config['WELCOMEPMMSG'] = 'Thank you for registering at our tracker! Please seed anything you download for as long as you can :)';

$site_config['SITENOTICEON'] = false;
$site_config['SITENOTICE'] = 'Welcome To our site<br /><br />Please keep your downloads seeded as long as you can<br /><br />';

$site_config['UPLOADRULES'] = 'Try to make sure your torrents are seeded as long as possible<br />Upload away';

//Setup Site Blocks
$site_config['LEFTNAV'] = true; //Left Column Enable/Disable
$site_config['RIGHTNAV'] = true; // Right Column Enable/Disable
$site_config['MIDDLENAV'] = true; // Middle Column Enable/Disable
$site_config['SHOUTBOX'] = true; //enable/disable shoutbox
$site_config['NEWSON'] = true;
$site_config['DONATEON'] = false;
$site_config['DISCLAIMERON'] = true;

//WAIT TIME VARS
$site_config['WAIT_CLASS'] = '1,2';		//Classes wait time applies to, comma seperated
$site_config['GIGSA'] = '1';			//Minimum gigs
$site_config['RATIOA'] = '0.50';		//Minimum ratio
$site_config['WAITA'] = '24';			//If neither are met, wait time in hours

$site_config['GIGSB'] = '3';			//Minimum gigs
$site_config['RATIOB'] = '0.65';		//Minimum ratio
$site_config['WAITB'] = '12';			//If neither are met, wait time in hours

$site_config['GIGSC'] = '5';			//Minimum gigs
$site_config['RATIOC'] = '0.80';		//Minimum ratio
$site_config['WAITC'] = '6';			//If neither are met, wait time in hours

$site_config['GIGSD'] = '7';			//Minimum gigs
$site_config['RATIOD'] = '0.95';		//Minimum ratio
$site_config['WAITD'] = '2';			//If neither are met, wait time in hours

//CLEANUP AND ANNOUNCE SETTINGS
$site_config['PEERLIMIT'] = '200';			//LIMIT NUMBER OF PEERS GIVEN IN EACH ANNOUNCE
$site_config['autoclean_interval'] = '600';		//Time between each auto cleanup (Seconds)
$site_config['LOGCLEAN'] = 28 * 86400;			// How often to delete old entries. (Default: 28 days)
$site_config['announce_interval'] = '1800';		//Announce Interval (Seconds)
$site_config['signup_timeout'] = '259200';		//Time a user stays as pending before being deleted(Seconds)
$site_config['maxsiteusers'] = '50';			//Maximum site members
$site_config['max_dead_torrent_time'] = '3600';//Time until torrents that are dead are set invisible(default: 3600 = 1 hour) (Seconds)
$site_config["cache_peers"] = true; // Cache peer lists
$site_config["cache_peers_time"] = 600; // Peer list cache time in seconds (Default: 10mins 600secs)
$site_config["cache_scrape"] = true; // Cache scrape result
$site_config["cache_scrape_time"] = 600; // Scrape cache time in seconds (Default: 10mins 600secs)
$site_config['invite_timeout'] = '604800'; # About (7 Days...)

//AUTO RATIO WARNING
$site_config["ratiowarn_enable"] = false; //Enable/Disable auto ratio warning
$site_config["ratiowarn_minratio"] = 0.4; //Min Ratio
$site_config["ratiowarn_mingigs"] = 4;  //Min GB Downloaded
$site_config["ratiowarn_daystowarn"] = 14; //Days to ban

// category = Category Image/Name, name = Torrent Name, dl = Download Link, uploader, comments = # of comments, completed = times completed, size, seeders, leechers, health = seeder/leecher ratio, external, wait = Wait Time (if enabled), rating = Torrent Rating, added = Date Added, nfo = link to nfo (if exists)
$site_config["torrenttable_columns"] = "category,name,dl,magnet,imdb,tube,size,seeders,leechers,health,added";
// size, speed, added = Date Added, tracker, completed = times completed comments,
$site_config["torrenttable_expand"] = "";

// Caching settings
$site_config["cache_type"] = "disk"; // disk = Save cache to disk, memcache = Use memcache, apc = Use APC
$site_config["cache_memcache_host"] = "localhost"; // Host memcache is running on
$site_config["cache_memcache_port"] = 11211; // Port memcache is running on
$site_config['cache_dir'] = getcwd().'/cache/diskcache'; // Cache dir (only used if type is "disk"). Must be CHMOD 777


// Mail settings
// php to use PHP's built-in mail function. or pear to use http://pear.php.net/Mail
// MUST use pear for SMTP
$site_config["mail_type"] = "phpmailer"; //Use PEAR for Gmail, otherwise, use PHP
$site_config["mail_smtp_host"] = ""; // SMTP server hostname. Google Gmail is smtp.gmail.com
$site_config["mail_smtp_port"] = "587"; // SMTP server port. Google Gmail uses 465
$site_config["mail_smtp_ssl"] = true; // true to use SSL. True for Google Gmail
$site_config["mail_smtp_auth"] = true; // true to use auth for SMTP. True for Google Gmail
$site_config["mail_smtp_user"] = ""; // SMTP username
$site_config["mail_smtp_pass"] = ""; // SMTP password
$site_config['mail_smtp_from'] = '';


// Password hashing - Once set, cannot be changed without all users needing to reset their passwords
$site_config["passhash_method"] = "sha1"; // Hashing method (sha1, md5 or hmac). Must use what your previous version of TT did or all users will need to reset their passwords
// Only used for hmac.
$site_config["passhash_algorithm"] = "sha1"; // See http://php.net/hash_algos for a list of supported algorithms.
$site_config["passhash_salt"] = "LyUvLjhldzAqXHYhKnouNUxpWmJmMiZgRHFwdTs1Vy1VXSJxdypRb3pKVGppPjRcPXc6L2VTJWEtK0hmJU85dg=="; // Shouldn't be blank. At least 20 characters of random text.

// class colors
$site_config['siteowner_color'] = '#00ffbf';  // Site Owner
$site_config['administrator_color'] = 'red';  // Administrator
$site_config['super_moderator_color'] = '#009AFF';   // Super Moderator
$site_config['moderator_color'] = 'pink';  // Moderator
$site_config['uploader_color'] = 'purple';  // Uploader
$site_config['vip_color'] = 'dimgray';  // VIP
$site_config['power_user_color'] = 'brown';  // Power User
$site_config['user_color'] = 'green';  // User

// Requests
$site_config["REQUESTSON"] = true; //activate request on site.
$site_config["REQ_SUB_IMAGE"] = false; //if you have "sub category with sub image" mod.
$site_config["REQ_CLASS_USER"] = true; // if you have "class user" mod.
$site_config["BBCODE_WITH_PREVIEW"] = false; // if you have "bbcode with preview everywhere, users may change color of icon" mod. 

//die("You didn't edit your config correctly, Please go look at your config file and edit it properly."); // You MUST remove or comment this line

	
?>
