<?php 

	class Mydb {

		//自建数据库操作类，提供基本的增删查改功能
		//有一定的适应性，不需要为每个数据库重写
		//目前还没有做任何的安全性检查，未来会加上

		protected $serverName;	//服务器地址
		protected $userName;		//用户名
		protected $password;		//密码
		protected $port;				//端口
		protected $dbname;			//数据库名称
		protected $db;					//连接成功后保存数据库对象
		protected $result;			//保存最近一次搜索的结果, echo 对象本身可以打印其中的内容

		//$queryArray			用于保存数据库连贯操作的数组，用以组合sql命令
		//select, where, orderby, limit, like, from, order为连贯操作方法
		//query, get, update, insert, delete为直接操作方法
		protected $queryArray = array (
			'select'=>'',
			'where'=>'',
			'orderby'=>'',
			'limit'=>'',
			'like'=>'',
			'from'=>''
			);

		//空数组，用于清空$queryArray
		protected $queryBlank = array (
			'select'=>'',
			'where'=>'',
			'orderby'=>'',
			'limit'=>'',
			'like'=>'',
			'from'=>''
			);

		//初始化类，根据传入的$dbInfo数组中的信息建立连接
		//返回：无返回值
		function __construct( $dbInfo ) {
			$this->serverName = $dbInfo['serverName'];
			$this->userName 	= $dbInfo['userName'];
			$this->password 	= $dbInfo['password'];
			$this->dbname 		= $dbInfo['dbname'];
			$this->port 			= $dbInfo['port'];
			$this->db 				= new mysqli($this->serverName, $this->userName, $this->password, $this->dbname, $this->port);
			if ( $this->db->connect_error ) {
				die("连接失败");
			}
			$this->db->query("set names 'utf8'");
			$this->result = null;
			//echo "连接成功！"."</br>";
		}

		//toString方法，返回最后一次查询的内容，若在未进行查询的情况下返回一个字符串 Nothing Found Yet!
		function __toString()	{
			if ( is_null( $this->result ) ) return "Nothing Found Yet!</br>";
			$string = "";
			foreach ( $this->result as $value ) { $string .= implode( ", ",$value )."</br>"; }
			return $string;
		}

		//析构函数，关闭数据库链接
		function __destruct() {
			$this->db->close();
		}

		//生成 SQL 指令中 select 部分
		//$select 	string 		字段名
		//例：select('classid, classname')
		function select ( $select ) {
			$this->queryArray['select'] = " ".$select;
			return $this;
		}

		//生成 SQL 指令中 where 部分，可以多次添加条件
		//$order 		string 		参加比较的字段和比较运算符，两者之间需要用一个空格隔开
		//$value		string		比较用的值
		//$logic		string		比较条件，默认是 AND，还可以是 OR 或 NOT
		function where ( $order, $value, $logic="AND" ) {
			$order = trim ( $order );
			$order = explode ( " ", $order );
			if( count ( $order ) == 1) $order[1] = "=";
			if( $this->queryArray['where'] == "" ) {
				$this->queryArray['where'] .= " ".$order[0].$order[1]."'".$value."'";
			} else {
				$this->queryArray['where'] .= " ".$logic." ".$order[0].$order[1]."'".$value."'";
			}
			return $this;
		}

		//生成 SQL 指令中 from 部分
		//$from 		string 		数据表名
		//例：from('articleclass')
		function from ( $from ) {
			$this->queryArray['from'] = " ".$from;
			return $this;
		}

		//生成 SQL 指令中 limit 部分
		//$num 			int 		获取记录条数
		//$offset		int			获取记录偏移位置
		//例：limit(3,3)
		function limit ( $num, $offset=0 ) {
			//echo "num: $num, offset: $offset";
			if ( $offset == 0 ) {
				$this->queryArray['limit'] = " ".$num;
			} else {
				$this->queryArray['limit'] = " ".$offset.", ".$num;
			}
			return $this;
		}

		//生成 SQL 指令中的 like 部分，可以多次添加条件
		//$key			string		字段名
		//$value		string		搜索用数值，默认两边加%
		//$logic		string		比较条件，默认是 AND，还可以是 OR 或 NOT
		//例：like('onlist', 0)
		function like ( $key, $value, $logic="AND" ) {
			if( $this->queryArray['where'] <> "" ) {
				$this->queryArray['where'] .= " ".$logic." ".$key." LIKE "."'%".$value."%'";
			} else {
				$this->queryArray['where'] .= " ".$key." LIKE "."'%".$value."%'";
			}
			return $this;
		}

		//生成 SQL 指令中的 order by 部分，可以多次添加条件
		//$key		string		排序的字段名和排序条件，用一个空格隔开，排序条件默认是 ASC，还可以是 DESC
		//例：orderby('classname DESC')
		function orderby ( $key ) {
			if ( $this->queryArray['orderby'] == "" ) {
				$this->queryArray['orderby'] = " ".$key;
			} else {
				$this->queryArray['orderby'] .= " , ".$key;
			}
			return $this;
		}

		//根据传入的指令类型 $order 生成不同的 SQL 指令字符串并返回
		//返回：SQL 指令字符串
		function combineSqlOrder ( $order, $data ="" ) {
			//$order == "select" 表示生成搜索命令
			if ( $order == "select" ) {
				$sqlOrder = "SELECT ";
				if ( $this->queryArray['select']<>"" ) { $sqlOrder .= $this->queryArray['select']; } else { $sqlOrder .= "*";}
				$sqlOrder .= " FROM ".$this->queryArray['from']." ";
				if ( $this->queryArray['where']<>"" ) $sqlOrder .= " WHERE".$this->queryArray['where'];
				if ( $this->queryArray['orderby']<>"" ) $sqlOrder .= " ORDER BY".$this->queryArray['orderby'];
				if ( $this->queryArray['limit']<>"" ) $sqlOrder .= " LIMIT".$this->queryArray['limit'];
			}
			//$order == "insert" 表示生成插入命令
			if ( $order == "insert" ) {
				$values = $this->checkInsertData ( $data );
				$field = "(".implode( ",", array_keys( $data ) ).")";
				$sqlOrder = "INSERT INTO ".$this->queryArray['from']." ".$field." VALUES (".$values.")";
			}
			//$order == "update" 表示生成更新命令
			if ( $order == "update" ) {
				$sqlOrder = "UPDATE ".$this->queryArray['from']." SET";
				foreach ( $data as $key => $value ) { $sqlOrder .= " ".$key."=\"".$value."\""; }
				$sqlOrder .= " WHERE ".$this->queryArray['where'];
			}
			//$order == "create" 表示新建数据表命令
			if ( $order == "create" ) {
				$sqlOrder = "CREATE TABLE ".$this->queryArray['from']." ( ".$data." )";
			}
			//$order == "drop" 表示删除数据表命令
			if ( $order == "drop" ) {
				$sqlOrder = "DROP TABLE ".$this->queryArray['from'];
			}
			$this->clearArray();
			return $sqlOrder;
		}

		//insert 方法，用于向数据表中插入一条记录，插入表名由 where 子句提供
		//dataArray		array 	插入的数据，键名为字段名，值为对应字段插入的数据
		//返回：插入成功返回 TRUE ，否则返回 FALSE
		function insert( $dataArray ) {
			$sqlOrder = $this->combineSqlOrder( 'insert', $dataArray );
			//echo "sqlorder for install: ".$sqlOrder;
			if( $this->db->query($sqlOrder) == TRUE ) { return TRUE; } else { return FALSE; }
		}

		//get 方法，用于从数据表中获取记录，限制条件由上面其他条件方法设置
		//返回：一个数组，每一项是一条记录，如果没查到数据，也会返回一个数组，内容为空
		function get () {
			$sqlOrder = $this->combineSqlOrder('select');
			//echo $sqlOrder.'</br>';
			$result = $this->db->query($sqlOrder);
			if( $result->num_rows > 0 ) {
				while ( $row = $result->fetch_assoc() ) { $data[] = $row; }
			} else { $data = array ( 'not found'=>array ( 'record'=>0 ) ); }
			$this->result = $data;
			return $data;
		}

		//update 方法，用于更新一条记录
		//参数：$dataArray  Array  用于更改的数据，有键值数组，键值为字段名，值为数据
		//更新记录的条件由 where 子句提供，必要条件
		//返回：成功返回 TRUE ，失败返回 FALSE
		function update ( $dataArray ) {
			if( $this->queryArray['where'] == "" ) die("必须提供 where 条件");
			$sqlOrder = $this->combineSqlOrder('update', $dataArray);
			$this->db->query($sqlOrder);
			if( $this->db->query($sqlOrder) === TRUE ) { return TRUE;	} else { echo "插入失败"; return FALSE; }
		}

		//create 方法，创建一个数据表
		//参数：	$dbstruction 	Array 		无键值数组，描述数据库结构，值为字段结构描述，表名由 from 子句提供
		//返回：成功返回 TRUE，失败返回 FALSE
		function create ( $dbstruction ) {
			$sqlOrder = $this->combineSqlOrder( 'create', implode ( ',', $dbstruction ) );
			$this->db->query( $sqlOrder );
			if( $this->db->query($sqlOrder) == TRUE ) { return TRUE; } else { return FALSE; }
		}

		//drop 方法，删除一张数据表
		//参数：无，删除表名由 from 子句提供
		function drop () {
			$sqlOrder = $this->combineSqlOrder( 'drop' );
			$this->db->query( $sqlOrder );
			if( $this->db->query($sqlOrder) == TRUE ) { return TRUE; } else { return FALSE; }
		}

		//用于将 insert 方法中存储插入数据的数组转换成可以用在 SQL 指令中的字符串
		//参数：$data Array
		//操作是将数组每一个项的 value 加上双引号
		//返回：一个字符串
		function checkInsertData ( $data ) {
			return "\"".implode("\",\"", $data )."\"";
		}

		//用于 update, insert, get 等操作之后清空 $queryArray 指令序列
		function clearArray () {
			$this->queryArray = $this->queryBlank;
		}

	}

?>