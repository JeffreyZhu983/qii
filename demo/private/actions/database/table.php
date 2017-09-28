<?php
/**
 * 数据管理
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Base\Action;

class table extends Action
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
		$loadDatabase = true;
		$this->controller->view->assign('controller', $this->controllerId);
		$this->controller->view->assign('action', $this->actionId);
		try {
			$this->controller->view->assign('loadDatabase', $loadDatabase);
			$database = $this->request->get('database', $database);
			$tableName = $this->request->get('tableName', $tableName);
			$currentPage = $this->request->get('currentPage', 1);
			$databases = array();
			$tables = array();
			if ($loadDatabase) {
				$databases = $this->controller->load->model('table')->getDatabases();
				if (!$database && count($database) > 0) $database = $databases[0];
				if (!$database) throw new \Exception('数据库名不能为空', __LINE__);
				$tables = $this->controller->load->model('table')->getTableLists($database);
				if (!$tableName && count($tables) > 0) {
					$tableName = $tables[0];
				}
			}
			$this->controller->view->assign('databases', $databases);
			$this->controller->view->assign('tables', $tables);

			$this->controller->view->assign('database', $database);
			$this->controller->view->assign('tableName', $tableName);
			$data = array();
			$data['page'] = array();
			$data['page']['currentPage'] = 1;
			$data['page']['totalPage'] = 0;
			$data['page']['total'] = 0;
			$data['rules'] = array();
			$data['rules']['end'] = array();
			$data['rows'] = array();
			if ($tableName) {
				$data = $this->controller->load->model('table')->loadTableData($database, $tableName, $currentPage);
			}
			$start = 0;
			if ($data['page']['currentPage'] >= 6) {
				$start = $data['page']['currentPage'] - 6;
			}
			$this->controller->view->assign('start', $start);
			$this->controller->view->assign('data', $data);
			$this->controller->view->assign('pages', $data['page']);
			$this->controller->view->display('manage/data/table.html');
		} catch (Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}
}