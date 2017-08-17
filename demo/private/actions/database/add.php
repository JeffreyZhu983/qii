<?php
/**
 * 添加数据
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2015-09-21
 *
 */
namespace actions\database;

use Qii\Action_Abstract;

class add extends Action_Abstract
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
		$database = $this->_request->get('database');
		$tableName = $this->_request->get('tableName');
		if (!$database) throw new \Exception('数据库名不能为空', __LINE__);
		if (!$tableName) throw new \Exception('数据表名不能为空', __LINE__);
		$this->_view->assign('database', $database);
		$this->_view->assign('tableName', $tableName);


		$rules = $this->_load->model('table')->getRules($database, $tableName);
		$fields = array_flip($rules['rules']['fields']);
		$this->_view->assign('rules', $rules);
		$this->_view->assign('fields', $fields);

		$this->_view->display('manage/data/add.html');
	}
}