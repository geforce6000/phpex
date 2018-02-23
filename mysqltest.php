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

	$data = array (
			'classid'=>8,
			'classname'=>"test8",
			'parentid'=>0,
			'onlist'=>1);

	$dataForUpdate = array (
		'classname'=>'test6');
	
	/*$mydb->from('articleclass')
			 ->insert($data);*/

	/*$data = $mydb->from('articleclass')
			 ->where('classid',4)
			 ->update($dataForUpdate);*/

	$e = $mydb->from('articleclass')
			 //->like('classname', 6)
			 //->like('onlist', 0)
			 //->limit(3,3)
			 //->orderby('classname DESC')
			 //->orderby('classid')
			 //->select('classid, classname')
			 ->where('classname','test6')
			 ->where('classid',2,">")
			 ->get();

	foreach ($e as $value) {
		print_r($value);
		echo "</br>";
	}



?>