<?php
namespace Qii\Driver;
/**
 * 数据库读写资源接口文件
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn> 2015-10-25 21:54
 */

interface ConnIntf
{
	public function __construct();

	/**
	 * 通过sql获取连接资源
	 *
	 * @param String $sql
	 */
	public function getConnectionBySQL($sql);

	/**
	 * 获取读数据的连接资源
	 */
	public function getReadConnection();

	/**
	 * 获取取数据的连接资源
	 *
	 */
	public function getWriteConnection();
}