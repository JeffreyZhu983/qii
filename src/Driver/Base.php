<?php
namespace Qii\Driver;

class Base
{
	const VERSION = '1.2';
	public $_cache;
	public $language;
	protected $_query = array(
		"INSERT" => "INSERT INTO %s(%s) VALUES('%s')",
		"REPLACE" => "REPLACE %s (%s) VALUES('%s')",
		"SELECT" => "SELECT %s FROM %s",
		"UPDATE" => "UPDATE %s SET ",
		"DELETE" => "DELETE FROM %s %s",
		"WHERE" => " WHERE %s",
		"LIKE" => " `%s` LIKE '%s'",
		"ORDER" => " ORDER BY %s %s",
		"GROUP" => " GROUP BY %s",
		"LIMIT" => " LIMIT %d, %d"
	);
	private $query;
	private $setArray = array();
	public $modelSQL = "";
	protected $fields;
	protected $where;
	protected $groupBy;
	protected $limit;
	protected $orderBy;
	public $load;
	/**
	 * @var string $_response Response对象
	 */
	protected $_response;

	//方法对应的别名
	protected $_modelAlias = array('selectRows' => 'selectAll', 'select' => 'selectRow', 'getOne' => 'selectOne', 'getRow' => 'selectRow', 'getAll' => 'selectAll', 'remove' => 'deleteRows');

