<?php
/**
 * 缓存类接口文件，统一所有缓存类的方法
 *
 * @author Jinhui.zhu    <jinhui.zhu@live.cn>
 *
 */
namespace Qii\Cache;

interface Intf
{
    public function __construct(array $policy = null);

    public function set($id, $data, array $policy = null);//设置

    public function get($id);//获取指定key的缓存

    public function remove($key);//移除指定key的缓存

    public function clean();//清除所有缓存
}