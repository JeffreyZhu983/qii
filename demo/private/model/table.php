<?php
/**
 * 数据表的显示规则
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn> 2015-08-23 10:07
 */
namespace Model;

use \Qii\Model;
use \Qii\Driver\Response;

class table extends Model
{
	public $tablesError;

	public function __construct()
	{
		parent::__construct();
		$this->checkRuleTable();
	}
	public function getRuleTableInfo()
	{
		$table = \Qii\Import::includes('configure/table.rules.config.php');
		$table['database'] = $this->db->useDB;
		return $table;
	}
	public function checkRuleTable()
	{
		$config = $this->getRuleTableInfo();
		if($config['sql'])
		{
			$this->db->exec($config['sql']);
		}
	}

	/**
	 * 获取数据库列表
	 * @return array
	 */
	public function getDatabases()
	{
		$databases = array();
		$sql = "SHOW DATABASES";

		$this->db->setQuery($sql);
		while ($row = $this->db->fetch()) {
			if (!in_array($row['Database'], array('information_schema', 'mysql', 'performance_schema', 'test'))) $databases[] = $row['Database'];
		}
		return $databases;
	}

	public function getTableSQL($db, $table)
	{
		if (!$db || !$table) {
			$this->tablesError = '参数不正确';
			return array('code' => 1, 'msg' => '参数不正确');
		}
		$data = array();
		$data['database'] = $db;
		$data['table'] = $table;
		try {
			$data['code'] = 0;
			$sql = "SHOW CREATE TABLE {$db}.{$table}";
			$this->db->setQuery($sql);
			$row = $this->db->fetch();
			$createTableSQL = preg_replace('/AUTO_INCREMENT\=[\d]/', 'AUTO_INCREMENT=1', $row['Create Table']);
			if ($row) $data['sql'] = str_replace("CREATE TABLE ", "CREATE TABLE IF NOT EXISTS ", $createTableSQL) . ';';
		} catch (Exception $e) {
			$data['code'] = 1;
			$data['msg'] = $e->getMessage();
			$data['sql'] = '';
		}
		return $data;
	}

	/**
	 * 获取指定数据库中的表名
	 * @param string $db
	 * @return array
	 */
	public function getTableLists($db = 'istudy', $table = '')
	{
		if (!$db) {
			$this->tablesError = '参数不正确';
			return array();
		}
		$tables = array();
		$sql = "SHOW TABLES IN " . $db;
		$this->db->setQuery($sql);
		while ($row = $this->db->fetch()) {
			$tableName = $row['Tables_in_' . $db];
			$insert = true;
			if (!empty($table)) $insert = stristr($tableName, $table) ? true : false;
			if ($insert) $tables[] = $tableName;
		}
		return $tables;
	}

	/**
	 * 匹配类型并返回类型字段长度
	 * @param string $type
	 * @return Array
	 */
	public function parseType($type)
	{
		preg_match("/(.*)\(.*?\)/", $type, $matches);
		$data = array();
		$data['source'] = $type;
		$data['type'] = isset($matches[1]) ? $matches[1] : $type;
		$ext = isset($matches[1]) ? str_replace(array($data['type'] . '(', ')'), '', $type) : 0;

		if ($data['type'] == 'enum') {
			$data['length'] = 0;
			$data['sets'] = $ext;
			$data['setsArray'] = preg_replace("/\"|\'/", "", explode(",", $ext));
		} else {
			$data['length'] = (int)$ext;
		}
		return $data;
	}

