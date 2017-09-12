<?php
/**
 * 添加数据
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Base\Action;

class add extends Action
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
		$database = $this->request->get('database');
		$tableName = $this->request->get('tableName');
		if (!$database) throw new \Exception('数据库名不能为空', __LINE__);
		if (!$tableName) throw new \Exception('数据表名不能为空', __LINE__);
		$this->controller->view->assign('database', $database);
		$this->controller->view->assign('tableName', $tableName);


		$rules = $this->controller->load->model('table')->getRules($database, $tableName);
		$fields = array_flip($rules['rules']['fields']);
		$this->controller->view->assign('rules', $rules);
		$this->controller->view->assign('fields', $fields);

		$this->controller->view->display('manage/data/add.html');
	}
}