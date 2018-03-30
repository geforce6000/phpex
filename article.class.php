<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/7 0007
 * Time: 下午 02:27
 */
class Article extends Schcrowler {
	protected $existLinks;
	function __construct($url, $tableName, $existLinks)
	{
		parent::__construct($url, $tableName);
		foreach ($existLinks as $item) {
			$this->existLinks[] = $item['articlehref'];
		}
	}

	function getAllItems ( $data ) {
		foreach ( $data as $link ) {
			$articleLink = $link[ key ( $link ) ];
			$page = file_get_html( $articleLink );
			foreach ( $page->find ( 'div.list-show h2 a' ) as $element ) {
				if (in_array( $this->url.$element->href, $this->existLinks)) continue;
				$articleUrls[] = array ( $this->tableName=>$this->url.$element->href );
				echo $this->url.$element->href;
			}
		}
		$this->items = $articleUrls;
		return $articleUrls;
	}

	function saveArticleData ( $db ) {
		foreach ( $this->items as $item ) {
			$articles[] = $this->getOneArticle( $item[$this->tableName] );
		}
		$db->from('articledata')->insert($articles);
	}

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
}