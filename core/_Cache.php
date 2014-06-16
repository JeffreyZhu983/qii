<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Cache.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * Cache类
 * 
 */
class _Cache
{
	public $version = '1.1.0';
	public function __construct($cache)
	{
		$this->setCache($cache);
	}
	/**
	 * 初始化缓存类
	 *
	 * @param Array $policy
	 * @return Object
	 */
	public function initialization($policy)
	{
		$cls =  Qii::instance('Cache', $policy);
		if(method_exists($cls, 'initialization')) $cls->initialization($policy);
		return $cls;
	}
	/**
	 * 设置用于缓存的类
	 *
	 * @param String $cache
	 */
	public function setCache($cache)
	{
		$cacheFile = dirname(__FILE__) . DS .'cache'. DS . $cache . '.php';
		if(!is_file($cacheFile))
		{
			$cacheFile = dirname(__FILE__) . DS .'cache'. DS . 'memcache.php';
		}
		Qii::requireOnce($cacheFile);
	}
}
?>