	/**
	 * 获取数据库中某个表的字段
	 * @param string $db
	 * @param string $table
	 * @return array 表中的字段列表
	 */
	public function getFieldsLists($db = 'istudy', $table)
	{
		$fields = array();
		$sql = 'DESC ' . $db . '.' . $table;
		$this->db->setQuery($sql);
		while ($row = $this->db->fetch()) {
			$val = array();
			$val['field'] = $row['Field'];
			$val['null'] = strtolower($row['Null']);
			$matches = $this->parseType($row['Type']);
			$val['length'] = 0;
			$val['type'] = $row['Type'];
			if (isset($matches['length'])) {
				$val['length'] = intval($matches['length']);
				$val['type'] = $matches['type'];
			}
			if ($matches['type'] == 'enum') {
				$val['sets'] = $matches['sets'];
				$val['setsArray'] = $matches['setsArray'];
			}
			$val['length'] = intval($matches['length']);
			if ($row['Null'] == 'NO') $val['require'] = true;
			if ($row['Key'] != '') {
				if ($row['Key'] == 'PRI') $val['pri'] = true;//主键，用于查询用
				if ($row['Key'] == 'UNI') $val['uni'] = true;
			}
			if ($row['Default']) $val['default'] = $row['Default'];
			if ($row['Extra']) $val['extra'] = $row['Extra'];
			$fields[$row['Field']] = $val;
		}
		return $fields;
	}

	/**
	 * 获取指定数据库、数据表的规则
	 * @param  string $database 数据库
	 * @param  string $table 数据表
	 * @return array  返回表的显示规则
	 */
	public function getRules($database, $table)
	{
		if (!$database || !$table) {
			$this->tablesError = '参数不正确';
			return array();
		}
		$rules = (new \Qii\Driver\Easy())->_initialize();
		$rules->setPrivateKey(array('database', 'table'));
		$rules->setRules(new \Qii\Driver\Rules($this->getRuleTableInfo()));
		$rules->database = $database;
		$rules->table = $table;
		$tableRules = $rules->_exist();
		$tableRule = array();
		$tableRule['rules'] = array();
		if (!$tableRules->isError()) {
			$tableRule = $tableRules->getResult();
			$rule = isset($tableRule['rules']) ? $tableRule['rules'] : '';
			if ($rule != '') {
				$tableRule['rules'] = json_decode($rule, true);
			} else {
				$tableRule['rules'] = array();
			}
		}
		$defaultRules = $this->tableRules($database, $table);
		if ($defaultRules['rules']) $tableRule['rules'] = array_merge($defaultRules['rules'], $tableRule['rules']);
		return $tableRule;
	}

	/**
	 * 保存数据库表的显示规则
	 * @param  string $database 数据库
	 * @param  string $table 数据表
	 * @param  string $rule 显示规则
	 * @return bool 保存成功或失败
	 */
	public function saveRules($database, $table, array $rule)
	{
		$rules = (new \Qii\Driver\Easy())->_initialize();
		$rules->setPrivateKey(array('database', 'table'));
		$rules->setRules(new \Qii\Driver\Rules($this->getRuleTableInfo()));
		$rules->database = $database;
		$rules->table = $table;

		$isExists = $rules->_exist();
		if ($isExists->isError()) {
			$this->tablesError = $isExists->getErrors();
			return false;
		}
		if (isset($isExists->getResult()['database'])) {
			//保存额外的规则，确保更新不影响
			$databaseRules = $this->getRules($database, $table);
			if (isset($databaseRules['rules']['invalidMessage']) && is_array($databaseRules['rules']['invalidMessage'])) {
				$rule['invalidMessage'] = $databaseRules['rules']['invalidMessage'];
			}
			if (isset($databaseRules['rules']['extRules']) && is_array($databaseRules['rules']['extRules'])) {
				$rule['extRules'] = $databaseRules['rules']['extRules'];
			}
			$rules->rules = json_encode($rule);
			$rules->update_time = time();
			//更新
			$result = $rules->_update();
		} else {
			$rules->rules = json_encode($rule);
			$rules->add_time = time();
			$result = $rules->_save();
		}
		$result = true;
		if ($rules->getErrors()) {
			$result = false;
			$this->tablesError = $rules->getErrors();
		}
		return $result;
	}

