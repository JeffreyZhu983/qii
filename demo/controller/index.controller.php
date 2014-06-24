<?php
session_start();
class index_controller extends Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->Qii('Model');
		$this->Qii('View');
	}
	public function index()
	{
		//$this->Qii('index_model');
		//print_r($this->index_model->index());
		$this->view->assign('memory', Qii::useMemory());
		$this->view->assign('file_lists', get_included_files());
		Benchmark::set('site', true);
		$this->view->assign('costTime', Benchmark::Caculate('site'));
		//$this->view->display('index.php');  OR 
		Qii::load('View')->display('index.php');
		$this->setCache('file', array('path' => 'tmp'));
		//$this->cache->set('sid', array('test'));
		//print_r($this->cache->get('sid'));
		//$this->cache->remove('sid');
		//$this->cache->clean();
		$this->Qii('index_model');
		$this->index_model->index();
	}
	protected function test()
	{
		$this->setCache('redis', array('servers' => array('127.0.0.1.6379')));
		print_r($this->cache->set('redis', array('servers' => '127.0.0.1.6379')));
		print_r($this->cache->get('redis'));
		
	}
}
?>