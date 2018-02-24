<?php
/**
 * Created by PhpStorm.
 * User: geforce
 * Date: 2018/2/24
 * Time: 16:41
 */

    //获取单页文件的测试文件，考虑之后做成一个函数
    //参数：单页面链接
    //功能：根据传入的页面链接，获取文章相关信息保存到数据库中，同时下载正文中和图片和附件文件
    //返回：无返回

    include "simple_html_dom.php";  //导入爬虫函数库
    include "getRemoteImage.php";   //导入下载图片和文件的函数

    function __autoload( $classname ) {
        include strtolower( $classname ).".class.php";
    }

    //获取单页文章信息
    $articlehtml = file_get_html('http://www.jjkjzz.com:8080/article.php?cate=1&cid=15&aid=1090');
    foreach ($articlehtml->find('div.left-content') as $element) {
        echo $element->children(0)->plaintext."</br>";  //获取标题
        echo $element->children(1)->plaintext."</br>";  //获取编辑姓名
        echo $element->children(2)->plaintext."</br>";  //获取发布时间
        echo $element->children(3)->plaintext."</br>";  //获取浏览量
    }
    //获取文章中的图片，并使用 getImage 函数保存到本地对应文件夹
    foreach ($articlehtml->find('div.main-content img') as $element) {
        $path = explode("/",$element->src);
        if ($path[1] == 'files') {
            getImage("http://www.jjkjzz.com:8080/".$element->src, "../".$path[1]."/".$path[2]."/".$path[3], $path[4]);
        }
    }
    //获取文件中附件的文件，并使用 httpcopy 函数保存到本地对应文件夹
    foreach ($articlehtml->find('div.main-content a') as $element) {
        $path = explode("/",$element->href);
        httpcopy("http://www.jjkjzz.com:8080/".$element->href, "../".$path[1]."/".$path[2]."/".$path[3]);
    }
    foreach ($articlehtml->find('div.main-content') as $element) {
        echo $element->innertext;                       //获取正文（包含其中的 img 和 a 标签）
    }

?>

