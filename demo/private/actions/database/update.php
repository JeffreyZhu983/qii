<?php
/**
 * 更新规则页面
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Base\Action;

class update extends Action
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
		try {
			$database = $this->request->get('__database');
			$tableName = $this->request->get('__tableName');
			$pri = explode(',', $this->request->get('__pri', ''));
			$val = array();
			foreach ($pri AS $key) {
				$val[$key] = $this->request->get($key);
			}
			if (!$database || !$tableName || !$pri || !$val) {
				throw new \Exception('参数不正确', __LINE__);
			}
			$rules = $this->controller->load->model('table')->getRules($database, $tableName);

			$data = $this->controller->load->model('table')->loadDataFromTable($database, $tableName, $pri, $val);
			$this->controller->view->assign('database', $database);
			$this->controller->view->assign('tableName', $tableName);
			$this->controller->view->assign('pri', $pri);
			$this->controller->view->assign('rules', $rules);
			$this->controller->view->assign('val', $val);
			$this->controller->view->assign('data', $data);

			$this->controller->view->display('manage/data/update.html');
		} catch (Exception $e) {
			$this->showErrorPage($e->getMessage());
		}
	}
}