<?php
/**
 * 简易数据库操作类
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-12-10 14:35
 *
 * usage:
 * $user = (new \Qii\Driver\Easy())->_initialize();
 * $user->setRules(new \Qii\Driver\Rules($rules));
 * $user->setPrivateKey(array());
 * $user->uid = 1;
 * $user->email = 'antsnet@163.com';
 * $user->nick = 'Jinhui Zhu';
 * $user->add_time = time();
 * $user->update_time = time();
 * 保存数据
 * $user->_save();
 * 检查指定数据是否存在，以privateKey为准
 * $user->_exist();
 * 删除指定数据，以privateKey为准
 * $user->_remove()
 * 更新数据,以privateKey为准
 * $user->_update();
 * if($user->isError())
 * {
 *     //todo 如果执行操作有问题，错误详情 $user->getErrors()
 * }
 * else
 * {
 *     //todo 对返回结果进行处理
 * }
 *
 * //获取所有
 * $user->_getRowsByEmail('antsnet@163.com');
 * $user->_getRowByFileds($this->_privateKey);
 * $user->_getRowsByFileds($this->_privateKey);
 * $user->getEmailByUid(1);
 * $user->_getAllByUid(1);
 */
namespace Qii\Driver;

\Qii\Autoloader\Import::requires(dirname(__FILE__) . DS . 'Response.php');

class Easy
{
	const VERSION = '1.2';
	/**
	 * @var Fields $fields
	 */
	private $fields = null;
	/**
	 * 数据表主键
	 * @var array $privateKeys
	 */
	private $privateKeys = array();
	/**
	 * 保存或更新的时候验证的规则
	 * @var array $rules
	 */
	private $easyRules = array();
	/**
	 * @var string $databaseName 数据库名称
	 * 如果没有指定数据库名称将使用当前连接的数据库
	 */
	private $databaseName;
	/**
	 * @var string $tableName 表名，
	 * 如果要指定操作的数据库名需要将数据库名和表名合在一起，如：database.table
	 */
	private $tableName = '';
	/**
	 * @var book $isInstance 是否实例化对象了
	 */
	private $isInstance = false;
	/**
	 * @var array $_response 操作出错保存数据到此数组
	 */
	private $_response;
	/**
	 * @var bool  是否需要重置privateKeys
	 */
	private $needResetPrivatekey = true;
	
	private $db;

	public function __construct()
	{
		$this->db = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Driver\Model')->db;
		$this->clean();
		return $this;
	}

	/**
	 * 初始化数据库对象
	 *
	 */
	final public function _initialize()
	{
		$this->isInstance = true;
		$this->clean();
		return $this;
	}

	/**
	 * 设置表字段
	 *
	 * @param array $fields
	 * @return $this
	 */
	final public function setFields(array $fields)
	{
		$this->fields = new Fields($fields);
		return $this;
	}

	/**
	 * 设置主键
	 *
	 * @param array|string $privateKeys
	 */
	final public function setPrivateKey(array $privateKeys)
	{
		if(count($privateKeys) == 0) return $this;
		$this->needResetPrivatekey = false;
		$this->privateKeys = $privateKeys;
		return $this;
	}

	/**
	 * 重置主键，主要用于查询、更新、删除操作
	 *
	 * @param string | null $opt
	 * @return array
	 */
	final public function resetPrivateKey($opt = null)
	{
		if ($this->needResetPrivatekey && $opt) {
			$valid = $this->easyRules->getOperateValidFields($opt);
			if (!empty($valid)) $this->privateKeys = array_values($valid);
		}
		return $this->privateKeys;
	}

	/**
	 * 设置规则
	 * @param array $rules {_save:{}, _update:{}, _remove:{}}
	 */
	final public function setRules(\Qii\Driver\Rules $rules)
	{
		$this->setFields($rules->getFields());
		$this->tableName = $rules->getTableName();
		$this->databaseName = $rules->getDatabase();
		if (!$this->privateKeys) $this->privateKeys = $rules->getPrivateKey();
		$this->easyRules = $rules;
		return $this;
	}

