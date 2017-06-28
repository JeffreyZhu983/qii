<?php
namespace Qii\Driver\Pdo;

\Qii\Autoloader\Import::requires(dirname(dirname(__FILE__)) . DS . 'Response.php');

class Driver extends \Qii\Driver\Base implements \Qii\Driver\Intf
{
	const VERSION = '1.2';
	private static $_instance;
	protected $connection;
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
	/**
	 * @var string $charset 数据库默认编码
	 */
	public $charset = 'UTF8';
	/**
	 * 当前使用的db信息
	 */
	public $useDB;
	/**
	 * @var string $__markKey debug信息保存用的key
	 */
	private $__markKey = '__model';
    /**
     * @var string $_response Response对象
     */
    protected $_response;
	/**
	 * 初始化
	 * @param \Qii_Driver_ConnIntf $connection 数据库连接
	 */
	public function __construct(\Qii\Driver\ConnIntf $connection)
	{
		parent::__construct();
		$this->connection = $connection;
		$this->sysConfigure = $this->connection->getDBInfo();
		$this->useDB = $this->sysConfigure['master']['db'];
        $this->_response = new \Qii\Driver\Response();
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
	 * 查询预处理
	 * @var string $sql 执行的sql语句
	 */
	public function setQuery($sql)
	{
		$this->rs = $rs = $this->query($sql);
		return $rs;
	}

	/**
	 * 查询
	 * @var string $sql 执行的sql语句
	 */
	public function query($sql)
	{
		/**
		 * 如果调试SQL的话就启用时间的记录
		 */
		if ($this->_debugSQL) {
			$startTime = microtime(true);
			$this->_exeSQL[] = $sql;
			\Qii::setPrivate($this->__markKey, array('_exeSQL' => $this->_exeSQL));
		}
		$this->sql = $sql;
		$this->db['CURRENT'] = $this->connection->getConnectionBySQL($sql);
		$this->db['CURRENT']->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$this->db['CURRENT']->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        $this->db['CURRENT']->query('set names utf8');
		$rs = $this->db['CURRENT']->query($sql);
		$this->setError();
		if (!$rs) {
			$error = $this->getError('error');
			return \Qii::setError(false, __LINE__, 1509, $sql, $error[2] == '' ? 'NULL' : $error[2]);
		}
		$rs->setFetchMode(\PDO::FETCH_ASSOC);
		/**
		 * 如果调试SQL的话就启用时间的记录
		 */
		if ($this->_debugSQL) {
			$endTime = microtime(true);
			$costTime = sprintf('%.4f', ($endTime - $startTime));
			$this->_querySeconds[$this->_queryTimes]['sql'] = $sql;
			$this->_querySeconds[$this->_queryTimes]['costTime'] = $costTime;
			$this->_querySeconds[$this->_queryTimes]['startTime'] = $startTime;
			$this->_querySeconds[$this->_queryTimes]['endTime'] = $endTime;
			\Qii::setPrivate($this->__markKey, array('_querySeconds' => $this->_querySeconds));
		}
		$this->_queryTimes++;
		\Qii::setPrivate($this->__markKey, array('_queryTimes' => $this->_queryTimes));
		return $rs;
	}

	/**
	 * 获取一行
	 *
	 * @param Resource $rs
	 * @return Array
	 */
	public function fetch($rs = null)
	{
		if (!$rs) return $this->rs->rech();
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

	/**
	 * 获取一行
	 * @var string $sql 获取一行
	 * @return array 返回数据
	 */
	public function getRow($sql)
	{
		if (!$this->sysConfigure['driver'] == 'mssql' && !preg_match("/LIMIT(\s){1,}(\d){1,},(\s){0,}(\d){1,}/ui", $sql) && !preg_match("/LIMIT(\s){1,}(\d){1,}/ui", $sql)) {
			$sql = $sql . " LIMIT 1";
		} else if ($this->sysConfigure['driver'] == 'mssql' && !preg_match("/^SELECT(\s)TOP(\s)(\d){1,}/i", $sql)) {
			$sql = preg_replace("/^SELECT(\s)/i", "SELECT TOP 1 ", $sql);
		}
		$this->rs = $rs = $this->setQuery($sql);
		return $rs->fetch();
	}

	/**
	 * 获取一列
	 * @var string $sql 需要获取一列的sql语句
	 * @return strin | bool 返回其中一列或者是false
	 */
	public function getOne($sql)
	{
		$rs = $this->setQuery($sql);
		if ($rs) {
			return $rs->fetchColumn();
		}
		return false;
	}

	/**
	 * 获取所有的行
	 * @var string $sql 需要获取所有行的sql语句
	 * @return array
	 */
	public function getAll($sql)
	{
		$this->rs = $rs = $this->setQuery($sql);
		return $rs->fetchAll();
	}

	/**
	 * 事务处理
	 */
	public function transaction()
	{
		$this->db['CURRENT']->beginTransaction();
	}

	/**
	 * 事务提交
	 */
	public function commit()
	{
		$this->db['CURRENT']->commit();
	}

	/**
	 * 事务回滚
	 */
	public function rollback()
	{
		$this->db['CURRENT']->rollBack();
	}

	/**
	 * 影响的行数
	 *
	 * @return Int
	 */
	public function affectedRows()
	{
		if (!$this->rs) return false;
		return $this->rs->rowCount();
	}

	/**
	 * 最后插入到数据库的自增长ID
	 *
	 * @return Int
	 */
	public function lastInsertId()
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
		if ($errorInfo) {
			//将错误加回来
			array_push($this->_errorInfo, $errorInfo);
			if (!empty($key)) {
				return $errorInfo[$key];
			}
			return $errorInfo;
		}
		return null;
	}

	/**
	 * 是否有错，有错误的话存储错误
	 *
	 */
	public function setError()
	{
		if ($this->connection->getConnectionBySQL($this->sql)->errorCode() != '00000') {
			$this->_errorInfo[$this->_queryTimes]['sql'] = $this->sql;
			$this->_errorInfo[$this->_queryTimes]['error'] = $this->connection->getConnectionBySQL($this->sql)->errorInfo();
			$this->_response = \Qii\Driver\Response::Fail('pdo.error', $this->_errorInfo);
			\Qii::setPrivate($this->__markKey, array('_errorInfo' => $this->_errorInfo));
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
		if ($this->connection->getConnectionBySQL($this->sql)->errorCode() != '00000') {
			return true;
		}
		return false;
	}
}