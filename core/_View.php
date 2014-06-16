<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: View.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 视图部分，提供供显示的方法，支持smarty、include、require支持
 * 
 * 如果你需要自定义view的话，请在Qii_DIR/core/view/下添加新的view类
 * 
 * 文件命名方式：{$view}.php
 * 类命名方式{$view}View
 * 
 */
class _View
{
	public $version = '1.1.0';
	private $default;
	private $data;
	private $view;
	public function __construct()
	{
		$this->default = '';
	}
	/**
	 * 设置View类
	 *
	 * @param unknown_type $view
	 */
	public function setView($view)
	{
		$this->default = $view;
		$fullPath = Qii_DIR . DS . 'core' . DS . 'view' . DS. $view . '.php';
		//如果没有找到配置中的view就调用默认的
		if(!is_file($fullPath))
		{
			$fullPath = Qii_DIR . DS . 'core' . DS . 'view' . DS. 'smarty' . '.php';
			$this->default  = 'smarty';
		}
		Qii::requireOnce($fullPath);
		unset($this->view);
		return $this->view = Qii::instance('View');
	}
	/**
	 * 返回View类
	 *
	 * @return Object
	 */
	public function getView()
	{
		return $this->view;
	}
	/**
	 * 析构函数，销毁变量
	 *
	 */
	public function __destruct()
	{
		unset($this->default);
		unset($this->data);
		unset($this->view);
	}
}
?>