	public function __construct()
	{
		$this->language = \Qii\Autoloader\Psr4::getInstance()->loadClass('Qii\Language\Loader');
		$this->load = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Autoloader\Loader');
		$this->_response = new \Qii\Driver\Response();
	}
	/**
	 * 获取数据库中所有的数据表
	 * @return array
	 */
	public function getAllDatabases()
	{
		$sql = "SHOW DATABASES";
		$rs = $this->setQuery($sql);
		
		$database = array();
		while($row = $rs->fetch())
		{
			$database[] = $row['Database'];
		}
		return $database;
	}
	/**
	 * 数据库中是否包含指定库
	 * @param string $database 数据库名
	 * @return bool
	 */
	public function hasDatabase($database)
	{
		if(!$database) return false;
		$sql = "SHOW DATABASES LIKE '". $database ."'";
		$rs = $this->setQuery($sql);
		while($row = $rs->fetch())
		{
			$val = array_values($row);
			if(in_array($database, $val))
			{
				return true;
			}
		}
		return false;
	}
	/**
	 * 获取当前数据库中所有的表
	 * @param null|string $database 数据库
	 * @return array
	 */
	public function getAllTables($database = null)
	{
		if($database)
		{
			$this->setQuery('USE '. $database);
		}
		$sql = "SHOW TABLES";
		$rs = $this->setQuery($sql);
		$tables = array();
		while($row = $rs->fetch())
		{
            if(is_array($row)) {
                foreach($row AS $val) {
                    $tables[] = $val;
                }
            }
		}
		return $tables;
	}
	/**
	 * 获取指定数据表的所有字段
	 * @param string $table 表名
	 * @param string $database 数据库名 
	 * @return array
	 */
	public function getTableInfo($table, $database = null)
	{
		if(!$database) $database = $this->currentDB;
		$sql = "SELECT * from information_schema.COLUMNS where table_name = '".$table."' and table_schema = '".$database."'";
		$data = ['fields' => [], 
					'rules' => [
						'pri' => [], 'required' => []
					]
				];
		
		$rs = $this->setQuery($sql);
		while($row = $rs->fetch())
		{
			$data['fields'][] = $row['COLUMN_NAME'];
			if($row['EXTRA'])
			{
				$data['rules']['extra'][$row['EXTRA']][] = $row['COLUMN_NAME'];
			}
			if($row['COLUMN_KEY'] == 'PRI') 
			{
				$data['rules']['pri'][] = $row['COLUMN_NAME'];
				$data['rules']['required'][] = $row['COLUMN_NAME'];
			}
			if($row['IS_NULLABLE'] == 'NO')
			{
				$data['rules']['required'][] = $row['COLUMN_NAME'];
			}
			if(in_array($row['DATA_TYPE'], ['varchar', 'char']))
			{
				$data['rules']['maxlength'][$row['COLUMN_NAME']] = $row['CHARACTER_MAXIMUM_LENGTH'];
			}
			if(in_array($row['DATA_TYPE'], ['mediumtext', 'TINYTEXT', 'text', 'longtext']))
			{
				$data['rules']['text'][] = $row['COLUMN_NAME'];
			}
			if(in_array($row['DATA_TYPE'], ['bigint', 'int', 'smallint', 'tinyint', 'integer']))
			{
				preg_match('/[\d]{1,}/', $row['COLUMN_TYPE'], $matches);
				$data['rules']['int'][$row['COLUMN_NAME']] = $matches[0];
				$data['rules']['number'][] = $row['COLUMN_NAME'];
			}
			if(in_array($row['DATA_TYPE'], ['float', 'double', 'decimal']))
			{
				$data['rules']['float'][$row['COLUMN_NAME']] = $this->getValueFromBrackets($row['COLUMN_TYPE']);
			}
			if(in_array($row['DATA_TYPE'], ['timestamp', 'datatime']))
			{
				$data['rules']['timestamp'][] = $row['COLUMN_NAME'];
			}
			if(in_array($row['DATA_TYPE'], ['enum', 'set']))
			{
				$data['rules']['sets'][$row['COLUMN_NAME']] = $this->getValueFromBrackets($row['COLUMN_TYPE']);
			}
			if(isset($row['COLUMN_DEFAULT']))
			{
				$data['rules']['default'][$row['COLUMN_NAME']] = $row['COLUMN_DEFAULT'];
			}
		}
		$data['sql'] = $this->getTableSQL($table, $database);
		return $data;
	}
	/**
	 * 从括号中获取指定的值
	 * @param string $str 需要获取的内容
	 * @return array
	 */
	public function getValueFromBrackets($str)
	{
		preg_match("/(?:\()(.*)(?:\))/i", $str, $matches);
		$str = $matches[1];
		$a = explode(",", $str);
		for($i=0; $i< count($a); $i++)
		{
			$this->removeQuote($a[$i]);//从字符串中去除单引号
		}
		return $a;
	}
	/**
	 * 去除双引号
	 * @param string $str 去除双引号 
	 */
	public function removeQuote(&$str) 
	{
		if(preg_match("/^\'/",$str))
		{
			$str = substr($str, 1, strlen($str)-1);
		}
		if(preg_match("/\'$/",$str))
		{
			$str = substr($str, 0, strlen($str)-1);
		}
		return $str;
	}
	/**
	 * 查询数据库中是否有指定的表
	 * @param string $tableName 表名
	 * @param null|string $database 数据库
	 * @return bool
	 */
	public function hasTable($tableName, $database = null)
	{
		if($database)
		{
			$this->setQuery('USE '. $database);
		}
		$sql = "SHOW TABLES LIKE '".$tableName."'";
		$rs = $this->setQuery($sql);

		$tables = array();
		while($row = $this->fetch($rs))
		{
            if(is_array($row)) {
                foreach($row AS $val) {
                    if($val == $tableName) return true;
                }
            }
		}
		return false;
	}
	/**
	 * 获取创建表的SQL语句
	 * @param string $tableName 表名
	 * @param null|string $database 数据库
	 * @param int|null $autoIncr 自增长的值，null的话就不使用
	 * @return string
	 */
	public function getTableSQL($tableName, $database = null, $autoIncr = null)
	{
		if($database)
		{
			$this->setQuery('USE '. $database);
		}
		$row = $this->getRow("SHOW CREATE TABLE `".$tableName."`");
		if(!$row) {
			throw new \Exception('数据表不存在', __LINE__);
		}
		$sql = $row['Create Table'];
		if($autoIncr === null) {
			return $sql;
		}
		return preg_replace("/AUTO_INCREMENT=[\d]{1,}/", "AUTO_INCREMENT=". intval($autoIncr), $sql);
	}
	/**
	 * 通过数据表名称获取规则
	 * @param string $table 表名
	 * @param string $database 数据库名 
	 */
	public function buildRulesForTable($table, $database = null)
	{
		if(!$database) $database = $this->currentDB;
		$tableInfo = $this->getTableInfo($table, $database);
		$rules = new \Qii\Base\Rules();
		$rules->addFields($tableInfo['fields']);
		if($tableInfo['rules']['required'])
		{
			$rules->addForceValidKey($tableInfo['rules']['required']);
		}
		if(isset($tableInfo['rules']['number']))
		{
			foreach ($tableInfo['rules']['number'] as $key => $value) 
			{
				$rules->addRules($value, 'number', true, $value . '字段必须是数字');
			}
		}
		if(isset($tableInfo['rules']['maxlength']))
		{
			foreach ($tableInfo['rules']['maxlength'] as $key => $value) 
			{
				$rules->addRules($key, 'maxlength', $value, $key . '字段内容长度不能大于'. $value .'个字符');
			}
		}
		if(isset($tableInfo['rules']['timestamp']))
		{
			foreach ($tableInfo['rules']['timestamp'] as $key => $value) 
			{
				$rules->addRules($value, 'datetime', true, $value . '字段必须为日期格式');
			}
		}
		return $rules;
	}
	
