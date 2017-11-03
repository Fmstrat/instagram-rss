<?php

use rdx\http\HTTP;

if ($_GET['code'] != "afdFjieaafef39aavm39wEfrkf3agra32g0GDF")
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
if ( !isset($data['entry_data']['ProfilePage'][0]['user']) ) {
	exit("Can't extract profile JSON. Invalid profile?");
}

$profile = $data['entry_data']['ProfilePage'][0]['user'];
if ( !isset($profile['media']['nodes']) ) {
	exit("Can't extract media JSON. Private profile?");
}

$media = $profile['media']['nodes'];

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
			$link = $node['display_src'];
			$fulllink = "https://www.instagram.com/p/" . $node['code'];
			$thumb = $node['thumbnail_src'];
			$title = trim(trim(@$node['caption']));
			$typename = $node['__typename'];
			$posttitle = $username;
			if ($typename == "GraphVideo")
				$posttitle = "VIDEO - ".$posttitle;
			if ($typename == "GraphSidecar")
				$posttitle = "MULTI - ".$posttitle;
			?>
			<item>
				<title><?= html($posttitle) ?></title>
				<image>
					<url><?= html($thumb) ?></url>
					<link><?= html($link) ?></link>
					<title><?= html($title) ?></title>
				</image>
				<link><?= html($fulllink) ?></link>
				<guid isPermaLink="true">https://www.instagram.com/p/<?= html($node['code']) ?>/</guid>
				<?php if ($typename == "GraphVideo") { ?>
				<description><![CDATA[<a href='<?= html($link) ?>'><img src='https://nowsci.com/instagram-rss/video.png'><br><br><br><img src='<?= html($link) ?>'></a><br><?= html($title) ?>]]></description>
				<?php } elseif ($typename == "GraphSidecar") { ?>
				<description><![CDATA[<a href='<?= html($link) ?>'><img src='https://nowsci.com/instagram-rss/video.png'><br><br><br><img src='<?= html($link) ?>'></a><br><?= html($title) ?>]]></description>
				<?php } else { ?>
				<description><![CDATA[<a href='<?= html($link) ?>'><img src='<?= html($link) ?>'></a><br><?= html($title) ?>]]></description>
				<?php } ?>
				<pubDate><?= date('r', $node['date']) ?></pubDate>
				<author><?= html($username) ?>@instagram.com</author>
			</item>
		<? endforeach ?>
	</channel>
</rss>
