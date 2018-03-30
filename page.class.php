<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/6 0006
 * Time: 下午 03:30
 */
class Page extends Schcrowler {
	function getAllItems ( $data ) {
		foreach ( $data as $element) {
			$listUrl = $element[key ( $element ) ];
			$listPage = file_get_html ( $listUrl );
			foreach ( $listPage->find('div.paginator' ) as $pagi ) {
				if ( count ( $pagi->children() ) == 0 ) {
					$pages[] = 1;
				} else {
					foreach ( $listPage->find('td.page_last' ) as $item) {
						$pages[] = $item->children(0)->getAttribute('data-page');
					}
				}
			}
		}
		for ( $i = 0; $i < count($data); $i++ ) {
			for ( $j = 1; $j <= $pages[$i]; $j++ ) {
				$pageUrl[] = array ( $this->tableName=>$data[$i][key($data[$i])]."?pg=".$j );
			}
		}
		$this->items = $pageUrl;
		return $pageUrl;
	}
}
