<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/21 0021
 * Time: 下午 02:23
 */
/**
 * 接收一个 url 地址，获取该地址的页面数据并以 simple_html_dom 对象的形式返回
 * @param $url  string  欲获取的 URL 地址
 * @return bool|simple_html_dom 对象
 */
function getPage( $url ) {
	$path = parse_url( $url );
	if ( $path['scheme'] == 'http' ) {
		$page = file_get_html( $url );
	} elseif ( $path['scheme'] == 'https' ) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);// 这个是主要参数
		$data = curl_exec($curl);
		$page = new simple_html_dom($data);
		curl_close($curl);
	}
	return $page;
}

/**
 * 根据传入的图片 url 地址，获取该图片并保存在指定的文件夹中
 * @param $url        string  图片 url
 * @param $save_dir   string  保存图片的文件夹，未指定则为当前文件夹
 * @param $filename   string  文件名，省略则为当前的文件名
 * @param $type       int     还不明白怎么用
 * @return array      返回值是一个数组，包含保存文件名、文件夹和错误码
 */
function getImage( $url, $save_dir='' ,$filename='' ,$type=1 ) {
	$path = parse_url( $url );
	$route = explode('/', $path['path']);
	if ( trim ( $url ) == '' ) {
		return array( 'file_name'=>'','save_path'=>'','error'=>1 );
	}
	if ( trim ( $save_dir )== '' ) {
		$save_dir='./';
	}
	if (trim ( $filename ) == '' ) {
		//保存文件名
		$ext = strrchr($url,'.');
		if ( $ext != '.gif' && $ext != '.jpg' ) {
			return array( 'file_name'=>'','save_path'=>'','error'=>3 );
		}
		$filename = $route[ count ( $route ) - 1 ];
	}
	if ( 0 !== strrpos ( $save_dir, '/') ){
		$save_dir .= '/';
	}
	//创建保存目录
	if ( !file_exists ( $save_dir ) && !mkdir( $save_dir,0777,true ) ) {
		return array( 'file_name'=>'', 'save_path'=>'', 'error'=>5 );
	}
	//获取远程文件所采用的方法
	if ( $type ){
		$ch = curl_init();
		$timeout = 60;
		curl_setopt( $ch,CURLOPT_URL,$url );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER,1 );
		curl_setopt( $ch,CURLOPT_CONNECTTIMEOUT,$timeout );
		if ( $path['scheme'] == 'https' ) {
			curl_setopt( $ch,CURLOPT_SSL_VERIFYHOST,false );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER,false );
		}
		$img = curl_exec( $ch );
		curl_close( $ch );
	} else {
		ob_start();
		readfile( $url );
		$img = ob_get_contents();
		ob_end_clean();
	}
	//文件大小
	$fp2=@fopen($save_dir.$filename,'a' );
	fwrite( $fp2, $img );
	fclose( $fp2 );
	unset( $img, $url );
	return array( 'file_name'=>$filename, 'save_path'=>$save_dir.$filename, 'error'=>0 );
}

/*
 * 获取远程服务器的文件到本地
 * 参数
 * $url         string      要下载文件的url
 * $dir         string      本地保存文件的路径
 * $file        string      可选，保存的文件名，省略即用远程文件的原名
 * $timeout     int         等待时间
 * 使用：$result = httpcopy('http://www.phpernote.com/image/logo.gif', "/files/file");
 * 返回：array ( 'filename', 'way', 'size' )
 */
function httpcopy( $url, $dir, $file='', $timeout=60 ) {
	$file = empty( $file ) ? pathinfo( $url,PATHINFO_BASENAME ) : $file;
	!is_dir( $dir ) && @mkdir( $dir, 0755, true );
	$url = str_replace(' ', "%20", $url);
	$result = array( 'fileName'=>'', 'way'=>'', 'size'=>0, 'spendTime'=>0 );
	$startTime = explode(' ', microtime());
	$startTime = (float)$startTime[0] + (float)$startTime[1];
	if ( function_exists ('curl_init' ) ) {
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL,$url );
		curl_setopt( $ch,CURLOPT_TIMEOUT,$timeout );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER,TRUE );
		$temp = curl_exec( $ch );
		if ( @file_put_contents( $dir."/".$file,$temp ) && !curl_error( $ch ) ) {
			$result['fileName'] = $file;
			$result['way']      = 'curl';
			$result['size']     = sprintf( '%.3f',strlen( $temp ) / 1024);
		}
	} else {
		$opts = array(
			'http'    =>array(
			'method'  =>'GET',
			'header'  =>'',
			'timeout' =>$timeout
			)
		);
		$context = stream_context_create( $opts );
		if ( @copy( $url, $file, $context ) ) {
			$result['fileName'] = $file;
			$result['way']      = 'copy';
			$result['size']     = sprintf('%.3f',strlen($context)/1024);
		}
	}
	$endTime = explode(' ', microtime());
	$endTime = (float)$endTime[0] + (float)$endTime[1];
	$result['spendTime'] = round($endTime-$startTime ) * 1000;//单位：毫秒
	return $result;
}

/**
 * Unicode 解码
 * @param $str
 * @return mixed
 */
function decodeUnicode( $str ) {
	return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
		create_function(
			'$matches',
			'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
		),
		$str);
}