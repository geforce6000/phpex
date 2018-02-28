<?php
/**
 * Created by PhpStorm.
 * User: geforce
 * Date: 2018/2/27
 * Time: 14:57
 */

    class Mypdo
    {

        //自建数据库操作类，提供基本的增删查改功能
        //有一定的适应性，不需要为每个数据库重写
        //目前还没有做任何的安全性检查，未来会加上
        protected $dbms;        //连接类型
        protected $host;        //服务器地址
        protected $user;        //用户名
        protected $password;    //密码
        protected $port;        //端口
        protected $charset;     //字符集
        protected $dbname;      //数据库名称
        protected $db;          //连接成功后保存数据库对象
        protected $result;      //保存最近一次搜索的结果, echo 对象本身可以打印其中的内容

        //$queryArray			用于保存数据库连贯操作的数组，用以组合sql命令
        //select, where, orderby, limit, like, from, order为连贯操作方法
        //query, get, update, insert, delete为直接操作方法
        protected $queryArray = array(
            'select' => '',
            'where' => '',
            'orderby' => '',
            'limit' => '',
            'like' => '',
            'from' => ''
        );

        //空数组，用于清空$queryArray
        protected $queryBlank = array(
            'select' => '',
            'where' => '',
            'orderby' => '',
            'limit' => '',
            'like' => '',
            'from' => ''
        );

        /**
         * Mypdo constructor.
         * @param $dbInfo   array   数据库信息
         * 无返回值
         */
        function __construct( $dbInfo ) {
            $this->dbms       = $dbInfo['dbms'];
            $this->host       = $dbInfo['host'];
            $this->user      	= $dbInfo['user'];
            $this->password 	= $dbInfo['password'];
            $this->dbname 		= $dbInfo['dbname'];
            $this->port 			= $dbInfo['port'];
            $this->charset    = $dbInfo['charset'];
            $dsn = "{$this->dbms}:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            try {
                $this->db = new PDO( $dsn, $this->user, $this->password, array(
                    PDO::ATTR_PERSISTENT => true));
            } catch ( PDOException $e ) {
                echo "Error: ".$e->getMessage()."</br>";
            }
            $this->result = null;
            //echo "连接成功！"."</br>";
        }

        /**
         * 析构函数，关闭数据库链接
         */
        function __destruct() {
            $this->db = null;
            //echo "连接关闭！"."</br>";
        }

        /**
         * toString方法，返回最后一次查询的内容，若在未进行查询的情况下返回一个字符串 Nothing Found Yet!
         * @return string
         */
        function __toString()	{
            if ( is_null ( $this->result ) ) return "Nothing Found Yet!</br>";
            $string = "";
            foreach ( $this->result as $value ) { $string .= implode( ", ",$value )."</br>"; }
            return $string;
        }

        /**
         * 生成 SQL 指令中 select 部分
         * @param $select string 		字段名
         * @return $this
         * 用法：select('classid, classname')
         */
        function select ( $select ) {
            $this->queryArray['select'] = " ".$select;
            return $this;
        }

        /**
         * 生成 SQL 指令中 where 部分，可以多次添加条件
         * @param $order string 	参加比较的字段和比较运算符，两者之间需要用一个空格隔开
         * @param $value string		比较用的值
         * @param $logic string		比较条件，默认是 AND，还可以是 OR 或 NOT
         * @return $this
         * 用法：where ( "field" , 5 )
         *      where ( "field >", 5 )
         */
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

        /**
         * 生成 SQL 指令中 BEETWEEN 子句，可以连贯使用，但是要注意条件的配合
         * @param $field    string		条件字段
         * @param $up       string		条件上限
         * @param $down     string		条件下限
         * @return $this
         * 用法：between ( 'field', 10, 5 )
         */
        function between ( $field, $up, $down ) {
            $this->queryArray['where'] = " ".$field." BETWEEN ".$up." AND ".$down;
            return $this;
        }

        /**
         * 生成 SQL 指令中 IN 子句，可以连贯使用，但是要注意条件的配合
         * @param $field        string		条件字段
         * @param $valueArray   string		范围数组，无键值
         * @return $this
         * 用法：in ( 'field', array ( '5', '10' )
         */
        function in ( $field, $valueArray ) {
            $this->queryArray['where'] = " ".$field." IN ( ".implode ( ",", $valueArray )." )";
            return $this;
        }

        /**
         * 生成 SQL 指令中 from 部分
         * @param $from     string 		数据表名
         * @return $this
         * 用法：from('articleclass')
         */
        function from ( $from ) {
            $this->queryArray['from'] = " ".$from;
            return $this;
        }

        /**
         * 生成 SQL 指令中 limit 部分
         * @param $num      int 		获取记录条数
         * @param $offset   int			获取记录偏移位置
         * @return $this
         * 用法：limit(3,3)
         */
        function limit ( $num, $offset=0 ) {
            if ( $offset == 0 ) {
                $this->queryArray['limit'] = " ".$num;
            } else {
                $this->queryArray['limit'] = " ".$offset.", ".$num;
            }
            return $this;
        }

        /**
         * 生成 SQL 指令中的 like 部分，可以多次添加条件
         * @param $key      string		字段名
         * @param $value    string		搜索用数值，默认两边加%
         * @param $logic    string		比较条件，默认是 AND，还可以是 OR 或 NOT
         * @return $this
         * 用法：like('onlist', 0)
         */
        function like ( $key, $value, $logic="AND" ) {
            if( $this->queryArray['where'] <> "" ) {
                $this->queryArray['where'] .= " ".$logic." ".$key." LIKE "."'%".$value."%'";
            } else {
                $this->queryArray['where'] .= " ".$key." LIKE "."'%".$value."%'";
            }
            return $this;
        }

        /**
         * 生成 SQL 指令中的 order by 部分，可以多次添加条件
         * @param $key  string		排序的字段名和排序条件，用一个空格隔开，排序条件默认是 ASC，还可以是 DESC
         * @return $this
         * 用法：orderby('classname DESC')
         */
        function orderby ( $key ) {
            if ( $this->queryArray['orderby'] == "" ) {
                $this->queryArray['orderby'] = " ".$key;
            } else {
                $this->queryArray['orderby'] .= " , ".$key;
            }
            return $this;
        }


        /**
         * 根据传入的指令类型 $order 生成不同的 SQL 指令字符串并返回
         * @param $order    string  命令字符串
         * @param $data     string  用于插入或更新的数据
         * @return string   SQL 指令
         */
        function combineSqlOrder ( $order, $data ="" ) {
            //$order == "select" 表示生成搜索命令
            if ( $order == "select" ) {
                $sqlOrder = "SELECT ";
                if ( $this->queryArray['select'] <> "" ) {
                    $sqlOrder .= $this->queryArray['select'];
                } else {
                    $sqlOrder .= "*";
                }
                $sqlOrder .= " FROM ".$this->queryArray['from']." ";
                if ( $this->queryArray['where'] <> "" ) $sqlOrder .= " WHERE".$this->queryArray['where'];
                if ( $this->queryArray['orderby'] <> "" ) $sqlOrder .= " ORDER BY".$this->queryArray['orderby'];
                if ( $this->queryArray['limit'] <> "" ) $sqlOrder .= " LIMIT".$this->queryArray['limit'];
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
                $sqlOrder = "CREATE TABLE IF NOT EXISTS ".$this->queryArray['from']." ( ".$data." )";
            }
            //$order == "drop" 表示删除数据表命令
            if ( $order == "drop" ) {
                $sqlOrder = "DROP TABLE ".$this->queryArray['from'];
            }
            //echo $sqlOrder."</br>";
            $this->clearArray();
            return $sqlOrder;
        }

        /**
         * get 方法，用于从数据表中获取记录，限制条件由上面其他条件方法设置
         * @return array 数组，每一项是一条记录，如果没查到数据，也会返回一个数组，内容为空
         */
        function get () {
            $sqlOrder = $this->combineSqlOrder('select');
            try {
                $rs = $this->db->prepare( $sqlOrder );
                $rs->execute();
                while ( $row = $rs->fetch(PDO::FETCH_ASSOC) ) {
                    $data[] = $row;
                 }
            } catch ( PDOException $e ) {
                //echo "Error: ".$e->getMessage()."</br>";
            }
            $this->result = $data;
            return $data;
        }

        /**
         * insert 方法，用于向数据表中插入一条记录，插入表名由 where 子句提供
         * @param $dataArray    array 	插入的数据，键名为字段名，值为对应字段插入的数据
         * @return bool         插入成功返回 TRUE ，否则返回 FALSE
         */
        function insert( $dataArray ) {
            $sqlOrder = $this->combineSqlOrder( 'insert', $dataArray );
            //if( $this->db->query($sqlOrder) == TRUE ) { return TRUE; } else { return FALSE; }
            try {
                $rs = $this->db->prepare( $sqlOrder );
                $count = $rs->execute();
                //echo $count."条记录已插入</br>";
                return TRUE;
            } catch ( PDOException $e ) {
                //echo "Error: ".$e->getMessage()."</br>";
                return FALSE;
            }
        }

        /**
         * update 方法，用于更新一条记录
         * @param $dataArray    Array  用于更改的数据，有键值数组，键值为字段名，值为数据
         * @return bool 成功返回 TRUE ，失败返回 FALSE
         */
        function update ( $dataArray ) {
            if( $this->queryArray['where'] == "" ) die("必须提供 where 条件");
            $sqlOrder = $this->combineSqlOrder('update', $dataArray);
            try {
                $rs = $this->db->prepare( $sqlOrder );
                $rs->execute();
                return TRUE;
            } catch ( PDOException $e ) {
                //echo "Error: ".$e->getMessage()."</br>";
                return FALSE;
            }
        }

        /**
         * create 方法，创建一个数据表
         * @param $dbstruction  Array   无键值数组，描述数据库结构，值为字段结构描述，表名由 from 子句提供
         * @return bool         成功返回 TRUE，失败返回 FALSE
         */
        function create ( $dbstruction ) {
            $sqlOrder = $this->combineSqlOrder( 'create', implode ( ',', $dbstruction ) );
            try {
                $rs = $this->db->prepare( $sqlOrder );
                $rs->execute();
                return TRUE;
            } catch ( PDOException $e ) {
                //echo "Error: ".$e->getMessage()."</br>";
                return FALSE;
            }
        }

        /**
         * drop 方法，删除一张数据表
         * 参数：无，删除表名由 from 子句提供
         * @return bool
         */
        function drop () {
            $sqlOrder = $this->combineSqlOrder( 'drop' );
            try {
                $rs = $this->db->prepare( $sqlOrder );
                $rs->execute();
                return TRUE;
            } catch ( PDOException $e ) {
                //echo "Error: ".$e->getMessage()."</br>";
                return FALSE;
            }
        }

        /**
         * 用于将 insert 方法中存储插入数据的数组转换成可以用在 SQL 指令中的字符串
         * 操作是将数组每一个项的 value 加上单引号
         * @param $data
         * @return string
         */
        function checkInsertData ( $data ) {
            return "'".implode ("','", $data )."'";
        }

        /**
         * 用于 update, insert, get 等操作之后清空 $queryArray 指令序列
         */
        function clearArray () {
            $this->queryArray = $this->queryBlank;
        }

        /**
         * 检查数据表是否已存在于数据库 $this->dbname 中
         * @param $tableName    array   要检查的数据表名
         * @return bool         如存在返回 TRUE，不存在返回 FALSE
         */
        function checkTable ( $tableName ) {
            $rs = $this->db->query("show TABLES");
            $rs->execute();
            //获取所有数据表名称
            While ($row = $rs->fetch()) {
                $data[] = $row['Tables_in_'.$this->dbname];
            }
            //判断表名是否存在于返回数组中
            if ( in_array ( strtolower ( $tableName ), $data ) ) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }