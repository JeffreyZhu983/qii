<?php
namespace Qii\Driver\Mysqli;

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
	public $queryTimes = 0;
	/**
	 * 查询耗时
	 *
	 * @var INT
	 */
	public $querySeconds = array();

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
	 * @var string $markKey 用于保存数据库执行相关信息
	 */
	public $markKey = '__model';
	/**
	 * @var string $response Response对象
	 */
	protected $response;

	public function __construct(\Qii\Driver\ConnIntf $connection)
	{
		parent::__construct();
		$this->connection = $connection;
		$this->sysConfigure = $this->connection->getDBInfo();
		$this->useDB = $this->sysConfigure['master']['db'];
		$this->response = new \Qii\Driver\Response();
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
	 */
	public function setQuery($sql)
	{
		return $this->query($sql);
	}

	/**
	 * 执行查询
	 */
	public function query($sql)
	{
		/**
		 * 如果调试SQL的话就启用时间的记录
		 */
		if ($this->_debugSQL) {
			$startTime = microtime(true);
		}
		$this->sql = $sql;
		$this->db['CURRENT'] = $this->connection->getConnectionBySQL($this->sql);
		if (!empty($this->sysConfigure['charset'])) {
			\mysqli_query($this->db['CURRENT'], "SET CHARACTER SET {$this->sysConfigure['charset']}");
		} else {
			\mysqli_query($this->db['CURRENT'], "SET CHARACTER SET UTF8");
		}

		$this->rs = $rs = \mysqli_query($this->db['CURRENT'], $sql);
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
			$this->querySeconds[$this->queryTimes]['sql'] = $sql;
			$this->querySeconds[$this->queryTimes]['costTime'] = $costTime;
			$this->querySeconds[$this->queryTimes]['startTime'] = $startTime;
			$this->querySeconds[$this->queryTimes]['endTime'] = $endTime;
		}
		$this->queryTimes++;
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
		return \mysqli_fetch_assoc($rs);
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
		while ($row = \mysqli_fetch_assoc($rs)) {
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
		if (!$rs) return \mysqli_fetch_assoc($this->rs);
		return \mysqli_fetch_assoc($rs);
	}

	/**
	 * 事务处理
	 */
	public function transaction()
	{
		\mysqli_query('begin');
	}

	/**
	 * 事务提交
	 */
	public function commit()
	{
		\mysqli_query('commit');
	}

	/**
	 * 事务回滚
	 */
	public function rollback()
	{
		\mysqli_query('rollback');
	}

	/**
	 * 返回影响的行数
	 */
	public function affectedRows()
	{
		return \mysqli_affected_rows($this->db['CURRENT']);
	}

	/**
	 * 返回自增长ID
	 */
	public function lastInsertId()
	{
		return \mysqli_insert_id($this->db['CURRENT']);
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
		if (\mysqli_errno($this->db['CURRENT'])) {
			$this->_errorInfo[$this->queryTimes]['sql'] = $this->sql;
			$this->_errorInfo[$this->queryTimes]['error'][2] = $this->iconv(\mysqli_error($this->db['CURRENT']));
			$this->response = \Qii\Driver\Response::Fail('mysqli.error', $this->_errorInfo);
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
}