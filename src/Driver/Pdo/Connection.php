<?php
/**
 * 数据库连接类
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-12-10 14:42
 */
namespace Qii\Driver\Pdo;

class Connection extends \Qii\Driver\ConnBase implements \Qii\Driver\ConnIntf
{
	const VERSION = '1.2';
	/**
	 * @var array $_dbInfo 数据库配置
	 */
	protected $_dbInfo;
	/**
	 * @var res $_instanceConnection 数据库连接
	 */
	protected $_instanceConnection;

	public function __construct()
	{
		$this->_dbInfo = \Qii\Config\Register::getAppConfigure(\Qii\Config\Register::get(\Qii\Config\Consts::APP_DB));
		if(!isset($this->_dbInfo['use_db_driver'])) $this->_dbInfo['use_db_driver'] = 'mysql';
	}

	/**
	 * 获取读数据的连接资源
	 * @return res
	 */
	public function getReadConnection()
	{
		$dbInfo = $this->_dbInfo['master'];
		$useSlave = false;

		if ($this->_dbInfo['readOrWriteSeparation'] && $this->_dbInfo['slave']) {
			$i = rand(0, count($this->_dbInfo['slave']) - 1);
			$dbInfo = $this->_dbInfo['slave'][$i];
			$useSlave = true;
		}
		if ($useSlave) {
			try {
				if ($this->_dbInfo['use_db_driver'] == 'mssql') {
					$dsn = 'odbc:Driver={SQL Server};Server=' . $dbInfo['host'] . ';Database=' . $dbInfo['db'] . ';';
				} else {
					$dsn = $this->_dbInfo['use_db_driver'] . ":host=" . $dbInfo['host'] . ";dbname=" . $dbInfo['db'];
				}
				return new \PDO($dsn, $dbInfo['user'], $dbInfo['password']);
			} catch (Exception  $e) {
				return $this->getWriteConnection();
			}
		}
		return $this->getWriteConnection();
	}

	/**
	 * 获取写数据的连接资源
	 * @return res
	 */
	public function getWriteConnection()
	{
		$dbInfo = $this->_dbInfo['master'];
		try {
			if ($this->_dbInfo['use_db_driver'] == 'mssql') {
				$dsn = 'odbc:Driver={SQL Server};Server=' . $dbInfo['host'] . ';Database=' . $dbInfo['db'] . ';';
			} else {
				$dsn = $this->_dbInfo['use_db_driver'] . ":host=" . $dbInfo['host'] . ";dbname=" . $dbInfo['db'];
			}
			return new \PDO($dsn, $dbInfo['user'], $dbInfo['password']);
		} catch (Exception  $e) {
			throw new \Qii\Exceptions\Errors(\Qii::i(1500, $dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['db'], iconv('GB2312', 'UTF-8', $e->getMessage())), __LINE__);
		}
	}
}