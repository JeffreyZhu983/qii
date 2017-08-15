<?php
/**
 * 数据库接口文件
 * @author Jinhui Zhu<zhujinhui@zhangyue.com>2015-10-25 21:54
 */
namespace Qii\Driver;

interface Intf
{
	public function __construct(\Qii\Driver\ConnIntf $connection);

	/**
	 * 执行SQL前检查是读/写
	 *
	 * @param String $sql
	 * @return String READ/WRITE
	 */
	public function setQuery($sql);//查询预处理

	public function query($sql);//查询

	public function exec($sql);//执行查询并返回影响的行数

	public function fetch($rs);//获取一行，在while循环中可使用

	public function getRow($sql);//获取一行

	public function getOne($sql);//获取一列

	public function getAll($sql);//获取所有的行

	public function transaction();//事务处理开始

	public function commit();//事务提交

	public function rollback();//事务回滚

	public function affectedRows();//返回影响的行数

	public function lastInsertId();//返回自增长ID

	public function getError($key = '');//获取错误

	public function setError();//设置错误
}