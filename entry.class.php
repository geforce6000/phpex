<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/5 0005
 * Time: 下午 03:22
 * 获取首页上所有入口
 */
class Entry extends Schcrowler {
	protected $skip;
	function __construct($url, $tableName, $skip)
	{
		parent::__construct($url, $tableName);
		$this->skip = $skip;
	}

	function getAllItems ( $data = '' ) {
		$html = file_get_html( $this->url );
		foreach ( $html->find('ul#jsddm li a') as $item ) {
			$root = parse_url( $item->href );
			if ( in_array ( $root['path'], $this->skip ) ) continue;
			if ( count ( $root ) == 1 ) $items[]= array ($this->tableName=>$this->url.$root['path']);
		}
		foreach ( $html->find('div.pic_link a') as $item ) {
			if ( $item->href <> '' ) $items[] = array ($this->tableName=>$this->url.$item->href);
		}
		$this->items = $items;
		return $items;
	}
}
