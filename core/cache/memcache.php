<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: memcache.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 *
 */
Qii::requireOnce(dirname(__FILE__) . DS . 'interface.php');
class Cache implements cacheInterface 
{
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
        if (!extension_loaded('memcache'))
        {
        	return Qii::setError(false, 111, array());
        }
        if(is_array($policy))
        {
            $this->_default_policy = array_merge($this->_default_policy, $policy);
        }
        if (empty($this->_default_policy['servers']))
        {
            $this->_default_policy['servers'][] = $this->_default_server;
        }
        if(!isset($this->_default_policy['persistent'])) $this->_default_policy['persistent'] = '';
        $this->_conn = new Memcache();
        foreach ($this->_default_policy['servers'] as $server)
        {
            $result = $this->_conn->addServer($server['host'], $server['port'], $this->_default_policy['persistent']);
            if (!$result)
            {
                return Qii::setError(false, 112, array($server['host'], $server['port']));
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
    	if(!isset($policy['compressed'])) $policy['compressed'] = $this->_default_policy['compressed'];
    	if(!isset($policy['life_time'])) $policy['life_time'] = $this->_default_policy['life_time'];

        return $this->_conn->set($id, $data, $policy['compressed'] ? MEMCACHE_COMPRESSED : 0, $policy['life_time']);
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
        return $this->_conn->get($id);
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
}
?>