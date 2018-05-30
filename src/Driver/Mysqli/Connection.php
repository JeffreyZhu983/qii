<?php
namespace Qii\Driver\Mysqli;

class Connection extends \Qii\Driver\ConnBase implements \Qii\Driver\ConnIntf
{
	const VERSION = '1.2';
	protected $_dbInfo;

	public function __construct()
	{
		$this->_dbInfo = \Qii\Config\Register::getAppConfigure(\Qii\Config\Register::get(\Qii\Config\Consts::APP_DB));
	}

	/**
	 * 获取读数据的连接资源
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
				$connection = mysqli_connect($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['db']);
				if (!$connection) throw new \Qii\Exceptions\Errors(\Qii::i(1501, iconv("GBK", "UTF-8//TRANSLIT", mysqli_connect_error())), true);
				mysqli_select_db($connection, $dbInfo['db']);
				return $connection;
			} catch (Exception  $e) {
				return $this->getWriteConnection();
			}
		}
		return $this->getWriteConnection();
	}

	/**
	 * 获取写数据的连接资源
	 *
	 */
	public function getWriteConnection()
	{
		$dbInfo = $this->_dbInfo['master'];
		try {
			$connection = @mysqli_connect($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['db']);
			if (!$connection) throw new \Qii\Exceptions\Errors(\Qii::i(1501, iconv("GBK", "UTF-8//TRANSLIT", mysqli_connect_error())), true);
			mysqli_select_db($connection, $dbInfo['db']);
			return $connection;
		} catch (Exception  $e) {
			throw new \Qii\Exceptions\Errors(\Qii::i(1500, $dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['db'], toUTF8($e->getMessage())), __LINE__);
		}
	}
}