<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/6 0006
 * Time: 下午 04:18
 * 学校官网爬虫类，基础功能主要是初始化、简单字符串化、基础数据存储
 */
abstract class Schcrowler {
	protected $url;       //string  网站入口
	protected $items;     //array   抓取到的数据项
	protected $tableName; //string  保存抓取项数据表名

	function __construct( $url, $tableName ) {
		$this->url = $url;
		$this->tableName = $tableName;
	}

	function __toString() {
		foreach ( $this->items as $item ) {
			print_r( $item );
			echo '</br>';
		}
		return '';
	}

	function getTableName() {
		return $this->tableName;
	}

	abstract function getAllItems( $data );

	function saveToForm ( $db ) {
		$db->from( $this->tableName )->drop();
		$db->from( $this->tableName )->create( array ( $this->tableName.' varchar(1024)' ) );
		$db->from( $this->tableName )->insert( $this->items );
	}
}