<?php
/**
 * 数据保存规则
 * @author Jinhui Zhu<jinhui.zhu@live.cn> 2015-12-16
 */
namespace Qii\Driver;

class Rules
{
	const VERSION = '1.2';
	/**
	 * @var  $rules
	 */
	private $rules;

	/**
	 * @var $cacheRules
	 */
	static $cacheRules;

	static $invalidMessage;

	public function __construct($rules)
	{
		$this->rules = $rules;
	}

	/**
	 * 获取使用的数据表名
	 *
	 * @return mixed
	 * @throws \Exception 未定义database的时候就抛出异常
	 */
	public function getDatabase()
	{
		if (!isset($this->rules['database'])) {
			throw new \Exception(\Qii::i(5001, 'database'), __LINE__);
		}
		return $this->rules['database'];
	}

	/**
	 * 获取数据表名称
	 *
	 * @return mixed
	 * @throws \Exception 未定义table的时候就抛出异常
	 * @return string
	 */
	public function getTableName()
	{
		if (!isset($this->rules['tableName'])) {
			throw new \Exception(\Qii::i(5001, 'tableName'), __LINE__);
		}
		return $this->rules['tableName'];
	}

	/**
	 * 获取数据表中的字段列表
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getFields()
	{
		if (!isset($this->rules['rules']['fields'])) {
			throw new \Exception(\Qii::i(5002, 'fields'), __LINE__);
		}
		return $this->rules['rules']['fields'];
	}

	/**
	 * 获取验证失败的时候提示信息
	 * 优先使用规则里表中的验证提示信息，如果未指定，再使用自动生成的规则信息
	 * @return array  提示信息
	 */
	public function getInvalidMessage()
	{
		if (isset($this->rules['rules']['invalidMessage'])) return $this->rules['rules']['invalidMessage'];
		if (isset(self::$invalidMessage[$this->rules['database'] . $this->rules['tableName']])) return self::$invalidMessage[$this->rules['database'] . $this->rules['tableName']];
		return array();
	}

	/**
	 * 获取数据表保存\更新\的验证规则
	 * @return array  获取验证规则
	 */
	public function getOriginalRules()
	{
		if (!isset($this->rules['rules'])) {
			return array();
		}
		return $this->rules['rules'];
	}

	/**
	 * 重置privateKeys使用
	 *
	 * @return array
	 */
	public function getOperateValidFields($opt = null)
	{
		$fields = array();
		//检查是否存在数据使用字段
		if (isset($this->rules['rules']['existValid'])) {
			$fields['exist'] = array_keys($this->rules['rules']['existValid']);
		}
		//检查是保存使用字段
		if (isset($this->rules['rules']['saveValid'])) {
			$fields['save'] = array_keys($this->rules['rules']['saveValid']);
		}
		//更新使用字段
		if (isset($this->rules['rules']['updateValid'])) {
			$fields['update'] = array_keys($this->rules['rules']['updateValid']);
		}
		//删除使用字段
		if (isset($this->rules['rules']['removeValid'])) {
			$fields['remove'] = array_keys($this->rules['rules']['removeValid']);
		}
		if (!$opt) return $fields;
		return isset($fields[$opt]) ? $fields[$opt] : array();
	}

	/**
	 * 获取唯一字段，如果不设置privatekey就是用此字段作为privatekey
	 */
	public function getPrivateKey()
	{
		if (isset($this->rules['rules']['uni']) && count($this->rules['rules']['uni']) > 0) return $this->rules['rules']['uni'];
	}

	/**
	 * 根据字段获取验证规则
	 * @param string $field 需要验证的字段
	 * @return array 验证规则
	 */
	public function getRulesByField($field)
	{
		$rules = $this->buildRules();
		return isset($rules[$field]) ? $rules[$field] : array();
	}

	/**
	 * 根据配置生成所有验证需要的规则
	 *
	 * @return array 生成规则
	 */
	public function buildRules()
	{
		$rules = array();
		if (!isset($this->rules['rules']['validate']) && count($this->rules['rules']['validate']) == 0) {
			return $rules;
		}
		if (isset(self::$cacheRules[$this->rules['database'] . $this->rules['tableName']]['rules'])) return self::$cacheRules[$this->rules['database'] . $this->rules['tableName']]['rules'];
		foreach ($this->rules['rules']['validate'] AS $key => $validate) {
			$fieldRules = array();
			$fieldRules['required'] = false;
			foreach ($validate AS $rule) {
				if ($rule == 'minlength') {
					$fieldRules[$rule] = 1;
					continue;
				}
				if ($rule == 'maxlength') {
					$fieldRules[$rule] = $this->rules['rules']['length'][$key];
					continue;
				}
				if ($rule == 'sets') {
					if (isset($this->rules['rules']['sets'][$key])) $fieldRules[$rule] = $this->rules['rules']['sets'][$key];
					continue;
				}
				$fieldRules[$rule] = true;
				//根据验证类型自动生成规则验证错误信息
				$alias = isset($this->rules['rules']['alias']) && isset($this->rules['rules']['alias'][$key]) ? $this->rules['rules']['alias'][$key] : $key;
				self::$invalidMessage[$this->rules['database'] . $this->rules['tableName']][$key][$rule] = $rule == 'required' ? \Qii::i(5003, $alias) : \Qii::i(5004, $alias);
			}
			$rules[$key] = $fieldRules;
		}
		self::$cacheRules[$this->rules['database'] . $this->rules['tableName']]['rules'] = $rules;
		return $rules;
	}

	/**
	 * 通过数据库操作类型获取对应的规则
	 * @param string $opt 操作类型
	 * @return array 规则
	 */
	public function getRulesByOperate($opt = 'save')
	{
		$rules = array();
		$allowOpt = array('save', 'update', 'remove');
		if (!in_array($opt, $allowOpt)) {
			throw new \Qii\Exceptions\NotAllowed(\Qii::i(5002, $opt), __LINE__);
		}
		if (isset(self::$cacheRules[$this->rules['database'] . $this->rules['tableName']][$opt])) return self::$cacheRules[$this->rules['database'] . $this->rules['tableName']][$opt];
		$buildRules = $this->buildRules();
		//获取字段的验证类型
		$fieldsValidate = $this->rules['rules']['validate'];
		//获取操作需要验证的字段
		if (!isset($this->rules['rules'][$opt]) || count($this->rules['rules'][$opt]) == 0) return $rules;
		foreach ($this->rules['rules'][$opt] AS $key => $val) {
			//如果操作需要验证字段没有设定规则，就验证字段必须有值
			if (isset($buildRules[$key])) {
				$rules[$key] = $buildRules[$key];
			} else {
				$rules[$key] = array('required' => true);
			}
		}
		self::$cacheRules[$this->rules['database'] . $this->rules['tableName']][$opt] = $rules;
		return $rules;
	}

	/**
	 * 通过 $this->$val来获取 $this->$val();的返回内容
	 * @param string $name 属性名称
	 */
	public function __get($name)
	{
		if (method_exists($this, $name)) {
			return call_user_func_array(array($this, $name), array());
		}
		throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $name), __LINE__);
	}

	/**
	 * 调用不存在的方法抛出方法不存在的异常
	 * @param string $method
	 * @param mix $args
	 */
	public function __call($method, $args)
	{
		throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $method), __LINE__);
	}
}