<?php
	require 'config.php';

	if ($_GET['code'] != $code)
		exit;

	//ini_set('display_errors', 1);
	//ini_set('display_startup_errors', 1);
	//error_reporting(E_ALL);

	function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
		for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
			for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
				$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
		return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
	}

	//Set the Content Type
	header('Content-type: image/jpeg');

	// Create Image From Existing File
	$header=array(
		'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language: en-us,en;q=0.5',
		'Accept-Encoding: gzip,deflate',
		'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
		'Keep-Alive: 115',
		'Connection: keep-alive',
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $_GET["url"]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	$jpg_image = imagecreatefromstring($data);
	$w = imagesx($jpg_image);
	$h = imagesy($jpg_image);

	// Allocate A Color For The Text
	$white = imagecolorallocate($jpg_image, 255, 255, 255);
	$black = imagecolorallocate($jpg_image, 0, 0, 0);

	// Set Path to Font File
	$font_path = 'open-sans';

	// Set Text to Be Printed On Image
	$text = $_GET["text"];

	// Print Text On Image
	//imagettftext($jpg_image, 25, 0, 75, 300, $white, $font_path, $text);
	imagettfstroketext($jpg_image, 50, 0, round($w/2)-100, round($h/2), $white, $black, $font_path, $text, 3);

	// Send Image to Browser
	imagejpeg($jpg_image);

	// Clear Memory
	imagedestroy($jpg_image);
?>