	/**
	 * 更新数据表规则
	 *
	 * @param string $database 数据库名称
	 * @param string $table 数据表名称
	 * @param array $rule 规则
	 * @return bool
	 */
	public function updateRules($database, $table, array $rule)
	{
		if (!$database || !$table) {
			$this->tablesError = '参数不正确';
			return false;
		}
		$databaseRules = $this->getRules($database, $table);
		if (!isset($databaseRules['rules']) || !is_array($databaseRules['rules'])) {
			$this->tablesError = '规则不存在';
			return false;
		}
		$rule = array_merge($databaseRules['rules'], $rule);
		$rules = (new \Qii\Driver\Easy())->_initialize();
		$rules->setPrivateKey(array('database', 'table'));
		$rules->setRules(new \Qii\Driver\Rules($this->getRuleTableInfo()));
		$rules->database = $database;
		$rules->table = $table;
		$rules->rules = json_encode($rule);

		$isExists = $rules->_exist();
		if ($isExists->isError()) {
			$this->tablesError = $isExists->getError();
			return false;
		}
		if ($isExists->getResult()['database']) {
			$rules->update_time = time();
			//更新
			$result = $rules->_update();
		} else {
			$rules->add_time = time();
			$result = $rules->_save();
		}
		$result = true;
		if ($rules->isError()) {
			$result = false;
			$this->tablesError = $rules->getErrors();
		}
		return $result;
	}

	/**
	 * 自动表的规则
	 *
	 * @param $database
	 * @param $tableName
	 * @return array
	 */
	public function tableRules($database, $tableName)
	{
		if (!$database || !$tableName) {
			$this->tablesError = '参数不正确';
			return array();
		}
		$data = array();
		$rules = $this->getFieldsLists($database, $tableName);
		$data['database'] = $database;
		$data['tableName'] = $tableName;
		$data['rules'] = array();
		$data['rules']['end'] = array();
		$data['rules']['front'] = array();
		$data['rules']['alias'] = array();
		$data['rules']['fields'] = array();
		foreach ($rules AS $key => $val) {
			$data['rules']['fields'][] = $key;
			$data['rules']['end'][$key] = $data['rules']['front'][$key] = 1;
			$data['rules']['alias'][$key] = $key;
			$data['rules']['length'][$key] = $val['length'];
			$data['rules']['type'][$key] = $val['type'];
			$data['rules']['null'][$key] = $val['null'];
			if (isset($val['sets'])) $data['rules']['sets'][$key] = $val['sets'];
			if (isset($val['setsArray'])) $data['rules']['setsArray'][$key] = $val['setsArray'];
			if (isset($val['pri'])) $data['rules']['pri'][$key] = 1;
			if (isset($val['uni'])) $data['rules']['uni'][$key] = 1;
		}
		if (!isset($data['rules']['validate'])) $data['rules']['validate'] = array();
		return $data;
	}

	/**
	 * 合并表的规则，当没有存储相关规则的时候就用系统默认规则
	 *
	 * @param $database
	 * @param $tableName
	 * @param $rules
	 */
	public function mergeRules($database, $tableName, &$rules)
	{
		$tableRules = $this->tableRules($database, $tableName);
		$rules = array_merge($tableRules, $rules);
		foreach ($tableRules['rules'] AS $key => $val) {
			if (!isset($rules['rules'][$key]) || count($rules['rules'][$key]) == 0) $rules['rules'][$key] = $val;
		}
		return $rules;
	}

	/**
	 * 自动合并数据表规则
	 *
	 * @param $database
	 * @param $tableName
	 * @return array
	 */
	public function autoRules($database, $tableName)
	{
		if (!$database || !$tableName) {
			$this->tablesError = '参数不正确';
			return array();
		}
		$rules = $this->getRules($database, $tableName);
		$this->mergeRules($database, $tableName, $rules);
		return $rules;
	}