	/**
	 * 设置表字段
	 *
	 * @param $name
	 * @param $val
	 * @return $this
	 */
	public function __set($name, $val)
	{
		$this->fields->$name = $val;
		return $this;
	}

	/**
	 * 批量设置字段的值
	 * @param array $data
	 * @return $this
	 */
	public function setFieldsVal(array $data)
	{
		foreach ($data AS $name => $val) {
			$this->fields->$name = $val;
		}
		return $this;
	}

	/**
	 * 获取当前使用的rules的字段
	 *
	 * @return mixed
	 */
	public function getFields()
	{
		return $this->easyRules->getFields();
	}
	/**
	 * 获取当前使用的fields的值
	 *
	 * @return mixed
	 */
	public function getValues()
	{
		return $this->fields->getValues();
	}

	/**
	 * 如果没有调用parent::__construct()方法就报错
	 *
	 */
	public function checkInstance()
	{
		if (!$this->isInstance) {
			$this->_response = Response::Fail('checkInstance', array('msg' => \Qii::i(1507, 'parent::__construct()'), 'code' => __LINE__));
			throw new \Qii\Exceptions\TableException(\Qii::i(1507, 'parent::__construct()'), __LINE__);
		}
		return $this;
	}

	/**
	 * 重置Fields及相关条件
	 *
	 */
	final protected function clean()
	{
		$this->fields = null;
		$this->privateKeys = array();
		$this->easyRules = array();
		$this->tableName = '';
		$this->_response = new Response();
		return $this;
	}

	/**
	 * 验证保存的数据
	 *
	 * @param $rules
	 * @return bool
	 */
	final protected function validateFields($rules)
	{
		if (empty($rules)) return true;
		$validateCls = _loadClass('\Qii\Library\Validate');
		$result = $validateCls->verify($this->fields->getValues(), $rules, $this->easyRules->getInvalidMessage());
		if ($result === true) {
			return true;
		}
		$error = $validateCls->getErrors();
		$this->_response = Response::FailValidate('validate', array('_result' => $error['msg'], 'fields' => array('field' => $error['field'], 'message' => $error['msg'])));
		return false;
	}

	/**
	 * 获取用户表
	 */
	final public function getTableName()
	{
		if (!$this->tableName) throw new \Qii\Exceptions\Errors(\Qii::i(1510), true);
		return $this->databaseName ? $this->databaseName . '.' . $this->tableName : $this->tableName;
	}

	/**
	 * 对指定字段执行指定方法 同时校验值的有效性
	 *
	 * @param String $func
	 * @param String $field
	 * @param String $val
	 * @return Object
	 */
	final public function func($func, $key, $val)
	{
		$rule = $this->getValidateField($key);
		//如果字段需要用到函数编码字符，如果没有通过验证就不将值放入fields中

		$validateCls = _loadClass('Qii\Library\Validate');
		$result = $validateCls->verify(array($key => $val), array($key => $rule), $this->easyRules->getInvalidMessage());
		if ($result === true) {
			$this->fields->$key = $func($val);
			return $this;
		}
		$error = $validateCls->getErrors();
		$this->_response = Response::FailValidate('validate', array('_result' => $error['msg'], 'fields' => array('field' => $error['field'], 'message' => $error['msg'])));
		return $this;
	}

	/**
	 * 检查数据是否已经存在,并返回一行，只能根据主键查询
	 *
	 * @return array
	 */
	final public function _exist()
	{
		$this->checkInstance();
		if (!$this->privateKeys) {
			$this->_response = Response::FAIL('privateKey', \Qii::i(1513));
			return $this->_response;
		}
		$where = array();
		foreach ($this->privateKeys AS $key) {
			$rule = $this->getValidateField($key);
			if (count($rule) > 0 && !$this->validateFields(array($key => $rule))) {
				return $this->_response;
			}
			if ($this->fields->isField($key)) $where[] = "`{$key}` = '" . $this->db->setQuote($this->fields->getField($key)) . "'";
		}
		$result = $this->db->limit(1)->where(join(' AND ', $where))->select($this->getTableName());
		if(!$result)
		{
			$result = array();
		}
		if ($this->db->isError()) {
			$this->_response = $this->db->getResponse();
		}
		return $this->_response = Response::Success('_exist', array('_result' => $result));
	}

