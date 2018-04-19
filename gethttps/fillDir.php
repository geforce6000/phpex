<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/19 0019
 * Time: 上午 09:05
 */
	include "../simple_html_dom.php";
	include '../forCrowler.php';

	$dir = "D:\\test\\";

	// Sort in ascending order - this is default
	$a = scandir($dir);

	$url = 'https://www.javbus.pw/';

	for($i=2; $i<count($a); $i++) {
		getImageFromJav($url.$a[$i], $dir.$a[$i]);
	}

	function getImageFromJav($url, $dir)
	{
		set_time_limit(0);
		$page = getPage($url);
		$path = parse_url($url);
		$bigImage = $page->find('div.col-md-9 a',0);
		getImage($bigImage->href,$dir,'bigimage.jpg');
		foreach ( $page->find('div#sample-waterfall a') as $item) {
			$file = getImage($item->href,$dir);
			//print_r($file);
			//echo '</br>';
			sleep(2);
		}
	}