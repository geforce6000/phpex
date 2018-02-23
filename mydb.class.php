<?php 

	class Mydb {

		//自建数据库操作类，提供基本的增删查改功能
		//有一定的适应性，不需要为每个数据库重写
		
		//$serverName 		    服务器地址
		//$userName 			用户名
		//$password 			密码
		//$dbname 				数据库名称
		//$db 					连接数据库对象
		//$connectStatus 	    连接状态

		protected $serverName;
		protected $userName;
		protected $password;
		protected $port;
		protected $dbname;
		protected $db;
		protected $connectStatus;

        //$queryArray			用于保存数据库连贯操作的数组，用以组合sql命令

		//select, where, orderby, limit, like, from, order为连贯操作方法
		//query, get, update, insert, delete为直接操作方法
		protected $queryArray = array (
			'select'=>'',
			'where'=>'',
			'orderby'=>'',
			'order'=>'',
			'limit'=>'',
			'like'=>'',
			'from'=>''
			);
        //空数组，用于清空$queryArray
		protected $queryBlank = array (
			'select'=>'',
			'where'=>'',
			'orderby'=>'',
			'order'=>'',
			'limit'=>'',
			'like'=>'',
			'from'=>''
			);

		//初始化类，根据传入的$dbInfo数组中的信息建立连接
		//返回：无返回值
		function __construct( $dbInfo ) {
			$this->serverName = $dbInfo['serverName'];
			$this->userName = $dbInfo['userName'];
			$this->password = $dbInfo['password'];
			$this->dbname = $dbInfo['dbname'];
			$this->port = $dbInfo['port'];
			$this->db = new mysqli($this->serverName, $this->userName, $this->password, $this->dbname, $this->port);
			if ($this->db->connect_error) {
				die("连接失败");
			}
			$this->connectStatus = 1;
			$this->db->query("set names 'utf8'");
		}

		//析构函数，关闭数据库链接
		function __destruct() {
			$this->db->close();
		}

		//生成SQL指令中字段选择部分
		//$select 	string 		字段名
		function select ( $select ) {
			$this->queryArray['select'] = " ".$select;
			return $this;
		}

		//生成SQL指令中字段条件部分
		//$where 		string 		条件字符串
		function where ( $where ) {
			$this->queryArray['where'] .= " ".$where;
			return $this;
		}

		//生成SQL指令中字段数据表名称部分
		//$from 		string 		数据表名
		function from ( $from ) {
			$this->queryArray['from'] = " ".$from;
			return $this;
		}

        //生成SQL指令中字段限制数量部分
        //$limit 		string 		数量限制字符串
		function limit ( $limit ) {
			$this->queryArray['limit'] = " ".$limit;
			return $this;
		}

		//根据传入的指令类型 $order 生成不同的 SQL 指令字符串并返回
		//返回：SQL指令字符串
		function combineSqlOrder ($order, $data ="") {
			//$order == "select" 表示生成搜索命令
			if ($order == "select") {
				$sqlOrder = "SELECT ";
				if ($this->queryArray['select']<>"") { $sqlOrder .= $this->queryArray['select']; } else { $sqlOrder .= "*";}
				$sqlOrder .= " FROM ".$this->queryArray['from']." ";
				if ($this->queryArray['where']<>"") $sqlOrder .= " WHERE".$this->queryArray['where'];
				if ($this->queryArray['limit']<>"") $sqlOrder .= " LIMIT".$this->queryArray['limit'];
				if ($this->queryArray['orderby']<>"") $sqlOrder .= " ORDER BY".$this->queryArray['orderby']." ".$this->queryArray['order'];
				$this->clearArray();
				return $sqlOrder;
			}
			//$order == "insert" 表示生成插入命令
			if ($order == "insert") {
				$data = $this->checkInsertData($data);
				$sqlOrder = "INSERT INTO ".$this->queryArray['from']." VALUES (".$data.")";
				$this->clearArray();
				return $sqlOrder;
			}
			//$order == "update" 表示生成更新命令
			if ($order == "update") {
				$sqlOrder = "UPDATE ".$this->queryArray['from']." SET";
				foreach ($data as $key => $value) {
					$sqlOrder .= " ".$key."=\"".$value."\"";
				}
				$sqlOrder .= " WHERE ".$this->queryArray['where'];
				$this->clearArray();
				/*print_r($this->queryArray);*/
				return $sqlOrder;
			}
		}

		//insert 方法，用于向数据表中插入一条记录
		//$tableName 	string 	插入的表名
		//dataArray		array 	插入的数据
		//返回：插入成功返回 TRUE ，否则返回 FALSE
		function insert( $dataArray ) {
			$sqlOrder = $this->combineSqlOrder('insert', $dataArray);
			if($this->db->query($sqlOrder) === TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		//get 方法，用于从数据表中获取记录
		//返回：一个数组，每一项是一条记录
		function get () {
			$sqlOrder = $this->combineSqlOrder('select');
			$result = $this->db->query($sqlOrder);
			if($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			return $data;
		}

		//update 方法，用于更新一条记录
		//参数：$dataArray  Array  用于更改的数据，有键值数组，键值为字段名，值为数据
		//更新记录的条件由 where 子句提供，必要条件
		//返回：成功返回 TRUE ，失败返回 FALSE
		function update ( $dataArray ) {
			if($this->queryArray['where'] == "") die("必须提供 where 条件");
			$sqlOrder = $this->combineSqlOrder('update', $dataArray);
			$this->db->query($sqlOrder);
			if($this->db->query($sqlOrder) === TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		//用于将insert方法中存储插入数据的数组转换成可以用在SQL指令中的字符串
		//操作是将数组每一个项的 value 加上双引号
		//返回：一个字符串
		function checkInsertData ($data) {
			return "\"".implode("\",\"", $data )."\"";
		}

		//用于 update, insert, get 等操作之后清空 $queryArray 指令序列
		function clearArray () {
			$this->queryArray = $this->queryBlank;
		}
	}

?>