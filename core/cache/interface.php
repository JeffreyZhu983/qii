<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: interface.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 *
 * 缓存类接口文件，统一所有缓存类的方法
 */
interface cacheInterface
{
	public function __construct(array $policy = null);
	public function set($id, $data, array $policy = null);//设置
	public function get($id);//获取指定key的缓存
	public function remove($key);//移除指定key的缓存
	public function clean();//清除所有缓存
}
?>