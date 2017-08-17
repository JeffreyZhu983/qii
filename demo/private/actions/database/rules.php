<?php
/**
 * 编辑详细规则
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Action_Abstract;

class rules extends Action_Abstract
{
	protected $enableView = true;
	protected $enableDatabase = true;

	public function __construct()
	{
		parent::__construct();

	}

	/**
	 * 创建规则
	 * @author Jinhui Zhu 2015-08-23
	 */
	public function execute()
	{
		list($database, $tableName) = array_pad(func_get_args(), 2, '');
		$loadDatabase = $database ? false : true;
		$this->_view->assign('loadDatabase', $loadDatabase);
		$tableName = $this->_request->get('tableName', $tableName);
		$database = $this->_request->get('database', $database);
		try {
			$databases = $this->_load->model('table')->getDatabases();
			if (!$database && count($database) > 0) $database = $databases[0];
			$this->_view->assign('databases', $databases);
			$tables = $this->_load->model('table')->getTableLists($database);
			if (!$tableName && count($tables) > 0) {
				$tableName = $tables[0];
			}
			$this->_view->assign('tables', $tables);
			$fields = array();
			$rules = array();
			$rules['rules'] = array();
			if ($tableName != '') {
				$fields = $this->_load->model('table')->getFieldsLists($database, $tableName);
				$rules = $this->_load->model('table')->getRules($database, $tableName);
				if (isset($rules['rules'])) $rules['rules'] = $rules['rules'];
			}
			$validateRules = isset($rules['rules']['validate']) ? $rules['rules']['validate'] : array();
			$invalidMessage = isset($rules['rules']['invalidMessage']) ? $rules['rules']['invalidMessage'] : array();
			$extRules = isset($rules['rules']['extRules']) ? $rules['rules']['extRules'] : array();
			$this->_view->assign('validateRules', $validateRules);
			$this->_view->assign('invalidMessage', $invalidMessage);
			$this->_view->assign('extRules', $extRules);

			$validate = new \Qii\Validate();
			$this->_view->assign('validate', $validate->getRuleNames());
			$this->_view->assign('fields', $fields);
			$this->_view->assign('rules', isset($rules['rules']) ? $rules['rules'] : array());
			$this->_view->assign('database', $database);
			$this->_view->assign('tableName', $tableName);
			$this->_view->display('manage/data/rules.html');
		} catch (Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}
}