	final public function getAlias($alias)
	{
		return isset($this->_modelAlias[$alias]) ? $this->_modelAlias[$alias] : null;
	}

	/**
	 * 设置Cache
	 *
	 * @param String $cache
	 * @param Array $policy
	 */
	final public function setCache($cache, $policy)
	{
		\Qii\Autoloader\Import::requires(Qii_DIR . DS . 'Qii' . DS . 'Cache.php');
		$this->_cache = \Qii\Autoloader\Psr4::loadClass('\Qii\Cache', $cache)->initialization($policy);//载入cache类文件
	}

	/**
	 * 缓存内容
	 *
	 * @param String $id
	 * @param Array $value
	 * @return Bool
	 */
	final public function cache($id, $value)
	{
		return $this->_cache->set($id, $value);
	}

	/**
	 * 获取缓存的类
	 */
	final public function getCache()
	{
		return $this->_cache;
	}

	/**
	 * 获取缓存内容
	 *
	 * @param String $id
	 * @return Array
	 */
	final public function getCacheData($id)
	{
		return $this->_cache->get($id);
	}

	/**
	 * 获取表的名称
	 * @param String $table
	 * @return String
	 */
	public function getTable($table)
	{
		list($database, $tableName) = array_pad(explode('.', $table), 2, '');
		if($tableName) return "`{$database}`.`{$tableName}`";
		return $table;
	}

	public function setLanguage()
	{
		$this->language = \Qii\Autoloader\Psr4::loadClass('Qii_Language_Loader');
	}

	/**
	 *
	 * Insert Object
	 * @param String $table
	 * @param Array|Object $dataArray
	 */
	final function insertObject($table, $dataArray)
	{
		if (empty($table)) {
			return -1;
		}
		if (sizeof($dataArray) > 0 || (is_object($dataArray) && get_object_vars($dataArray)) > 0) {
			$keys = array();
			$values = array();
			foreach ($dataArray AS $key => $value) {
				$keys[] = $key;
				if(is_array($value))
				{
					throw new \Qii\Exceptions\InvalidFormat(_i('Invalid %s format', $key), __LINE__);
				}
				$values[] = $this->setQuote($value);
			}

			$this->modelSQL = $sql = "INSERT INTO ". $this->getTable($table) ."(`" . join("`, `", $keys) . "`) VALUES('" . join("', '", $values) . "')";
			$this->setQuery($sql);
			$this->cleanData();
			$this->setError();
			return $this->lastInsertId();
		}
		return -2;
	}

	/**
	 *
	 * Replace Object
	 * @param String $table
	 * @param Array|Object $dataArray
	 */
	final function replaceObject($table, $dataArray)
	{
		if (empty($table)) {
			return -1;
		}
		if (sizeof($dataArray) > 0 || get_object_vars($dataArray) > 0) {
			$keys = array();
			$values = array();
			foreach ($dataArray AS $key => $value) {
				$keys[] = $key;
				if(is_array($value))
				{
					throw new \Qii\Exceptions\InvalidFormat(_i('Invalid %s format', $key), __LINE__);
				}
				$values[] = $this->setQuote($value);
			}
			$this->modelSQL = $sql = "REPLACE INTO ". $this->getTable($table) ."(`" . join("`, `", $keys) . "`) VALUES('" . join("', '", $values) . "')";
			$rs = $this->setQuery($sql);
			$this->cleanData();
			$this->setError();
			return $this->AffectedRows($rs);
		}
		return -2;
	}

