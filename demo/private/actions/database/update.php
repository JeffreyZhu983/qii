<?php
/**
 * 更新规则页面
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Action_Abstract;

class update extends Action_Abstract
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
		try {
			$database = $this->_request->get('__database');
			$tableName = $this->_request->get('__tableName');
			$pri = explode(',', $this->_request->get('__pri', ''));
			$val = array();
			foreach ($pri AS $key) {
				$val[$key] = $this->_request->get($key);
			}
			if (!$database || !$tableName || !$pri || !$val) {
				throw new \Exception('参数不正确', __LINE__);
			}
			$rules = $this->_load->model('table')->getRules($database, $tableName);

			$data = $this->_load->model('table')->loadDataFromTable($database, $tableName, $pri, $val);
			$this->_view->assign('database', $database);
			$this->_view->assign('tableName', $tableName);
			$this->_view->assign('pri', $pri);
			$this->_view->assign('rules', $rules);
			$this->_view->assign('val', $val);
			$this->_view->assign('data', $data);

			$this->_view->display('manage/data/update.html');
		} catch (Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}
}