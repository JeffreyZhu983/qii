<?php
/**
 * Memcache缓存
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-10-25 21:41
 *
 */
namespace Qii\Cache;

class Memcached implements Intf
{
    const VERSION = '1.2';
    /**
     * memcached连接句柄
     *
     * @var resource
     */
    protected $_conn;
    /**
     * 默认的缓存策略
     *
     * @var array
     */
    protected $_default_policy = array(
        /**
         * 缓存服务器配置，参看$_default_server
         * 允许多个缓存服务器
         */
        'servers' => array(),

        /**
         * 是否压缩缓存数据
         */
        'compressed' => false,

        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存永不过期
         */
        'life_time' => 900,

        /**
         * 是否使用持久连接
         */
        'persistent' => true,
    );

    /**
     * 默认的缓存服务器
     *
     * @var array
     */
    protected $_default_server = array(
        /**
         * 缓存服务器地址或主机名
         */
        'host' => '127.0.0.1',

        /**
         * 缓存服务器端口
         */
        'port' => '11211',
    );

    public function __construct(array $policy = null)
    {
        if (!extension_loaded('memcached') && !extension_loaded('memcache')) {
            return \Qii::setError(false, __LINE__, 1004);
        }
        if (is_array($policy)) {
            $this->_default_policy = array_merge($this->_default_policy, $policy);
        }
        if (empty($this->_default_policy['servers'])) {
            $this->_default_policy['servers'][] = $this->_default_server;
        }
        if (!isset($this->_default_policy['persistent'])) $this->_default_policy['persistent'] = '';
        if(extension_loaded('memcached'))
        {
	        $this->_conn = new \Memcached();
        }
        else
        {
	        $this->_conn = new \Memcache();
        }
        foreach ($this->_default_policy['servers'] as $server) {
            $result = $this->_conn->addServer($server['host'], $server['port'], $this->_default_policy['persistent']);
            if (!$result) {
                return \Qii::setError(false, __LINE__, 1005, $server['host'], $server['port']);
            }
        }
    }

    /**
     * 写入缓存
     *
     * @param string $id
     * @param mixed $data
     * @param array $policy
     * @return boolean
     */
    public function set($id, $data, array $policy = null)
    {
        if (is_array($policy)) {
            $this->_default_policy = array_merge($this->_default_policy, $policy);
        }

        if($this->_conn instanceof \Memcache || $this->_conn instanceof \Memcached){
            $data = serialize($data);
            if($this->_conn instanceof \Memcache) {
                return $this->_conn->set($id, $data, MEMCACHE_COMPRESSED, $this->_default_policy['life_time']);
            }
            return $this->_conn->set($id, $data, $this->_default_policy['life_time']);
        }
        return $this->_conn->set($id, $data, $this->_default_policy['life_time']);
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
        $data = $this->_conn->get($id);
        if($this->_conn instanceof \Memcache || $this->_conn instanceof \Memcached){
            $data = unserialize($data);
        }
        return $data;
    }

    /**
     * 删除指定的缓存
     *
     * @param string $id
     * @return boolean
     */
    public function remove($id)
    {
        return $this->_conn->delete($id);
    }

    /**
     * 清除所有的缓存数据
     *
     * @return boolean
     */
    public function clean()
    {
        return $this->_conn->flush();
    }
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_conn, $method), $args);
    }
}