	/**
	 * 获取主键对应的值
	 * @return array
	 */
	final protected function getPrivateValue()
	{
		$data = array();
		foreach ($this->privateKeys AS $key) {
			$data[$key] = $this->fields->getField($key);
		}
		return $data;
	}

	/**
	 * 保存数据
	 *
	 * @return string 如果是自动增长的行返回插入数据的id
	 */
	final public function _save()
	{
		$this->checkInstance();
		if (!$this->validateFields($this->easyRules->getRulesByOperate('save'))) return $this->_response;
		$this->resetPrivateKey('save');
		if ($this->privateKeys && count($this->_exist()->getResult()) > 0) {
			$this->_response = Response::Exist('_save', array('_result' => \Qii::i(1511, join(',', $this->getPrivateValue()))));
			return $this->_response;
		}
		$result = $this->db->insertObject($this->getTableName(), $this->fields->getValues());
		if ($this->db->isError()) {
			return $this->db->getResponse();
		}
		return $this->_response = Response::Success('_save', array_merge($this->fields->getValueAsArray(), array('_result' => $result)));
	}

	/**
	 * 更新数据
	 *
	 * @return int  更新数据影响的行数
	 */
	final public function _update()
	{
		$this->checkInstance();
		if (!$this->validateFields($this->easyRules->getRulesByOperate('update'))) return $this->_response;
		$this->resetPrivateKey('update');
		if (count($this->_exist()) == 0) {
			return $this->_response = Response::NotExist('_update', \Qii::i(1512, join(',', $this->getPrivateValue())));
		}
		$result = $this->db->updateObject($this->getTableName(), $this->fields->getValues(), $this->privateKeys);
		if ($this->db->isError()) {
			return $this->_response = $this->db->getResponse();
		}
		return $this->_response = Response::Success('_update', array('_result' => $result));
	}

	/**
	 * 删除数据
	 * @return int 删除数据影响的行数
	 */
	final public function _remove()
	{
		$this->checkInstance();
		if (!$this->validateFields($this->easyRules->getRulesByOperate('remove'))) return $this->_response;
		$this->resetPrivateKey('remove');
		if (count($this->_exist()) == 0) {
			return $this->_response = Response::NotExist('_remove', \Qii::i(1512, join(',', $this->getPrivateValue())));
		}
		$result = $this->db->deleteObject($this->getTableName(), $this->fields->getValues());
		if ($this->db->isError()) {
			return $this->_response = $this->db->getResponse();
		}
		return $this->_response = Response::Success('_remove', array('_result' => $result));
	}

	/**
	 * 获取操作数据返回的错误
	 */
	final public function getErrors()
	{
		if ($this->_response) {
			if (!$this->_response->isError()) return false;
			return $this->_response;
		}
		return $this->db->getError();
	}

	/**
	 * 是否有错误
	 */
	final public function isError()
	{
		if ($this->_response) {
			return $this->_response->isError();
		}
		return $this->db->isError();
	}

