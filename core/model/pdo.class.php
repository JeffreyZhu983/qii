<?php
/**
 * URI
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: pdo.php 188 2011-08-01 09:18:34Z zjh $
 */
Qii::requireOnce(dirname(__FILE__) . DS . 'iterface.php');
class pdo_class extends modelBase implements modelIterface
{
	private $sysConfigure;
	private $rs;
	public $db;
	/**
	 * 是否开启调试
	 *
	 * @var BOOL
	 */
	public $_debugSQL = true;
	/**
	 * 执行SQL的列表
	 *
	 * @var Array
	 */
	public $_exeSQL = array();
	/**
	 * 查询次数
	 *
	 * @var unknown_type
	 */
	public $_queryTimes = 0;
	/**
	 * 查询耗时
	 *
	 * @var INT
	 */
	public $_querySeconds = array();
	
	/**
	 * 最后一次执行的SQL
	 *
	 * @var unknown_type
	 */
	private $sql;
	/**
	 * 是否开启执行SQL的时间
	 *
	 * @var BOOL
	 */
	public $_debugTime = false;
	public $_errorInfo = array();
	/**
	 * 保存未定义变量
	 *
	 * @var Array
	 */
	private $_undefined;
	public $charset = 'UTF8';
	/**
	 * 初始化
	 *
	 * @param String $sysConfigure Qii::setPrivate中保存的配置文件
	 */
	public function __construct($sysConfigure = 'qii_site_db')
	{
		$this->sysConfigure = Qii::getPrivate($sysConfigure);
	}
	/**
	 * 用户直接输出这个实例化的类后会输出当前类的名称
	 *
	 * @return String
	 */
	public function __toString()
	{
		return get_class($this);
	}
	/**
	 * 检查语句是否是读/写
	 *
	 * @param String $sql
	 * @return String
	 */
	public function prepare($sql)
	{
		$default = "READ";
		$readMode = "/^SELECT\s/u";
		$writeMode = "/^(UPDATE)|(REPLACE)|(DELETE)\s/u";
		$isRead = preg_match($readMode, $sql);
		$isWrite = preg_match($writeMode, $sql);
		if($isWrite)
		{
			$default = "WRITE";
		}
		return $default;
	}
	/**
	 * 初始化连接
	 *
	 * @param String $readOrWrite
	 * @return Object
	 */
	public function initExec($readOrWrite = "READ")//初始化数据库链接
	{
		//检测，如果上一次操作为写操作或者没设置读写分离的话就不用再重新连接数据库服务器
		if(is_object(Qii::getPrivate('model_pdo_'. $readOrWrite)))
		{
			return Qii::getPrivate('model_pdo_'. $readOrWrite);
		}
		/*if(isset($this->db[$readOrWrite]))
		{
			return $this->db[$readOrWrite];
		}
		/*if(($readOrWrite == 'WRITE' && isset($this->db[$readOrWrite])) || (!$this->sysConfigure['readOrWriteSeparation'] && isset($this->db[$readOrWrite])))
		{
			return $this->db[$readOrWrite];
		}*/
		$allowExec = array("WRITE" => 'master', 'READ' => 'slave');
		//如果没指定读写分离就直接用写的服务器
		$dbInfo = $this->sysConfigure['master'];
		if($this->sysConfigure['readOrWriteSeparation'] && $allowExec[$readOrWrite] == 'slave' && $this->sysConfigure['slave'])
		{
			$i = rand(0, count($this->sysConfigure['slave']) - 1);
			$dbInfo = $this->sysConfigure['slave'][$i];
		}
		if(empty($this->sysConfigure['driven']))
		{
			$this->sysConfigure['driven'] = "mysql";
		}
		if($this->sysConfigure['driven'] == 'mssql')
		{
			$dsn = 'odbc:Driver={SQL Server};Server='.$dbInfo['host'].';Database='.$dbInfo['db'].';';
		}
		else
		{
			$dsn = $this->sysConfigure['driven'].":host=".$dbInfo['host'].";dbname=". $dbInfo['db'];
		}
		//如果读的服务器挂了就直接连接写的服务器
		try
		{
			$this->db[$readOrWrite] = $db = new PDO($dsn, $dbInfo['user'], $dbInfo['password']);
		}
		catch (Exception $e) 
		{
			if($readOrWrite == 'READ')
			{
				$dbInfo = $this->sysConfigure['master'];
				if($this->sysConfigure['driven'] == 'mssql')
				{
					$dsn = 'odbc:Driver={SQL Server};Server='.$dbInfo['host'].';Database='.$dbInfo['db'].';';
				}
				else
				{
					$dsn = $this->sysConfigure['driven'].":host=".$dbInfo['host'].";dbname=". $dbInfo['db'];
				}
				$this->db[$readOrWrite] = $db = new PDO($dsn, $dbInfo['user'], $dbInfo['password']);
			}
			else 
			{
				return Qii::setError(false, 1, array($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['db']));
			}
		}
		Qii::setPrivate('model_pdo_'. $readOrWrite, $db);
		return $db;
	}
	public function setQuery($sql)//查询预处理
	{
		$this->rs = $rs = $this->query($sql);
		return $rs;
	}
	public function query($sql)//查询
	{
		/**
		 * 如果调试SQL的话就启用时间的记录
		 */
		if($this->_debugSQL)
		{
			$startTime = microtime(true);
			$this->_exeSQL[] = $sql;
			Qii::setPrivate('model', array('_exeSQL'=>$this->_exeSQL));
		}
		$this->sql = $sql;
		$this->db['CURRENT'] = $this->initExec($this->prepare($sql));
		if(!empty($this->sysConfigure['charset']))
		{
			$this->db['CURRENT']->query("SET CHARACTER SET {$this->sysConfigure['charset']}");
		}
		else 
		{
			$this->db['CURRENT']->query("SET CHARACTER SET UTF8");
		}
		$this->db['CURRENT']->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$this->db['CURRENT']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		//$this->rs = $rs = $this->db['CURRENT']->query($this->setQuote($sql));
		$rs = $this->db['CURRENT']->query($sql);
		$this->setError();
		if(!$rs)
		{
			$error = $this->getError('error');
			return Qii::setError(false, 110, array($sql, $error[2] == '' ? 'NULL' : $error[2]));
		}
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		/**
		 * 如果调试SQL的话就启用时间的记录
		 */
		if($this->_debugSQL)
		{
			$endTime = microtime(true);
			$costTime = sprintf('%.4f', ($endTime - $startTime));
			$this->_querySeconds[$this->_queryTimes]['sql'] = $sql;
			$this->_querySeconds[$this->_queryTimes]['costTime'] = $costTime;
			$this->_querySeconds[$this->_queryTimes]['startTime'] = $startTime;
			$this->_querySeconds[$this->_queryTimes]['endTime'] = $endTime;
			Qii::setPrivate('model', array('_querySeconds'=>$this->_querySeconds));
		}
		$this->_queryTimes++;
		Qii::setPrivate('model', array('_queryTimes' => $this->_queryTimes));
		return $rs;
	}
	/**
	 * 获取一行
	 *
	 * @param Resource $rs
	 * @return Array
	 */
	public function fetch($rs)
	{
		if(!$rs) return $this->rs->rech();
		return $rs->fetch();
	}
	/**
	 * 执行SQL
	 *
	 * @param String $sql
	 * @return Int
	 */
	public function exec($sql)
	{
		$this->rs = $this->query($sql);
		return $this->affectedRows();
	}
	/**
	 * 设置获取数据的类型
	 *
	 */
	public function setFetchMode()
	{
		$this->rs->setFetchMode(PDO::FETCH_ASSOC);
	}
	public function getRow($sql)//获取一行
	{
		if(!$this->sysConfigure['driven'] == 'mssql' && !preg_match("/LIMIT(\s){1,}(\d){1,},(\s){0,}(\d){1,}/ui", $sql) && !preg_match("/LIMIT(\s){1,}(\d){1,}/ui", $sql))
		{
			$sql = $sql . " LIMIT 1";
		}
		else if($this->sysConfigure['driven'] == 'mssql' && !preg_match("/^SELECT(\s)TOP(\s)(\d){1,}/i", $sql))
		{
			$sql = preg_replace("/^SELECT(\s)/i", "SELECT TOP 1 ", $sql);
		}
		$this->rs = $rs = $this->setQuery($sql);
		return $rs->fetch();
	}
	public function getOne($sql)//获取一列
	{
		$rs = $this->setQuery($sql);
		if($rs)
		{
			return $rs->fetchColumn();
		}
		return false;
	}
	public function getAll($sql)//获取所有的行
	{
		$this->rs = $rs = $this->setQuery($sql);
		return $rs->fetchAll();
	}
	public function transaction()//事务处理
	{
		$this->db['CURRENT']->beginTransaction();
	}
	public function commit()//事务提交
	{
		$this->db['CURRENT']->commit();
	}
	public function rollback()//事务回滚
	{
		$this->db['CURRENT']->rollBack();
	}
	/**
	 * 影响的行数
	 *
	 * @return Int
	 */
	public function affectedRows()//返回影响的行数
	{
		if(!$this->rs) return false;
		return $this->rs->rowCount();
	}
	/**
	 * 最后插入到数据库的自增长ID
	 *
	 * @return Int
	 */
	public function lastInsertId()//返回自增长ID
	{
		return $this->db['CURRENT']->lastInsertId();
	}
	/**
	 * 获取最后一次出错的信息
	 *
	 * @return Array
	 */
	public function getError($key = '')
	{
		$errorInfo = array_pop($this->_errorInfo);
		if($errorInfo)
		{
			//将错误加回来
			array_push($this->_errorInfo, $errorInfo);
			if(!empty($key))
			{
				return $errorInfo[$key];
			}
			return $errorInfo;
		}
		return NULL;
	}
	/**
	 * 是否有错，有错误的话存储错误
	 *
	 */
	public function setError()
	{
		if($this->initExec($this->prepare($this->sql))->errorCode() != '00000')
		{
			$this->_errorInfo[$this->_queryTimes]['sql'] = $this->sql;
			$this->_errorInfo[$this->_queryTimes]['error'] = $this->initExec($this->prepare($this->sql))->errorInfo();
			Qii::setPrivate('model', array('_errorInfo'=>$this->_errorInfo));
		}
	}
	/**
	 * 是否执行出错
	 *
	 * @return Bool
	 */
	public function isError()
	{
		$errorInfo = $this->rs->errorInfo();
		if($errorInfo[0]!= '00000')
		{
			return true;
		}
		return false;
	}
}
?>