	/**
	 *
	 * Update data
	 * @param String $table
	 * @param Array|Objct $dataArray
	 * @param Array $keys
	 */
	final function updateObject($table, $dataArray, $keys = array())
	{
		if (empty($table) || !is_array($keys)) {
			return -1;
		}
		if (sizeof($dataArray) > 0 || get_object_vars($dataArray) > 0) {
			$values = array();
			$where = array();
			foreach ($dataArray AS $key => $value) {
				if(is_array($value))
				{
					throw new \Qii\Exceptions\InvalidFormat(_i('Invalid %s format', $key), __LINE__);
				}
				$value = $this->setQuote($value);
				if (in_array($key, $keys)) {
					$where[] = "`{$key}` = '" . $value . "'";
				} else {
					$values[] = "`{$key}` = '" . $value . "'";
				}
			}
			//$keys为key => value的方式，就直接用keys
			if (empty($where) && count($keys) > 0) {
				foreach ($keys as $key => $value) {
					$value = $this->setQuote($value);
					$where[] = "`{$key}` = '" . $value . "'";
				}
			}
			$this->modelSQL = $sql = "UPDATE ". $this->getTable($table) ." SET " . join(", ", $values) . (sizeof($where) > 0 ? " WHERE " . join(" AND ", $where) : '');
			$rs = $this->setQuery($sql);
			$this->cleanData();
			$this->setError();
			return $this->AffectedRows($rs);
		}
		return 0;
	}

	/**
	 *
	 * 删除数据
	 * @param String $table
	 * @param Array $keys
	 */
	final function deleteObject($table, $keys = array())
	{
		if (empty($table)) {
			return -1;
		}
		$where = array();
		if (sizeof($keys) > 0 || get_object_vars($keys)) {
			foreach ($keys AS $k => $v) {
				$where[] = "`{$k}` = '" . $this->setQuote($v) . "'";
			}
		}
		$this->modelSQL = $sql = "DELETE FROM ". $this->getTable($table) ." " . (sizeof($where) > 0 ? " WHERE " . join(" AND ", $where) : '');
		$rs = $this->query($sql);
		$this->cleanData();
		$this->setError();
		return $this->AffectedRows($rs);
	}

	/**
	 * 需要清除的数据
	 *
	 * @return Array
	 */
	final function cleanOptions()
	{
		return array('fields', 'where', 'groupBy', 'orderBy', 'limit', 'setArray');
	}

	/**
	 * 清除数据
	 *
	 */
	final function cleanData()
	{
		$array = $this->cleanOptions();
		foreach ($array AS $k) {
			if (is_array($this->$k)) {
				$this->$k = array();
			} else {
				$this->$k = null;
			}
		}
	}

	/**
	 *
	 * 查询的字段
	 * @param String $fileds
	 */
	final function fields($fileds = "*")
	{
		$this->fields = null;
		if (empty($fileds)) $fileds = "*";
		if (is_array($fileds)) $fileds = join(',', $fileds);
		$this->fields = $fileds;
		return $this;
	}

	/**
	 *
	 * GROUP BY方法
	 * @param String $fields
	 */
	final function groupBy($fields)
	{
		$this->groupBy = null;
		if (!empty($fields)) {
			$this->groupBy = sprintf($this->_query['GROUP'], $fields);
		}
		return $this;
	}

	/**
	 *
	 * 插入数据用
	 * @param Array $array
	 */
	final function dataArray($array)
	{
		$this->fileds = null;
		if (is_array($array)) {
			$tmpArray = array();
			foreach ($array AS $k => $v) {
				$tmpArray['k'][] = $k;
				$tmpArray['v'][] = $this->setQuote($v);
			}
			$this->fileds = $tmpArray;
		}
		return $this;
	}

	/**
	 *
	 * Order By函数
	 * @param String $field
	 * @param String $orderBy
	 */
	final function orderBy($field, $orderBy)
	{
		if(!empty($field)) return $this;

		if($this->orderBy != '') {
			$this->orderBy .= $field .' '. $orderBy;
		}else{
			$this->orderBy = sprintf($this->_query['ORDER'], $field, $orderBy);
		}
	}
	
	final function orderByArr($map)
	{
		if(empty($map)) return $this;
		foreach($map AS $val)
		{
			$this->orderBy($val['field'], $val['orderBy']);
		}
		return $this;
	}

