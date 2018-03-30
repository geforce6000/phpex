
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
<?php
/**
* Created by PhpStorm.
* User: Administrator
* Date: 2018/3/14 0014
* Time: 上午 10:36
*/
//导入爬虫函数库
include "../simple_html_dom.php";
include "../forCrowler.php";

$url = 'http://www.afwing.com/encyclopaedia/hms-queen-elizabeth.html';

$page = file_get_html($url);

$item = $page->find('a.getContent',  0);

$articleHref = 'http://d.afwing.com/e/zz/contentVal.php?id='.$item->aid;

$article = file_get_html($articleHref);

$article = decodeUnicode($article);

$article = stripcslashes($article);

$article = ltrim ($article, '﻿("');

$article = rtrim ($article, '")');

echo $article;

?>
</body>
</html>
