<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15 0015
 * Time: 下午 01:34
 */
	include "../simple_html_dom.php";
	include "../mypdo.class.php";

	$pages[] = array('page'=>'https://www.javbus.pw/');
	for ($i=2; $i<=91; $i++) {
		$pages[] = array('page'=>'https://www.javbus.pw/page/'.$i);
	}
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
	$mydb->from('pages')->create(array('page varchar(1024)'));
	$mydb->from('pages')->insert($pages);
