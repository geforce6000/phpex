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

    $info = array (
        'serverName'=>'localhost',
        'userName'=>'jjkjzz',
        'password'=>'jjkjzz',
        'dbname'=>'jjkjzz',
        'port'=>'3306');

    $mydb = new Mydb($info);

    //获得所有入口文件名称，保存在 $e 数组中
    $e = $mydb->from( 'articleclassRoot' )
              ->select( 'path' )
              ->where( 'parentid', 0 )
              ->where( 'classid >', 0 )
              ->where( 'classid <', 999 )
              ->get();

    //域名
    $url = "http://www.jjkjzz.com:8080/";

    //获得每个入口页面数量，保存在 $pages 数组中
    foreach ( $e as $element ) {
        $listUrl = $url . $element['path'];
        $listPage = file_get_html ( $listUrl );
        foreach ( $listPage->find('div.paginator') as $pagi ) {
            if ( count( $pagi->children() ) == 0 ) {
                //echo $element['path'] . ": no pagi</br>";
                $pages[] = 0;
            } else {
                //echo $element['path'] . ": has pagi</br>";
                foreach ($listPage->find('td.page_last') as $item) {
                    //echo "-->".$item->getAttribute('class');
                    //echo " pages: ".$item->children(0)->getAttribute('data-page')."</br>";
                    $pages[] = $item->children(0)->getAttribute('data-page');
                }
            }

        }
    }
    //获得全部页面的链接，保存在 $pageUrl 数组中
    for ($i = 0; $i < 6; $i++) {
        for ($j = 1; $j <= $pages[$i]; $j++) {
            $pageUrl[] = $url.$e[$i]['path']."?pg=".$j;
        }
    }

    echo $pageUrl[0]."</br>";

    $articleUrls = getArticleUrl ( $pageUrl[0] );

    $articleData = getOneArticle( $articleUrls[3] );

    $mydb->from( 'articledata' )->insert( $articleData );

    $a = $mydb->from('articledata')->get();

    print_r($a);




