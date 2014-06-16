<?php
/**
 *
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: instance.tools.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 自动加载
 */
class instance_sys_tools
{
	public $version = '1.1.0';
	private $_global;
	public function __construct()
	{
		$args = func_get_args();
		$className = $args[0];
		if(isset($this->_global['instance'][$className]))
		{
			return clone $this->_global['instance'][$className];
		}
		elseif(!Qii::setError(class_exists($className, false), 103, array($className)))
		{
			/**
			 * 判断类的继承关系, 如果从Module、View、Control继承的则先包含这几个文件
			 */
			array_shift($args);
			$loader = new ReflectionClass($className);
			$this->_global['instance'][$className] =  call_user_func_array(array($loader, 'newInstance'), $args);
			return $this->_global['instance'][$className];
		}
	}
	public function __toString()
	{
		return __CLASS__;
	}
}
?>