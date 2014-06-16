<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: include.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 视图部分，提供供显示的方法，支持include
 * 
 */
class View
{
	public $data;
	private $viewPath;
	public function __construct()
	{
		$siteInfo = Qii::getSiteInfo();
		$this->viewPath = $siteInfo['view']['path'];
	}
	/**
	 * Assign
	 *
	 * @param Mix $name
	 * @param Mix $val
	 */
	public function assign($name, $val)
	{
		if(isset($val))
		{
			$this->data[$name] = $val;
		}
		else if(is_array($name))
		{
			foreach ($name AS $k => $v)
			{
				$this->data[$k] = $v;
			}
		}
	}
	/**
	 * 载入数据和模板
	 *
	 * @param String $tpl
	 */
	public function display($tpl)
	{
		$tpl = $this->viewPath . DS . $tpl;
		extract((array) $this->data);
		if(!Qii::setError(file_exists($tpl), 102, array($tpl)))
		{
			include($tpl);
		}
	}
}
?>