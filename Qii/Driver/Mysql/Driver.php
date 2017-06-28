<?php
namespace Qii\Driver\Mysql;

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
	 * @var int $_queryTimes
	 */
	public $_queryTimes = 0;
	/**
	 * 查询耗时
	 *
	 * @var array $_querySeconds
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
	 * @var array $useDB 当前数据库信息
	 */
	public $useDB;
	/**
	 * @var string $__markKey 用于保存数据库执行相关信息
	 */
	private $__markKey = '__model';
	/**
	 * @var string $_response Response对象
	 */
	protected $_response;

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

	public function setQuery($sql)//查询预处理
	{
		return $this->query($sql);
	}

	public function query($sql)//查询
	{
		/**
		 * 如果调试SQL的话就启用时间的记录
		 */
		if ($this->_debugSQL) {
			$startTime = microtime(true);
			$this->_exeSQL[] = $sql;
			\Qii::setPrivate('model', array('_exeSQL' => $this->_exeSQL));
		}
		$this->sql = $sql;
		$this->db['CURRENT'] = $this->connection->getConnectionBySQL($this->sql);
		if (!empty($this->sysConfigure['charset'])) {
			\mysql_query($this->db['CURRENT'], "SET CHARACTER SET {$this->sysConfigure['charset']}");
		} else {
			\mysql_query($this->db['CURRENT'], "SET CHARACTER SET UTF8");
		}

		$this->rs = $rs = \mysql_query($this->db['CURRENT'], $sql);
		$this->setError();
		if (!$rs) {
			$error = $this->getError('error');
			return \Qii::setError(false, __LINE__, 1509, $sql, $error[2] == '' ? 'NULL' : $error[2]);
		}
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
			\Qii::setPrivate('model', array('_querySeconds' => $this->_querySeconds));
		}
		$this->_queryTimes++;
		\Qii::setPrivate('model', array('_queryTimes' => $this->_queryTimes));
		return $rs;
	}

	/**
	 * 执行SQL
	 *
	 * @param String $sql
	 * @return Int
	 */
	public function exec($sql)
	{
		$this->setQuery($sql);
		return $this->affectedRows();
	}

	public function getRow($sql)//获取一行
	{
		if (!preg_match("/LIMIT(\s){1,}(\d){1,},(\s){0,}(\d){1,}/u", $sql) && !preg_match("/LIMIT(\s){1,}(\d){1,}/u", $sql)) {
			$sql = $sql . " LIMIT 1";
		}
		$rs = $this->query($sql);
		return \mysql_fetch_assoc($rs);
	}

	public function getOne($sql)//获取一列
	{
		$data = $this->getRow($sql);
		return array_shift($data);
	}

	public function getAll($sql)//获取所有的行
	{
		$data = array();
		$rs = $this->query($sql);
		while ($row = \mysql_fetch_assoc($rs)) {
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * 获取一行
	 *
	 * @param Resource $rs
	 * @return Array
	 */
	public function fetch($rs = null)
	{
		if (!$rs) return \mysql_fetch_assoc($this->rs);
		return \mysql_fetch_assoc($rs);
	}

	public function transaction()//事务处理
	{
		\mysql_query('begin');
	}

	public function commit()//事务提交
	{
		\mysql_query('commit');
	}

	public function rollback()//事务回滚
	{
		\mysql_query('rollback');
	}

	public function affectedRows()//返回影响的行数
	{
		return \mysql_affected_rows($this->db['CURRENT']);
	}

	public function lastInsertId()//返回自增长ID
	{
		return \mysql_insert_id($this->db['CURRENT']);
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
		return false;
	}

	/**
	 * 是否有错，有错误的话存储错误
	 *
	 */
	public function setError()//设置错误
	{
		if (\mysql_errno($this->db['CURRENT'])) {
			$this->_errorInfo[$this->_queryTimes]['sql'] = $this->sql;
			$this->_errorInfo[$this->_queryTimes]['error'][2] = \mysql_error($this->db['CURRENT']);
			$this->_response = \Qii\Driver\Response::Fail('pdo.error', $this->_errorInfo);
			\Qii::setPrivate('model', array('_errorInfo' => $this->_errorInfo));
		}
	}

	/**
	 * 是否执行出错
	 *
	 * @return Bool
	 */
	public function isError()
	{
		if ($this->getError()) {
			return true;
		}
		return false;
	}

	/**
	 * 返回response对象
	 *
	 * @return Bool
	 */
	public function getResponse()
	{
		return $this->_response;
	}
}