<?php
/**
 * 数据表的显示规则
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn> 2015-08-23 10:07
 */
namespace Model;

use \Qii\Driver\Model;
use \Qii\Driver\Response;

class code extends Model
{
	public $tableName;
	public $codes = array();
	public function __construct()
	{
		parent::__construct();
	}
	public function setDatabase($database)
	{
		$this->database = $database;
	}
	public function setClass($tableName)
	{
		$this->tableName = $tableName;
	}

	public function output()
	{
		if(!$this->tableName) throw new \Exception("未设置类名", __LINE__);
		$output['className'] = $this->tableName;
		$output['database'] = $this->database;
		return $output;
	}
}