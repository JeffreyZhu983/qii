<?php
namespace Qii\Cache;

class Apcu implements Qii_Cache_Intf
{
    const VERSION = '1.2';
    public $policy = array('life_time' => 3600);//设置目录、过期时间、文件前缀

    public function __construct(array $policy = null)
    {
        if (!empty($policy)) {
            $this->policy = array_merge($this->policy, $policy);
        }
    }

    public function set($key, $value, $policy)
    {
        if(in_array($policy))
        {
            $this->policy = array_merge($this->policy, $policy);
        }
        return apcu_store($key, $value, $this->policy['life_time']);
    }

    public function get($key)
    {
        return apcu_fetch($key);
    }

    public function exists($key)
    {
        return apcu_exists($key);
    }
    
    public function del($key)
    {
        return apcu_delete($key);
    }
}