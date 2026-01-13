<?php

/*
	@author: NIKET MALIK <niketmalik@gmail.com>
			 https://google.com/+NiketMalik
	@author(torrent trader): tornav
	@name: Advance Feed Generator
	@TT version: 2.08
	@TT theme: default
	@Mod version: 1.0.0
	@lisence: released under the MIT license
*/

require_once("backend/functions.php");

// <-- @config
$description = ($GLOBALS['site_config']['SITEDESCRIPTION']) ? $GLOBALS['site_config']['SITEDESCRIPTION'] : "";
$language = 'en-US';
// @config -->

$host = $GLOBALS['site_config']['SITEURL'];

function _FeedGen($host) {

	dbconn();

	$feed = '<?xml version="1.0" encoding="UTF-8"?>
<rss 
	version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
	<channel>
		<title>' . $GLOBALS['site_config']['SITENAME'] . '</title>
		<atom:link href="' . $host . '/feed.xml" rel="self" type="application/rss+xml" />
		<link>' . $GLOBALS['site_config']['SITEURL'] . '</link>
		<description>' . $description . '</description>
		<lastBuildDate> ' . date(DATE_RSS) . '</lastBuildDate>
		<language>' . $language . '</language>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
		<generator>Advance Feed Generator MOD by tornav</generator>';

	$query = SQL_Query_EXEC("SELECT 
								`torrents`.`id`,
								`torrents`.`save_as`,
								`torrents`.`added`,
								`torrents`.`info_hash`,
								`torrents`.`seeders`,
								`torrents`.`leechers`,
								`torrents`.`descr`,
								`torrents`.`size`,
							    `torrents`.`anon`,
							    `users`.`username`,
							    `users`.`privacy`,
							    `categories`.`parent_cat` as `cat1`,
							    `categories`.`name` as `cat2`
						    FROM `torrents`
						    INNER JOIN `categories` ON `torrents`.`category` = `categories`.`sort_index`
						    INNER JOIN `users` ON `torrents`.`owner` = `categories`.`id`
						    WHERE `torrents`.`visible` = 'yes'
						    ORDER BY `torrents`.`added` DESC
							");
				
	while($row = mysqli_fetch_assoc($query)) {
		$author = ($row['privacy']!=='strong' && $row['anon']==='no') ? $row['username'] : 'Anonymous';
		$feed .= '<item>
		<title><![CDATA[ ' . utf8_encode($row["save_as"]) . ' ]]></title>
		<link>' . $host .'/torrents-details.php?id=' . $row["id"] . '</link>
		<pubDate>' . date(DATE_RSS, strtotime($row["added"])) . '</pubDate>
		<hash>' . $row['info_hash'] . '</hash>
		<author>' . $author . '</author>
		<seeders>' . $row['seeders'] . '</seeders>
		<leechers>' . $row['leechers'] . '</leechers>
		<category>' . $row['cat1'] . ' - ' . $row['cat2'] . '</category>
		<description><![CDATA[ ' . utf8_encode($row["descr"]) . ' ]]></description>
		<enclosure url="' . $host .'/download.php?id=' . $row["id"] . '" length="' . $row["size"] . '" type="application/x-bittorrent" />
	</item>' . PHP_EOL . '	';
	}

	return $feed . PHP_EOL . '	</channel>' . PHP_EOL . '</rss>';
}

$feed = _FeedGen($host);

// @output
header('Content-Type: rss+xml; charset=utf-8');
echo $feed;

?>