<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Model.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 数据库类，提供统一的数据库接口及方法
 * 
 */
class QiiModelException
{
	public function __construct()
	{
		
	}
	public function __call($name, $args)
	{
		Qii::setError(false, 106, array('Model', $name, print_r($args, true)));
		return false;
	}
}
class _Model
{
	public $version = '1.1.0';
	public function __construct()
	{
		$siteInfo = Qii::getSiteInfo();
		if(!isset($siteInfo['status']['dbModel']))
		{
			$use = 'pdo';
		}
		else
		{
			$use = $siteInfo['status']['dbModel'];
		}
		Qii::requireOnce(Qii_DIR . DS . 'core' . DS . 'model' . DS . $use .'.php');
	}
	/**
	 * 初始化数据库类
	 *
	 * @param Array $policy
	 * @return Object
	 */
	public function initialization()
	{
		return Qii::instance('Model');
	}
}
?>