	final function orderByStr($orderBy)
	{
		if(!$orderBy) return $this;
		$this->orderBy = $orderBy;
		return $this;
	}

	/**
	 *
	 * Limit函数，如果省略第二个参数则第一个为0，第二个参数值取第一个
	 * @param Int $limit
	 * @param Int $offset
	 */
	final function limit($limit, $offset = 0)
	{
		$this->limit = null;
		if ($limit !== '') {
			if (!$offset) {
				$this->limit = sprintf($this->_query["LIMIT"], 0, $limit);
			} else {
				$this->limit = sprintf($this->_query["LIMIT"], $limit, $offset);
			}
		}
		return $this;
	}

	final function like($like)
	{
		if(empty($like)) return $this;
		$likeArray = array();
		if($like && !is_array($like))
		{
			$likeArray[] = $like;
		}
		else
		{
			foreach($like AS $key => $val)
			{
				$likeArray[] = sprintf($this->_query['LIKE'], $key, "%". $this->setQuote($val) . "%");
			}
		}
		if(count($likeArray) > 0)
		{
			$likeSQL = join(" OR ", $likeArray);
			echo $likeSQL;
			$this->where = sprintf($this->_query["WHERE"], $likeSQL);
		}
		return $this;
	}

	/**
	 * 传的条件为数组
	 * @param  Array $where 条件
	 * @return Object       对象本身
	 */
	final function whereArray($where)
	{
		$this->where = null;
		if (!empty($where)) {
			$whereArray = array();
			foreach ($where AS $k => $v) {
				$whereArray[] = " `{$k}` = '{$v}'";
			}
			if (sizeof($whereArray) > 0) {
				$whereSQL = join(" AND ", $whereArray);
				$this->where = sprintf($this->_query["WHERE"], $whereSQL);
			}
		}
		return $this;
	}

	/**
	 *
	 * WHERE 子句
	 * @param String $where
	 */
	final function where($where)
	{
		if (is_array($where)) return $this->whereArray($where);
		$this->where = null;
		if (!empty($where)) {
			$this->where = sprintf($this->_query["WHERE"], $where);
		}
		return $this;
	}

