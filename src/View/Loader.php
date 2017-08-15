<?php
namespace Qii\View;

class Loader
{
	protected $view;
	protected $allow = array('smarty', 'require', 'include');
	
	public function __construct()
	{

	}
	
	public function setView($engine, $policy = array())
	{
		if(!in_array($engine, $this->allow))
		{
			throw new \Qii\Exceptions\Unsupport(\Qii::i('Unsupport method', $engine));
		}
		$class = '\Qii\View\\'. ucwords($engine);
		return $this->view = new $class();
	}

	public function getView()
	{
		return $this->view;
	}
}