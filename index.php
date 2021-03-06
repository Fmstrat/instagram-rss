<?php

use rdx\http\HTTP;

require "config.php";

if ($_GET['code'] != $code)
	exit;

header('Content-type: text/plain; charset=utf-8');

require 'vendor/autoload.php';
require 'inc.functions.php';

$username = (string) @$_GET['user'] ?: 'instagram';

// 1. Overview
$url = 'https://www.instagram.com/' . urlencode($username) . '/';
$request = HTTP::create($url);
$response = $request->request();

// 2. Extract JSON
if ( !preg_match('#>\s*window._sharedData\s*=\s*(\{.+?)</script>#', $response->body, $match) ) {
	exit("Can't extract any JSON. Wrong URL? $url");
}

$json = trim($match[1], ' ;');
$data = json_decode($json, true);
// print_r($data);
if ( !isset($data['entry_data']['ProfilePage'][0]['graphql']['user']) ) {
	exit("Can't extract profile JSON. Invalid profile?");
}

$profile = $data['entry_data']['ProfilePage'][0]['graphql']['user'];
// print_r($profile);
if ( !isset($profile['edge_owner_to_timeline_media']['edges']) ) {
	exit("Can't extract media JSON. Private profile?");
}

$media = $profile['edge_owner_to_timeline_media']['edges'];
// print_r($media);

// 3. Print RSS
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<rss version="2.0">
	<channel>
		<title>+ @<?= html($username) ?></title>
		<link>https://www.instagram.com/<?= html($username) ?>/</link>
		<description>@<?= html($username) ?></description>
		<? foreach ($media as $node):
			$title = trim(@$node['node']['edge_media_to_caption']['edges'][0]['node']['text']);
			$typename = $node['node']['__typename'];
			$posttitle = $username;
			if ($typename == "GraphVideo")
				$posttitle = "VIDEO - ".$posttitle;
			if ($typename == "GraphSidecar")
				$posttitle = "MULTI - ".$posttitle;
			$postUrl = "https://www.instagram.com/p/" . $node['node']['shortcode'];
			$utc = $node['node']['taken_at_timestamp'];
			$link = $node['node']['display_url'];
			$thumb = $node['node']['thumbnail_src'];
			?>
			<item>
				<title><?= html($posttitle) ?></title>
				<image>
					<url><?= html($thumb) ?></url>
					<link><?= html($link) ?></link>
					<title><?= html($title) ?></title>
				</image>
				<link><?= html($postUrl) ?></link>
				<guid isPermaLink="true"><?= html($postUrl) ?>/</guid>
				<?php if ($typename == "GraphVideo") { ?>
				<description><![CDATA[<a href='<?= html($link) ?>'><img src='https://nowsci.com/instagram-rss/image.php?text=VIDEO&code=afdFjieaafef39aavm39wEfrkf3agra32g0GDF&url=<?= html($link) ?>'></a><br><?= html($title) ?>]]></description>
				<?php } elseif ($typename == "GraphSidecar") { ?>
				<description><![CDATA[<a href='<?= html($link) ?>'><img src='https://nowsci.com/instagram-rss/image.php?text=MUTLI&code=afdFjieaafef39aavm39wEfrkf3agra32g0GDF&url=<?= html($link) ?>'></a><br><?= html($title) ?>]]></description>
				<?php } else { ?>
				<description><![CDATA[<a href='<?= html($link) ?>'><img src='<?= html($link) ?>'></a><br><?= html($title) ?>]]></description>
				<?php } ?>
				<pubDate><?= date('r', $utc) ?></pubDate>
				<author><?= html($username) ?>@instagram.com</author>
			</item>
		<? endforeach ?>
	</channel>
</rss>