	/**
	 *
	 * 插入数据到指定表中
	 * @param String $table
	 */
	final function insertRow($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['INSERT'], $table, join(",", $this->fileds['k']), join("', '", $this->fileds['v']));
		$this->cleanData();
		$this->setQuery($sql, '');
		return $this->lastInsertId();
	}

	/**
	 *
	 * Replace方法
	 * @param String $table
	 */
	final function replaceRow($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['REPLACE'], $table, join(",", $this->fileds['k']), join("', '", $this->fileds['v']));
		$this->cleanData();
		return $this->exec($sql);
	}

	/**
	 *
	 * 查询一行
	 * @param String $table
	 */
	final function selectRow($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['SELECT'], ((trim($this->fields) != '') ? $this->fields : "*"), $table) . $this->where . $this->groupBy . $this->orderBy . $this->limit;
		$this->cleanData();
		return $this->getRow($sql);
	}

	/**
	 *
	 * 查询一行
	 * @param String $table
	 */
	final function selectOne($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['SELECT'], ((trim($this->fields) != '') ? $this->fields : "*"), $table) . $this->where . $this->groupBy . $this->orderBy . $this->limit;
		$this->cleanData();
		return $this->getOne($sql);
	}
	/**
	 * 创建SQL
	 */
	final function createSQL($table)
	{
		$sql = sprintf($this->_query['SELECT'], ((trim($this->fields) != '') ? $this->fields : "*"), $table) . $this->where . $this->groupBy . $this->orderBy . $this->limit;
		$this->cleanData();
		return $sql;
	}
	/**
	 *
	 * 查询所有
	 * @param String $table 数据表名称
	 * @return Array
	 */
	final function selectAll($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['SELECT'], ((trim($this->fields) != '') ? $this->fields : "*"), $table) . $this->where . $this->groupBy . $this->orderBy . $this->limit;
		$this->cleanData();
		return $this->getAll($sql);
	}
	/**
	 * 返回resource资源
	 * @param string $table 数据表名称
	*/
	final function rs($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['SELECT'], ((trim($this->fields) != '') ? $this->fields : "*"), $table) . $this->where . $this->groupBy . $this->orderBy . $this->limit;
		$this->cleanData();
		return $this->setQuery($sql);
	}

	/**
	 * 将指定字段减指定值
	 *
	 * @param array $data 数据
	 * @return $this
	 */
	final function downsetCounter($data)
	{
		if (is_array($data)) {
			foreach ($data AS $k => $value) {
				$this->setArray[] = $k . "=" . $k . '-' . $value;
			}
		}
		$this->set(null);
		return $this;
	}

	/**
	 * 将指定字段加指定值
	 *
	 * @param $data
	 * @return $this
	 */
	final function upsetCounter($data)
	{
		if (is_array($data)) {
			foreach ($data AS $k => $value) {
				$this->setArray[] = $k . "=" . $k . '+' . $value;
			}
		}
		$this->set(null);
		return $this;
	}

	/**
	 * 更新数据时候用，方法同setData
	 * @param Array $data
	 */
	final function set($data)
	{
		return $this->setData($data);
	}

	/**
	 *
	 * 更新数据时候用
	 * @param Array $data
	 * @return $this
	 */
	final function setData($data)
	{
		if (is_array($data)) {
			$set = array();
			foreach ($data AS $k => $value) {
				$set[] = $k . "='" . $this->setQuote($value) . "'";
			}
			if (sizeof($this->setArray) > 0) {
				$this->set = " " . join(", ", $set) . ", " . join(",", $this->setArray);
			} else {
				$this->set = " " . join(", ", $set);
			}
		} else {
			if (sizeof($this->setArray) > 0) {
				$this->set = join(",", $this->setArray);
			} else {
				$this->set = "";
			}
		}
		return $this;
	}
	/**
	 * 执行更新操作，updateRows的alias
	 * @param String $table
	 * @return number
	 */
	/*
	final function update($table){
		return $this->updateRows($table);
	}
	*/
	/**
	 *
	 * 执行更新操作
	 * @param $table
	 * @return Int 返回影响的行数
	 */
	final function updateRows($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['UPDATE'], $table) . $this->set . $this->where . $this->limit;
		$this->cleanData();
		return $this->exec($sql);
	}

	/**
	 *
	 * 执行删除操作
	 * @param String $table
	 */
	final function deleteRows($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['DELETE'], $table, $this->where) . $this->limit;
		$this->cleanData();
		return $this->exec($sql);
	}

	/**
	 * 执行Model过程中保存的相关信息
	 *
	 * @param String $option
	 * @return Mix
	 */
	final function querySQL($option = '')
	{
		$allow = array('_queryTimes', '_querySeconds', '_errorInfo', '_exeSQL');
		if (in_array($option, $allow)) {
			return $this->{$option};
		}
		return 0;
	}

	/**
	 * 将结果编码一下
	 * @param String $word
	 * @return String|multitype:
	 */
	public function setQuote($word)//过滤sql字符
	{
		if (ini_get("magic_quotes_gpc")) {
			return $word;
		}
		return is_array($word) ? array_map('addslashes', $word) : addslashes($word);
	}
	/**
	 * 获取错误码
	 */
	public function getCode()
	{
		return $this->_response->getCode();
	}
	/**
	 * 获取错误信息
	 */
	public function getMessage()
	{
		if($this->_response->isError())
		{
			return $this->_response->getMessage();
		}
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

	public function iconv($str)
	{
		if (is_array($str)) {
            return array_map(function ($n) {
                return iconv('GB2312', 'UTF-8', $n);
            }, $str);
        }

		return iconv('GB2312', 'UTF-8', $str);
	}
	/**
	 * 如果不存在指定的方法则调用提示错误
	 *
	 * @param String $name
	 * @param Mix $args
	 * @return Mix
	 */
	public function __call($method, $argvs)
	{
		if (isset($this->_modelAlias[$method])) {
			if (method_exists($this, $this->_modelAlias[$method])) {
				return call_user_func_array(array($this, $this->_modelAlias[$method]), $argvs);
			}
			\Qii::setError(false, __LINE__, 1506, 'Alias ' . get_called_class() . '->' . $method . '()');
		}

		\Qii::setError(false, __LINE__, 1506, get_called_class() . '->' . $method . '()');
	}
}