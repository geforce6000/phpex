<?php
/**
 * Created by PhpStorm.
 * User: geforce
 * Date: 2018/2/24
 * Time: 16:41
 */

    //获取单页文件的信息并保存
    //$articleUrl   string  单页面 url 地址
    //功能：根据传入的页面链接，获取文章相关信息保存到数据库中，同时下载正文中和图片和附件文件
    //返回：无返回

    include "getRemoteImage.php";   //导入下载图片和文件的函数

    //获取单页文章信息
    function getOneArticle ( $articleUrl ) {
        $href = parse_url ( $articleUrl );
        parse_str ( $href['query'], $query );
        $articleData = array (
            'id'        =>  $query['aid'],
            'classid'   =>  $query['cid'],
            'pics'      =>  "",
            'appendfile'=>  "",
            'bannerpic' =>  "",
            'onlist'    =>  1
        );
        $articleHtml = file_get_html ( $articleUrl );
        foreach ( $articleHtml->find('div.left-content') as $element ) {
            //标题
            $articleData['title']       = trim ( $element->children(0)->plaintext );
            //编辑姓名
            $articleData['author']      = trim ( substr ( strrchr ( $element->children(1)->plaintext, "：" ), 3 ) );
            //发布时间
            $articleData['posttime']    = trim ( substr ( strrchr ( $element->children(2)->plaintext, "：" ), 3 ) ) ;
            //浏览量
            $articleData['hits']        = trim ( substr ( strrchr ( $element->children(3)->plaintext, ": " ), 2 ) );
        }
        //获取文章中的图片，并使用 getImage 函数保存到本地对应文件夹
        foreach ($articleHtml->find('div.main-content img') as $element) {
            $path = explode("/",$element->src);
            if ($path[1] == 'files') {
                if ( $articleData['pics'] <> "" ) { $articleData['pics'] .= "|"; }
                $articleData['pics'] .= $element->src;
                getImage("http://www.jjkjzz.com:8080/".$element->src,
                    "../".$path[1]."/".$path[2]."/".$path[3], $path[4]);
            }
        }
        //获取文件中附件的文件，并使用 httpcopy 函数保存到本地对应文件夹
        foreach ( $articleHtml->find('div.main-content a' ) as $element) {
            if ( $articleData['appendfile'] <> "" ) { $articleData['appendfile'] .= "|"; }
            $articleData['appendfile'] .= $element->href."|";
            $path = explode( "/",$element->href );
            httpcopy ( "http://www.jjkjzz.com:8080/".$element->href, "../".$path[1]."/".$path[2]."/".$path[3] );
        }
        foreach ($articleHtml->find('div.main-content') as $element) {
            //正文（包含其中的 img 和 a 标签）
            $articleData['content'] = htmlentities ( trim ( $element->innertext ) );
        }
        return $articleData;
    }

?>

