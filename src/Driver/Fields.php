<?php
/**
 * 存储表的相关数据
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-09-21 16:52
 *
 * 用法
 *
 */
namespace Qii\Driver;

final class Fields
{
	const VERSION = '1.2';
	protected $keys;
	protected $fields;

	/**
	 * 初始化数据表结构
	 *
	 * @param $fields
	 *
	 */
	public function __construct($fields)
	{
		if (!is_array($fields) && count($fields) == 0) throw new \Exception(\Qii::i(1508), __LINE__);
		$this->keys = new \stdClass();
		$this->fields = $fields;
		return $this;
	}

	/**
	 * 设置数据表字段值，仅在列表中的才保存到对应的字段中
	 *
	 * @param $name
	 * @param $val
	 * @return $this
	 */
	public function __set($name, $val)
	{
		if (in_array($name, $this->fields)) $this->keys->$name = $val;
		return $this;
	}

	/**
	 * 判断是否存在相关键值
	 *
	 * @param $field
	 * @return bool
	 */
	public function isField($field)
	{
		if (isset($this->keys->$field)) return true;
		return false;
	}

	/**
	 * 获取相关的键值
	 *
	 * @param $field
	 * @return null
	 */
	public function getField($field)
	{
		if (isset($this->keys->$field)) return $this->keys->$field;
		return null;
	}

	/**
	 * 获取字段及值
	 *
	 * @return stdClass
	 */
	public function getValues()
	{
		return $this->keys;
	}

	/**
	 * 以array的形式返回字段及值
	 *
	 * @return array
	 */
	public function getValueAsArray()
	{
		return (array)$this->keys;
	}

	public function __call($method, $argvs)
	{
		throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $method . ' Not found'), __LINE__);
	}
}