	/**
	 * 获取数据表的数据
	 *
	 * @param $database 当前数据表
	 * @param $tableName 当前表名称
	 * @param int $currentPage 当前页码
	 * @param int $pageSize 每一页数据条数
	 * @return mixed
	 */
	public function loadTableData($database, $tableName, $currentPage = 1, $pageSize = 12)
	{
		if (!$database || !$tableName) {
			$this->tablesError = '参数不正确';
			return array();
		}
		$currentPage = max(1, $currentPage);
		$start = ($currentPage - 1) * $pageSize;
		/*
		$rules = $this->getRules($database, $tableName);
		//当rules为空的时候，通过数据表结构自动生成规则
		if(empty($rules['rules']))
		{
			$rules = $this->tableRules($database, $tableName);
		}
		else
		{
			$this->mergeRules($database, $tableName, $rules);
		}*/
		$rules = $this->autoRules($database, $tableName);
		$data = array();
		$data['rows'] = array();
		$data['page'] = array('total' => 0, 'currentPage' => 0, 'totalPage' => 0);
		$data['page']['total'] = $this->db->getOne("SELECT COUNT(*) FROM {$database}.{$tableName}");
		$data['page']['currentPage'] = $currentPage;
		$data['page']['totalPage'] = ceil($data['page']['total'] / $pageSize);
		$sql = "SELECT * FROM {$database}.{$tableName} LIMIT " . $start . ',' . $pageSize;
		$this->db->setQuery($sql);
		$rulesCount = isset($rules['rules']['end']) && count($rules['rules']['end']) > 0 ? $rules['rules']['end'] : 0;
		while ($row = $this->db->fetch()) {
			$val = array();
			if ($rulesCount > 0) {
				foreach ($rules['rules']['end'] AS $key => $field) {
					if ($field == 1) $val[$key] = $row[$key];
				}
			}
			$updateFields = array();
			$priKeys = array();
			foreach ($rules['rules']['pri'] AS $key => $pri) {
				$priKeys[] = $key;
				$updateFields[$key] = $row[$key];
			}
			//为了避免表中字段跟主要参数冲突，主要参数前边添加两个下划线
			$updateFields['__pri'] = join(',', $priKeys);
			$updateFields['__database'] = $database;
			$updateFields['__tableName'] = $tableName;
			$val['__updateFields'] = http_build_query($updateFields);
			$data['rows'][] = $val;
			unset($val);
		}
		$data['rules'] = $rules['rules'];
		return $data;
	}

	/**
	 * 获取表中的数据
	 * @author Jinhui Zhu 2015-08-26
	 * @param string $database
	 * @param string $tableName
	 * @param string $pri
	 * @param Array $val
	 * @return mixed
	 */
	public function loadDataFromTable($database, $tableName, $pri, $val)
	{
		if (!$database || !$tableName || (!$val && count($val) == 0)) {
			$this->tablesError = '参数不正确';
			return array();
		}
		return $this->db->whereArray($val)->selectRow($database . '.' . $tableName);
	}

	/**
	 * 更新数据表数据
	 * @param string $database
	 * @param string $tableName
	 * @param array $priVal
	 * @param array $fields
	 * @return bool
	 */
	public function updateTableData($database, $tableName, $priVal, $fields)
	{
		if (!$database || !$tableName || !$fields) {
			$this->tablesError = '参数不正确';
			return array();
		}
		$rules = $this->autoRules($database, $tableName);
		$privateKey = array();
		if (empty($priVal) && isset($rules['rules']['uni'])) $privateKey = array_keys($rules['rules']['uni']);
		$table = (new \Qii\Driver\Easy())->_initialize();
		$table->setPrivateKey($priVal);
		$table->setRules(new \Qii\Driver\Rules($rules));
		foreach ($fields AS $key => $val) {
			$table->{$key} = $val;
		}
		return $result = $table->_update();
	}

