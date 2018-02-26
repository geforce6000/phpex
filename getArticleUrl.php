<?php
/**
 * Created by PhpStorm.
 * User: geforce
 * Date: 2018/2/25
 * Time: 20:09
 */
    //根据传入的页面链接，获取该页全部文章链接并以数组的形式返回
    function getArticleUrl ( $link ) {
        global $url;
        $page = file_get_html( $link );
        foreach ( $page->find ( 'div.list-show h2 a' ) as $element ) {
            //echo 'url: '.$url.$element->href.'</br>';
            $e = $element->first_child();
            //echo $e->plaintext.'</br>';
            $articleUrls[] = $url.$element->href;
        }
        return $articleUrls;
    }
