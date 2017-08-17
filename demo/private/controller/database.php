<?php
namespace controller;
class database extends base
{
	public $actions = array(
		'creator' => 'actions\database\creator',//创建规则
		'rules' => 'actions\database\rules',//编辑详细的规则哦
		'table' => 'actions\database\table',//管理数据表数据
		'update' => 'actions\database\update',//更新规则页面
		'add' => 'actions\database\add',//添加数据
	);

	public function __construct()
	{
		parent::__construct();
	}
}