	/**
	 * 删除指定数据表中数据
	 * 避免删除整张表的数据，验证val，如果为空就不删除
	 * @param string $database
	 * @param string $tableName
	 * @param array $val {key : val}
	 * @return bool
	 */
	public function removeTableData($database, $tableName, $val)
	{
		if (empty($val)) {
			$this->tablesError = '参数不正确';
			return false;
		}
		return $this->db->deleteObject($database . '.' . $tableName, $val);
	}

	/**
	 * 向指定数据库中插入数据
	 *
	 * @param $database
	 * @param $tableName
	 * @param $value
	 */
	public function addTableData($database, $tableName, $value)
	{
		if (!$database || !$tableName || !$value) {
			$this->tablesError = '参数不正确';
			return array();
		}
		$rules = $this->autoRules($database, $tableName);
		//去掉自动privateKey,通过配置文件来做
		//$privateKey = array();
		//if(isset($rules['rules']['uni'])) $privateKey = array_keys($rules['rules']['uni']);
		$table = (new \Qii\Driver\Easy())->_initialize();
		//$table->setPrivateKey($privateKey);
		$table->setRules(new \Qii\Driver\Rules($rules));
		foreach ($value AS $key => $val) {
			$table->{$key} = $val;
		}
		try {
			$result = $table->_save();
			if ($result->isError()) {
				return $result;
			}
			return Response::Success('addTableData', '操作成功');
		} catch (\Exception $e) {
			$msg = strip_tags($e->getMessage());
			return Response::Instance(10010, 'addTableData', array('message' => $msg));
		}
	}

	/**
	 * 备份指定数据表
	 *
	 * @param $database  数据库名称
	 * @param $tableName  数据表名称
	 * @return string
	 */
	public function backupTable($database, $tableName)
	{
		$sql = "SELECT * FROM {$database}.{$tableName}";
		$rs = $this->db->setQuery($sql);
		$data = array();
		$backupSQL = "USE {$database};\n";
		$tableSQL = $this->getTableSQL($database, $tableName);
		if ($tableSQL['sql'] == '') _e('获取数据表错误', __LINE__);
		$backupSQL .= $tableSQL['sql'];
		$i = 0;
		$fields = array();
		while ($row = $this->db->fetch($rs)) {
			$fields = array_keys($row);
			$row = array_map('addslashes', $row);
			$data[] = '(\'' . join("','", $row) . '\')';
			if ($i == 500) {
				//执行一次合并操作
				$backupSQL .= "\nINSERT INTO {$database}.{$tableName}(`" . join('`, `', $fields) . "`) VALUES " . join(', ', $data) . ";\n";
				$data = array();
				$i = 0;
			} else {
				$i++;
			}
		}
		if (count($data) > 0) {
			$backupSQL .= "\nINSERT INTO {$database}.{$tableName}(`" . join('`, `', $fields) . "`) VALUES " . join(', ', $data) . ";\n";
		}
		return $backupSQL;
	}

	/**
	 * 还原数据
	 *
	 * @param $database  数据库名
	 * @param $tableName  数据表名
	 * @param $fileName  文件名
	 * @return array|void
	 */
	public function restore($database, $tableName, $fileName)
	{
		$data = array();
		$data['code'] = 1;
		if (!$data || !$tableName || !$fileName) {
			$data['msg'] = '参数错误或文件错误';
			return $data;
		}
		$contents = file_get_contents($fileName);
		$tableSQL = explode(';', $contents);
		if (count($tableSQL) == 0) {
			$data['msg'] = '上传的文件无相关据';
			return $data;
		}
		try {
			foreach ($tableSQL AS $sql) {
				$sql = trim($sql);
				if ($sql == '') continue;
				$this->db->setQuery($sql);
			}
			$data['code'] = 0;
			$data['msg'] = '成功';
		} catch (\Exception $e) {
			$data['code'] = $e->getCode();
			$data['msg'] = strip_tags($e->getMessage());
		}
		return $data;
	}
}
