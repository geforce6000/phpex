<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/14 0014
 * Time: 下午 01:14
 */

include "../simple_html_dom.php";
include '../forCrowler.php';

$url = 'https://www.javbus.pw/MIST-200';
getImageFromJav($url);
/*$file = fopen("..\\fileoperate\\urls.txt", "r") or exit("无法打开文件!");
// 读取文件每一行，直到文件结尾
while( !feof( $file ))	{
	$url = trim(fgets( $file ));
	getImageFromJav($url);
}
fclose( $file );*/

function getImageFromJav($url)
{
	set_time_limit(0);
	$page = getPage($url);
	$path = parse_url($url);
	$bigImage = $page->find('div.col-md-9 a',0);
	getImage($bigImage->href,'./dim/'.$path['path'].'/','bigimage.jpg');
	foreach ( $page->find('div#sample-waterfall a') as $item) {
		$file = getImage($item->href,'./dim/'.$path['path'].'/');
		print_r($file);
		echo '</br>';
		sleep(2);
	}
}


function showPic($filename,$path='') {
	echo '<img src="'.$path.'\\'.$filename.'">';
}


