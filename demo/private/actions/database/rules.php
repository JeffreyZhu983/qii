<?php
/**
 * 编辑详细规则
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Base\Action;

class rules extends Action
{
	public $enableView = true;
	public $enableDB = true;

	public function __construct()
	{
		parent::__construct();

	}

	/**
	 * 创建规则
	 * @author Jinhui Zhu 2015-08-23
	 */
	public function run()
	{
		list($database, $tableName) = array_pad(func_get_args(), 2, '');
		$loadDatabase = $database ? false : true;
		$this->controller->view->assign('loadDatabase', $loadDatabase);
		$tableName = $this->request->get('tableName', $tableName);
		$database = $this->request->get('database', $database);
		try {
			$databases = $this->controller->load->model('table')->getDatabases();
			if (!$database && count($database) > 0) $database = $databases[0];
			$this->controller->view->assign('databases', $databases);
			$tables = $this->controller->load->model('table')->getTableLists($database);
			if (!$tableName && count($tables) > 0) {
				$tableName = $tables[0];
			}
			$this->controller->view->assign('tables', $tables);
			$fields = array();
			$rules = array();
			$rules['rules'] = array();
			if ($tableName != '') {
				$fields = $this->controller->load->model('table')->getFieldsLists($database, $tableName);
				$rules = $this->controller->load->model('table')->getRules($database, $tableName);
				if (isset($rules['rules'])) $rules['rules'] = $rules['rules'];
			}
			$validateRules = isset($rules['rules']['validate']) ? $rules['rules']['validate'] : array();
			$invalidMessage = isset($rules['rules']['invalidMessage']) ? $rules['rules']['invalidMessage'] : array();
			$extRules = isset($rules['rules']['extRules']) ? $rules['rules']['extRules'] : array();
			$this->controller->view->assign('validateRules', $validateRules);
			$this->controller->view->assign('invalidMessage', $invalidMessage);
			$this->controller->view->assign('extRules', $extRules);

			$validate = new \Qii\Library\Validate();
			$this->controller->view->assign('validate', $validate->getRuleNames());
			$this->controller->view->assign('fields', $fields);
			$this->controller->view->assign('rules', isset($rules['rules']) ? $rules['rules'] : array());
			$this->controller->view->assign('database', $database);
			$this->controller->view->assign('tableName', $tableName);
			$this->controller->view->display('manage/data/rules.html');
		} catch (Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}
}