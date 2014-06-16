<?php
/**
 * 
 * Helper 类
 * 
 * 输入文件的头部信息
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Helper.php 2 2011-05-04 08:50:19Z zjh $
 */
if(class_exists('Helper'))
{
	return;
}
class Helper
{
	public $version = '1.0.0';
	public function __consturct()
	{
		
	}
	/**
	 * 自动加载helper目录中的文件
	 *
	 * @param String $appPath Dir
	 */
	public function loadHelper($appPath = '')
	{
		if($appPath == '')
		{
			return;
		}
		if(!is_dir($appPath . DS . 'helper'))
		{
			return;
		}
		foreach (glob(str_replace("//", "/", $appPath . DS . 'helper' . DS .'*.php'), GLOB_BRACE) AS $file)
		{
			Qii::requireOnce($file);
			//如果里边包含class的话就将class注册到Qii::instance('class');
			$className = str_replace(array('.php', '.'), array('', '_'), basename($file));
			if(class_exists($className, false))
			{
				Qii::instance($className);	
			}
		}
	}
	
}
?>