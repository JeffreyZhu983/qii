<?php
/**
 * 数据管理
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Action_Abstract;

class table extends Action_Abstract
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
		$loadDatabase = true;
		$this->_view->assign('controller', $this->_controller);
		$this->_view->assign('action', $this->_action);
		try {
			$this->_view->assign('loadDatabase', $loadDatabase);
			$database = $this->_request->get('database', $database);
			$tableName = $this->_request->get('tableName', $tableName);
			$currentPage = $this->_request->get('currentPage', 1);
			$databases = array();
			$tables = array();
			if ($loadDatabase) {
				$databases = $this->_load->model('table')->getDatabases();
				if (!$database && count($database) > 0) $database = $databases[0];
				if (!$database) throw new \Exception('数据库名不能为空', __LINE__);
				$tables = $this->_load->model('table')->getTableLists($database);
				if (!$tableName && count($tables) > 0) {
					$tableName = $tables[0];
				}
			}
			$this->_view->assign('databases', $databases);
			$this->_view->assign('tables', $tables);

			$this->_view->assign('database', $database);
			$this->_view->assign('tableName', $tableName);
			$data = array();
			$data['page'] = array();
			$data['page']['currentPage'] = 1;
			$data['page']['totalPage'] = 0;
			$data['page']['total'] = 0;
			$data['rules'] = array();
			$data['rules']['end'] = array();
			$data['rows'] = array();
			if ($tableName) {
				$data = $this->_load->model('table')->loadTableData($database, $tableName, $currentPage);
			}
			$start = 0;
			if ($data['page']['currentPage'] >= 6) {
				$start = $data['page']['currentPage'] - 6;
			}
			$this->_view->assign('start', $start);
			$this->_view->assign('data', $data);
			$this->_view->assign('pages', $data['page']);
			$this->_view->display('manage/data/table.html');
		} catch (Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}
}