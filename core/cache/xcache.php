<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: xcache.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 *
 */
Qii::requireOnce(dirname(__FILE__) . DS . 'interface.php');
class Cache implements cacheInterface
{
	/**
	 * 默认的缓存策略
	 *
	 * @var array
	 */
	protected $_default_policy = array(
	/**
	 * 缓存有效时间
	 *
	 * 如果设置为 0 表示缓存总是失效，设置为 null 则表示不检查缓存有效期。
	 */
	 'life_time'         => 900,
	 );

	 /**
	  * 构造函数
	  *
	  * @param 默认的缓存策略 $default_policy
	  */
	 public function __construct(array $default_policy = null)
	 {
	 	if (isset($default_policy['life_time']))
	 	{
	 		$this->_default_policy['life_time'] = (int)$default_policy['life_time'];
	 	}
	 }
	 /**
	 * 写入缓存
	 *
	 * @param string $id
	 * @param mixed $data
	 * @param array $policy
	 */
	 public function set($id, $data, array $policy = null)
	 {
	 	$life_time = !isset($policy['life_time']) ? (int)$policy['life_time'] : $this->_default_policy['life_time'];
	 	xcache_set($id, $data, $life_time);
	 }

	 /**
	  * 读取缓存，失败或缓存撒失效时返回 false
	  *
	  * @param string $id
	  *
	  * @return mixed
	 */
	 public function get($id)
	 {
	 	if (xcache_isset($id))
	 	{
	 		return xcache_get($id);
	 	}
	 	return false;
	 }

	 /**
	 * 删除指定的缓存
	 *
	 * @param string $id
	 */
	 public function remove($id)
	 {
	 	xcache_unset($id);
	 }
	 public function clean()
	 {
	 	
	 }
}
?>