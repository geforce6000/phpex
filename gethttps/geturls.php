<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15 0015
 * Time: 下午 01:49
 */
	include "../simple_html_dom.php";
	include "../mypdo.class.php";
	include "../forCrowler.php";

	$dbinfo = array (
		'dbms'    =>'mysql',          //连接类型
		'host'    =>'localhost',      //服务器地址
		'user'    =>'root',           //用户名
		'dbname'  =>'javbus',         //数据库名
		'password'=>'Radeon7500',     //密码
		'charset' =>'utf8',           //字符集
		'port'    =>3306              //端口
	);
	$mydb = new Mypdo($dbinfo);
	$mydb->from('entry')->drop();
	$mydb->from('entry')->create(array('entry varchar(256)'));

	$urls = $mydb->from('pages')->get();
	set_time_limit(0);
	$i = 0;
	foreach ( $urls as $url) {
		$i++;
		if ($i == 3) die;
		$page = getPage($url['page']);
		foreach ($page->find('a.movie-box') as $item ) {
			$entries[] = array('entry'=>$item->href);
		}
		$mydb->from('entry')->insert($entries);
		unset($entries);
		$page->clear();
		sleep(2);
	}

	$url = $urls[0]['page'];