	/**
	 * 获取response对象
	 */
	final public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * 使用此方法用于查询某一条数据的某一个字段
	 * @useage getXxxByXxx
	 *     getNameById：通过id获取name的值； getEmailById：通过id获取email；
	 *     _getRowById:返回所有字段; _getRowsById:通过id获取所有匹配的数据
	 * 备注：以_开头的才会去走getRow, getRows方法去取所有字段,目前仅支持All, Row, Rows这几个方法
	 * @param  String $method [description]
	 * @param  Mix $args 请求的参数
	 * @return Mix         [description]
	 */
	final public function __call($method, $args)
	{
		$this->checkInstance();
		$selectType = 'normal';
		if (substr($method, 0, 1) == '_') {
			$selectType = 'system';
			$method = substr($method, 1);
		}
		preg_match('/^(get)(.*)(By)(.*)/', $method, $matches);

		if ($matches && count($matches) == 5 && $matches[1] == 'get') {
			//大写字母匹配下划线，字段中统一用小写字母，在查询的时候使用驼峰结构
			//如：getEmailAddressByUserId 通过user_id查询email_address
			$field = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $matches[2]));

			if (isset($this->_relationMap[$field])) $field = $this->_relationMap[$field];
			$method = 'selectRow';
			if ($field == 'rows' && $selectType == 'system') $method = 'selectRows';
			if ($field == 'row' && $selectType == 'system') $method = 'selectRow';
			if (in_array($field, array('all', 'row', 'rows')) && $selectType == 'system') {
				$field = '*';
			}
			$value = $this->db->setQuote(array_shift($args));
			$name = strtolower(strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $matches[4])));
			$whereArray = array();
			if ($selectType == 'system' && $name == 'fields') {
				foreach ($value AS $val) {
					$whereArray[$val] = $this->fields->isField($val) ? $this->fields->getField($val) : '';
				}
			} else {
				if (!$this->fields->isField($name)) $this->fields->$name = $value;
				$whereArray[$name] = $this->fields->getField($name);
			}
			foreach ($whereArray AS $key => $val) {
				$rule = $this->getValidateField($key);
				if (count($rule) > 0 && !$this->validateFields(array($key => $rule))) {
					return $this->_response;
				}
			}
			$result = $this->db->fields($field)->whereArray($whereArray)->$method($this->getTableName());
			if ($this->db->isError()) {
				return $this->_response = $this->db->getResponse();
			}
			return $this->_response = Response::Success($method, array('_result' => $result));
		}
		//exec{$method}
		preg_match('/^(exec)(.*)/', $method, $matches);

		if ($matches && count($matches) == 3) {
			$alias = lcfirst($matches[2]);
			if (method_exists($this->db, $alias)) {
				$result = $this->db->{$matches[2]}($this->getTableName(), $this->getFields());
				if ($this->db->isError()) {
					return $this->_response = $this->db->getResponse();
				}
				return $this->_response = Response::Success($matches[2], array('_result' => $result));
			}
			if ($this->db->getAlias($method) && method_exists($this->db, $this->db->getAlias($method))) {
				$this->db->whereArray($this->getFields());
				$result = $this->db->{$this->db->getAlias($method)}($this->getTableName());
				if ($this->db->isError()) {
					return $this->_response = $this->db->getResponse();
				}
				return $this->_response = Response::Success($this->db->getAlias($method), array('_result' => $result));
			}
		}
		//访问方法的别名
		if ($this->db->getAlias($method)) {
			if (method_exists($this->db, $this->db->getAlias($method))) {
				$result = call_user_func_array(array($this->db, $this->db->getAlias($method)), $args);
				if ($this->db->isError()) {
					return $this->_response = $this->db->getResponse();
				}
				return $this->_response = Response::Success($this->db->getAlias($method), array('_result' => $result));
			}
			$this->_response = Response::UndefinedMethod('__call', $this->db->getAlias($method));
			\Qii::setError(false, __LINE__, 1106, 'Model', 'Alias ' . $this->db->getAlias($method) . ' does not exist.', print_r($args, true));
		}
		$this->_response = Response::UndefinedMethod('__call', $method);
		\Qii::setError(false, __LINE__, 1106, 'Model', $method, print_r($args, true));
	}

	/**
	 * 获取单个字段的验证规则
	 * @param  String $fieldName 字段名
	 * @return Array
	 */
	protected function getValidateField($fieldName)
	{
		return $this->easyRules->getRulesByField($fieldName);
	}
}