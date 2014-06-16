<?php
class index_model extends Model 
{
	public $cache;
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$this->cache = Qii::getPrivate('sysCache');
		//$this->cache->set('sid', array('test'));
		//print_r($this->cache->get('sid'));
	}
}
?>