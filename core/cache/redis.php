<?php
Qii::requireOnce(dirname(__FILE__) . DS . 'Redis/Client.php');
Qii::requireOnce(dirname(__FILE__) . DS . 'Redis/Cluster.php');
Qii::requireOnce(dirname(__FILE__) . DS . 'interface.php');
/**
 * PHP 操作 redis
 * @author Jinhui.Zhu
 *
 */
class Cache implements cacheInterface {
	public $redis;
	protected $_default_policy = array(
        /**
         * 缓存服务器配置，参看$_default_server
         * 允许多个缓存服务器
         */
        'servers' => array('127.0.0.1:6379'),

        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存永不过期
         */
        'life_time' => 900
    );
	
	public function __construct(array $policy = null)
	{
		if (!extension_loaded('redis'))
        {
        	return Qii::setError(false, 116, array());
        }
		if(!empty($policy))
		{
			$this->policy = array_merge($this->policy, $policy);
		}
		
		$redisServer = array();
		
		foreach($this->_default_policy['servers'] AS $value)
		{
			$host = explode(':', $value);
			$redisServer[] = array('host' => $host[0], 'port' => $host[1]);
		}
		$this->redis = new Redis_Credis_Cluster($redisServer, 128);
	}
	/**
	 * 保存指定key的数据
	 */
	public function set($id, $data, array $policy = null)
	{
    	if(!isset($policy['life_time'])) $policy['life_time'] = $this->_default_policy['life_time'];
		try
		{
			$this->redis->hMset($id, $data);
			if($policy['lift_time'] > 0)
			{
				$this->redis->setTimeout($id, $policy['life_time']);
			}
		}
		catch(CredisException $e)
		{
        	return Qii::setError(false, -1, array($e->getMessage()));
		}
	}
	/**
	 * 获取指定key的数据
	 */
	public function get($id)
	{
		if($this->redis->exists($id))
		{
			return $this->redis->hGetAll($id);
		}
		return null;
	}
	/**
	 * 删除指定key的数据
	 */
	public function remove($key)
	{
		if($this->redis->exists($id))
		{
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
}