<?php
namespace Qii\Cache;

\Qii\Autoloader\Import::requires(array(dirname(__FILE__) . DS . 'Redis/Client.php', dirname(__FILE__) . DS . 'Redis/Cluster.php'));

/**
 * PHP 操作 redis
 * @author Jinhui.Zhu
 *
 */

class Redis implements Intf
{
    const VERSION = '1.2';

    public $redis;
    protected $policy = array(
        /**
         * 缓存服务器配置，参看$_default_server
         * 允许多个缓存服务器
         */
        'servers' => array(['host' => '127.0.0.1', 'port' => '6379']),

        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存永不过期
         */
        'life_time' => 900
    );

    public function __construct(array $policy = null)
    {
        if (!extension_loaded('redis')) {
            throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1006), __LINE__);
        }


        if (is_array($policy)) {
            $this->policy = array_merge($this->policy, $policy);
        }

        $redisServer = array();
        foreach ($this->policy['servers'] AS $value) {
            $redisServer[] = array('host' => $value['host'], 'port' => $value['port']);
        }
        $this->redis = new \Qii\Cache\Redis\Cluster($redisServer, 128);
    }

    /**
     * 保存指定key的数据
     */
    public function hMset($id, $data, array $policy = null)
    {
        if (is_array($policy)) {
            $this->policy = array_merge($this->policy, $policy);
        }
        try {
            $this->redis->hMset($id, $data);
            if (isset($this->policy['life_time']) && $this->policy['life_time'] > 0) {
                $this->redis->setTimeout($id, $this->policy['life_time']);
            }
        } catch (\CredisException $e) {
            throw new \Qii\Exceptions\Errors(\Qii::i(-1, $e->getMessage()), __LINE__);
        }
    }

    /**
     * 保存指定key的数据
     */
    public function set($id, $value, array $policy = null)
    {
        if (is_array($policy)) {
            $this->policy = array_merge($this->policy, $policy);
        }
        try {
            $this->redis->set($id, $value);
            if (isset($this->policy['life_time']) && $this->policy['life_time'] > 0) {
                $this->redis->setTimeout($id, $this->policy['life_time']);
            }
        } catch (\CredisException $e) {
            throw new \Qii\Exceptions\Errors(\Qii::i(-1, $e->getMessage()), __LINE__);
        }
    }
    /**
     * 获取指定key的数据
     */
    public function hGet($id)
    {
        if ($this->redis->exists($id)) {
            return $this->redis->hGetAll($id);
        }
        return null;
    }
    /**
     * 获取指定key的数据
     */
    public function get($id)
    {
        if ($this->redis->exists($id)) {
            return $this->redis->get($id);
        }
        return null;
    }
    
    public function exists($id)
    {
        return $this->redis->exists($id);
    }

    /**
     * 删除指定key的数据
     */
    public function remove($id)
    {
        if ($this->redis->exists($id)) {
            return $this->redis->delete($id);
        }
    }

    /**
     * 清除当前db的所有数据
     */
    public function clean()
    {
        $this->redis->flushdb();
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->redis, $method), $args);
    }
}