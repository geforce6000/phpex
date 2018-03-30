<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 上午 10:59
 * 九江科技中专网页爬虫，用于爬取所有文章链接并保存到数据库中保存
 * 仅对 http://www.jjkjzz.com:8080/ 有效
 */
	//导入爬虫函数库
	include "simple_html_dom.php";
	include "getRemoteImage.php";

	/**
	 * 自动载入类定义文件
	 * @param $classname string 类名称
	 */
	function  __autoload ( $classname ) {
		require_once strtolower( $classname ).'.class.php';
	}

	//数据库连接初始化信息
	$dbinfo = array (
		'dbms'    =>'mysql',          //连接类型
		'host'    =>'localhost',      //服务器地址
		'user'    =>'jjkjzz',         //用户名
		'dbname'  =>'crawler',        //数据库名
		'password'=>'jjkjzz',         //密码
		'charset' =>'utf8',           //字符集
		'port'    =>3306              //端口
	);

	//初始化数据库操作类对象
	$mydb = new Mypdo( $dbinfo );

	//域名
	$url = 'http://www.jjkjzz.com:8080/';

	$skip = array( 'index.php','signup.php' );

	$entry = new Entry( $url, 'temproot', $skip );

	$entry->getAllItems();

	$entry->saveToForm( $mydb );

	$pages = new Page ( $url, 'temppage' );

	$pages->getAllItems( $mydb->from($entry->getTableName())->get() );

	$pages->saveToForm( $mydb );

	$existLinks = $mydb->from('articlehref')->get();

	$article = new Article( $url, 'temparticle', $existLinks);

	$article->getAllItems( $mydb->from($pages->getTableName())->limit(1)->get());

	$article->saveToForm( $mydb );

	//echo $article;

	//$articleData = $article->getArticleData();

	/*$mydb->from('tempdata')->create( array (
		'id int(8)',
		'classid int(8)',
		'pics varchar(1024)',
		'appendfile varchar(1024)',
		'bannerpic varchar(256)',
		'onlist int(1)',
		'title varchar(1024)',
		'author varchar(256)',
		'posttime datetime',
		'hits int(8)',
		'content mediumtext'
	));*/

	//$mydb->from('tempdata')->insert($articleData);

	/**
	 * 获得所有页面的连接，保存在 $pages 数组中
	 * @param $page array 每个元素是一个 array ，其中 path 键保存着入口文件名称
	 * @param $url string 网站根链接
	 * @return array 无键值数组，每个值是每个内页的链接
	 */
	function getAllPages ( $page, $url ) {
		foreach ( $page as $element ) {
			$listUrl = $url . $element['path'];
			$listPage = file_get_html ( $listUrl );
			foreach ( $listPage->find('div.paginator' ) as $pagi ) {
				if ( count( $pagi->children() ) == 0 ) {
					$pages[] = 1;
				} else {
					foreach ( $listPage->find('td.page_last' ) as $item) {
						$pages[] = $item->children(0)->getAttribute('data-page');
					}
				}
			}
		}
		for ( $i = 0; $i < 6; $i++ ) {
			for ( $j = 1; $j <= $pages[$i]; $j++ ) {
				$pageUrl[] = $url.$page[$i]['path']."?pg=".$j;
				echo $url.$page[$i]['path']."?pg=".$j.'</br>';
			}
		}
		return $pageUrl;
	}

	/**
	 * 根据传入的页面链接获取该页面所有文章的链接并以数组形式返回
	 * @param $link string 一个页面的入口链接
	 * @return array 无键值数组，包含该页面所有文章的入口链接
	 */
	function getArticleUrl ( $link, $url ) {
		$page = file_get_html( $link );
		foreach ( $page->find ( 'div.list-show h2 a' ) as $element ) {
			$e = $element->first_child();
			$articleUrls[] = $url.$element->href;
		}
		return $articleUrls;
	}

	/**
	 * 对每条链接获取文章数据并存入articledata数据表
	 * @param $pageUrls string 一个单页文章的入口链接
	 */
	function getArticles ( $pageUrls ) {
		foreach ($pageUrls as $item) {
			$articleData = getOneArticle( $item['articlehref'] );
			echo $i.': '.$item['articlehref'].'</br>';
			$i++;
			//$mydb->from( 'articledata' )->insert( $articleData );
			//每获取一篇文章，等待一秒，避免服务器压力过大
			//sleep(1);
		}
	}





