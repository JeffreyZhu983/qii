<?php
namespace Qii\Driver;

/**
 * 数据库连接基类
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn> 2015-12-10 14:34
 */
class ConnBase
{
	const VERSION = '1.2';
	/**
	 * @var $allowExec  读写对应的数据库配置文件
	 */
	public $allowExec = array("WRITE" => 'master', 'READ' => 'slave');

	/**
	 * 获取数据库配置中指定的key的值，不指定则获取全部
	 * @param string key 数据库配置中指定的key
	 */
	public function getDBInfo($key = null)
	{
		if ($key != null) return isset($this->_dbInfo[$key]) ? $this->_dbInfo[$key] : false;
		return $this->_dbInfo;
	}

	/**
	 * 通过sql语句判断是读还是写操作
	 * @param $sql  读/写的sql语句
	 */
	protected function prepare($sql)
	{
		$default = "READ";
		$readMode = "/^SELECT\s/u";
		$writeMode = "/^(UPDATE)|(REPLACE)|(DELETE)\s/u";
		$isRead = preg_match($readMode, $sql);
		$isWrite = preg_match($writeMode, $sql);
		if ($isWrite) $default = "WRITE";
		if (!isset($this->allowExec[$default])) $default = 'WRITE';
		return $default;
	}

	/**
	 * 通过sql获取连接资源
	 *
	 * @param String $sql 通过sql语句获取读/写操作对应的res
	 * @return res
	 */
	public function getConnectionBySQL($sql)
	{
		$default = $this->prepare($sql);
		if (isset($this->_connections[$default])) return $this->_connections[$default];
		switch ($default) {
			case 'READ':
				return $this->_connections[$default] = $this->getReadConnection();
				break;
			default:
				return $this->_connections['WRITE'] = $this->getWriteConnection();
				break;
		}
		throw new \Exception('Call undefined driver', __LINE__);
	}
}