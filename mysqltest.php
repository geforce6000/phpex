<?php 

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

	$data = array ( 7, "test7", 0, 1);

	/*$dataForUpdate = array (
		'classname'=>'test6');*/
	
	/*$mydb->from('articleclass')
			 ->insert($data);*/

	/*$data = $mydb->from('articleclass')
			 ->where('classid=4')
			 ->update($dataForUpdate);*/

	$e = $mydb->from('articleclass')
			 //->limit(2)
			 //->select('classid, classname')
			 //->where('parentid=0 and classid=2')
			 ->get();

	foreach ($e as $value) {
		print_r($value);
		echo "</br>";
	}

	
  
?>