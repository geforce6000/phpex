<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15 0015
 * Time: 上午 11:05
 */

	include "..\simple_html_dom.php";
	function  __autoload ( $classname ) {
		require_once "..\\".strtolower( $classname ).'.class.php';
	}

	$host = 'http://www.afwing.com/';

	$dbinfo = array (
		'dbms'    =>'mysql',          //连接类型
		'host'    =>'localhost',      //服务器地址
		'user'    =>'root',           //用户名
		'dbname'  =>'afwing',         //数据库名
		'password'=>'Radeon7500',     //密码
		'charset' =>'utf8',           //字符集
		'port'    =>3306              //端口
	);

	$mydb = new Mypdo($dbinfo);

	$pageList[] = array('page'=>'http://www.afwing.com/list/index.html');

	for ( $i=2; $i<=184; $i++ ) {
		$pageList[] = array('page'=>'http://www.afwing.com/list/index_'.$i.'.html');
	}

	$urllist = file_get_html($pageList[0]['page']);

	foreach ( $urllist->find('div.content3_txt') as $item) {
		$articleList[] = array (
			'href'=>$item->children(1)->href
		);
	}
	$mydb->from('articlelist')->create(array('href varchar(1024)'));
	$mydb->from('articlelist')->insert($articleList);