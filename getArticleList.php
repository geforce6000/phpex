<?php
/**
 * Created by PhpStorm.
 * User: geforce
 * Date: 2018/2/25
 * Time: 13:51
 */

    include "simple_html_dom.php";  //导入爬虫函数库
    include "getArticleUrl.php";
    include "getOneArticle.php";

    function __autoload( $classname ) {
        include strtolower( $classname ).".class.php";
    }

		$dbinfo = array (
			'dbms'=>'mysql',        //连接类型
			'host'=>'localhost',        //服务器地址
			'user'=>'jjkjzz',        //用户名
			'dbname'=>'crawler',
			'password'=>'jjkjzz',    //密码
			'charset'=>'utf8',
			'port'=>3306        //端口
		);

		$mydb = new Mypdo($dbinfo);

    //获得所有入口文件名称，保存在 $e 数组中
		//$e array 每个值是一个数组，保存6个入口文件名称
    $e = $mydb->from( 'articleclass' )
              ->select( 'path' )
              ->where( 'parentid', 0 )
              ->get();

    //域名
    $url = "http://www.jjkjzz.com:8080/";

    //获得每个入口页面数量，保存在 $pages 数组中
		//遍历$e，
    foreach ( $e as $element ) {
        $listUrl = $url . $element['path'];
        $listPage = file_get_html ( $listUrl );
        foreach ( $listPage->find('div.paginator') as $pagi ) {
            if ( count( $pagi->children() ) == 0 ) {
                $pages[] = 1;
            } else {
                foreach ($listPage->find('td.page_last') as $item) {
                    $pages[] = $item->children(0)->getAttribute('data-page');
                }
            }
        }
    }
    //获得全部页面的链接，保存在 $pageUrl 数组中
    for ($i = 0; $i < 6; $i++) {
        for ($j = 1; $j <= $pages[$i]; $j++) {
            $pageUrl[] = $url.$e[$i]['path']."?pg=".$j;
            echo $url.$e[$i]['path']."?pg=".$j.'</br>';
        }
    }


    /*echo $pageUrl[0]."</br>";

    $articleUrls = getArticleUrl ( $pageUrl[0] );

    $articleData = getOneArticle( $articleUrls[3] );

    $mydb->from( 'articledata' )->insert( $articleData );

    $a = $mydb->from('articledata')->get();

    print_r($a);*/




