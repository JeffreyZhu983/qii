<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: interface.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 数据库接口类
 * 
 */
interface modelIterface
{
	public function __construct();
	/**
	 * 执行SQL前检查是读/写
	 *
	 * @param String $sql
	 * @return String READ/WRITE
	 */
	public function prepare($sql);//预处理mysql，确认是读/写
	public function initExec($readOrWrite = "READ");//初始 读/写 化链接
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
class modelBase
{
	public $cache;
	public $language;
	protected $_query = array(
					"INSERT" => "INSERT INTO %s(%s) VALUES('%s')",
					"REPLACE" => "REPLACE %s (%s) VALUES('%s')",
					"SELECT" => "SELECT %s FROM %s",
					"UPDATE" => "UPDATE %s SET ",
					"DELETE" => "DELETE FROM %s %s",
					"WHERE" => " WHERE %s",
					"ORDER" => " ORDER BY %s %s",
					"GROUP" => " GROUP BY %s",
					"LIMIT" => " LIMIT %d, %d"
	);
	private $query;
	private $setArray = array();
	public $modelSQL = "";
	/**
	 * 设置Cache
	 *
	 * @param String $cache
	 * @param Array $policy
	 */
	public function setCache($cache, $policy)
	{
		Qii::requireOnce(Qii_DIR . DS . 'core' . DS . '_Cache.php');
		$this->cache = Qii::instance('_Cache', $cache)->initialization($policy);//载入cache类文件
	}
	/**
	 * 缓存内容
	 *
	 * @param String $id
	 * @param Array $value
	 * @return Bool
	 */
	public function cache($id, $value)
	{
		return $this->cache->set($id, $value);
	}
	/**
	 * 获取Cache
	 *
	 * @param String $id
	 * @return Array
	 */
	public function getCache($id)
	{
		return $this->cache->get($id);
	}
	/**
	 * 获取表的名称
	 * @param String $table
	 * @return String
	 */
	public function getTable($table)
	{
		return $table;
	}
	public function setLanguage()
	{
		$this->language = Qii::instance('Language');
	}
	final function __set($name, $value)
	{
		$this->query[$name] = $value;
	}
	final function __get($name)
	{
		if(isset($this->query[$name])) return $this->query[$name];
	}
	final function __unset($name)
	{
		if(isset($this->query[$name]))
		{
			$this->query[$name] = '';
			unset($this->query[$name]);
		}
	}
	/**
	 * 
	 * Insert Object
	 * @param String $table
	 * @param Array|Object $dataArray
	 */
	final function insertObject($table, $dataArray)
	{
		if(empty($table))
		{
			return -1;
		}
		if(sizeof($dataArray) > 0 || get_object_vars($dataArray) > 0)
		{
			$keys = array();
			$values = array();
			foreach($dataArray AS $key => $value)
			{
				$keys[] = $key;
				$values[] = $this->setQuote($value);
			}
			$this->modelSQL = $sql = "INSERT INTO {$table}(`" . join("`, `", $keys) . "`) VALUES('" . join("', '", $values) . "')";
			$rs = $this->setQuery($sql);
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
		if(empty($table))
		{
			return -1;
		}
		if(sizeof($dataArray) > 0 || get_object_vars($dataArray) > 0)
		{
			$keys = array();
			$values = array();
			foreach($dataArray AS $key => $value)
			{
				$keys[] = $key;
				$values[] = $this->setQuote($value);
			}
			$this->modelSQL = $sql = "REPLACE INTO {$table}(`" . join("`, `", $keys) . "`) VALUES('" . join("', '", $values) . "')";
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
		if(empty($table))
		{
			return -1;
		}
		if(sizeof($dataArray) > 0 || get_object_vars($dataArray) > 0)
		{
			$values = array();
			$where = array();
			foreach ($dataArray AS $key => $value)
			{
				$value = $this->setQuote($value);
				if(in_array($key, $keys))
				{
					$where[] = "`{$key}` = '" . $value . "'";
				}
				else 
				{
					$values[] = "`{$key}` = '" . $value . "'";
				}
			}
			$this->modelSQL = $sql = "UPDATE {$table} SET " . join(", ", $values) . (sizeof($where) > 0 ? " WHERE " . join(" AND ", $where) : '');
			$rs = $this->setQuery($sql);
			$this->cleanData();
			$this->setError();
			return $this->AffectedRows($rs);
			//return $rs->rowCount();
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
		if(empty($table))
		{
			return -1;
		}
		$where = array();
		if(sizeof($keys) > 0 || get_object_vars($keys))
		{
			foreach($keys AS $k => $v)
			{
				$where[] = "`{$k}` = '" . $this->setQuote($v) . "'";
			}
		}
		$this->modelSQL = $sql = "DELETE FROM {$table}" . (sizeof($where) > 0 ? " WHERE " . join(" AND ", $where) : '');
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
		foreach($array AS $k)
		{
			if(is_array($this->$k))
			{
				$this->$k = array();
			}
			else 
			{
				unset($this->$k);
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
		unset($this->fields);
		if(empty($fileds)) $fileds = "*";
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
		unset($this->groupBy);
		if(!empty($fields))
		{
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
		unset($this->fileds);
		if(is_array($array))
		{
			$tmpArray = array();
			foreach ($array AS $k => $v)
			{
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
		unset($this->orderBy);
		if(!empty($field))
		{
			$this->orderBy = sprintf($this->_query['ORDER'], $field, $orderBy);
		}
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
		unset($this->limit);
		if($limit !== '')
		{
			if(!$offset)
			{
				$this->limit = sprintf($this->_query["LIMIT"], 0, $limit);
			}
			else 
			{
				$this->limit = sprintf($this->_query["LIMIT"], $limit, $offset);
			}
		}
		return $this;
	}
	final function whereArray($where)
	{
		unset($this->where);
		if(!empty($where))
		{
			$whereArray = array();
			foreach($where AS $k => $v)
			{
				$whereArray[] = " `{$k}` = '{$v}'";
			}
			if(sizeof($whereArray) > 0)
			{
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
		unset($this->where);
		if(!empty($where))
		{
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
	 * 查詢一列
	 * @param String $table
	 */
	final function selectOne($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['SELECT'], ((trim($this->fields) != '') ? $this->fields : "*"), $table) . $this->where . $this->groupBy . $this->orderBy . $this->limit;
		$this->cleanData();
		return $this->getOne($sql);
	}
	/**
	 * 
	 * 查询所有
	 * @param String $table
	 */
	final function selectAll($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['SELECT'], ((trim($this->fields) != '') ? $this->fields : "*"), $table) . $this->where . $this->groupBy . $this->orderBy . $this->limit;
		$this->cleanData();
		return $this->getAll($sql);
	}
	final function downsetCounter($data)
	{
		if(is_array($data))
		{
			foreach($data AS $k => $value)
			{
				$this->setArray[] = $k . "=" . $k . '-'. $value;
			}
		}
		return $this;
	}
	final function upsetCounter($data)
	{
		if(is_array($data))
		{
			foreach($data AS $k => $value)
			{
				$this->setArray[] = $k . "=" . $k . '+'. $value;
			}
		}
		return $this;
	}
	/**
	 * 
	 * 更新数据时候用
	 * @param Array $data
	 * @return $this
	 */
	final function setData($data)
	{
		if(is_array($data))
		{
			$set = array();
			foreach($data AS $k => $value)
			{
				$set[] = $k . "='" . $this->setQuote($value) . "'";
			}
			if(sizeof($this->setArray) > 0)
			{
				$this->set = " " . join(", ", $set) . ", ". join(",", $this->setArray);
			}
			else
			{
				$this->set = " " . join(", ", $set);
			}
		}
		else 
		{
			if(sizeof($this->setArray) > 0)
			{
				$this->set = join(",", $this->setArray);
			}
			else
			{
				$this->set = "";
			}
		}
		return $this;
	}
	/**
	 * 
	 * 执行更新操作
	 * @param $table
	 * @return Int 返回影响的行数
	 */
	final function updateRows($table)
	{
		$this->modelSQL = $sql = sprintf($this->_query['UPDATE'], $table) . $this->set .  $this->where . $this->limit;
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
		if(in_array($option, $allow))
		{
			return $this->{$option};
		}
		return 0;
	}
	public function setQuote($word)//过滤sql字符
	{
		if(ini_get("magic_quotes_gpc"))
		{
			return $word;
		}
		return is_array($word) ? array_map('addslashes', $word) : addslashes($word);
	}
	/**
	 * 如果不存在指定的方法则调用提示错误
	 *
	 * @param String $name
	 * @param Mix $args
	 * @return Mix
	 */
	public function __call($name, $args)
	{
		Qii::setError(false, 106, array('Model', $name, print_r($args, true)));
	}
}
?>