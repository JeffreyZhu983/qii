<?php
/**
 * 保存Session在网站配置的缓存中
 * 
 * @author Jinhui.Zhu
 * 
 */
if(class_exists('Session'))
{
	return;
}
class Session
{
	static $version = '1.1.0';
	static private $_cache;
	/**
	 * Session初始化
	 */
	static function init()
	{
		session_set_save_handler(
				array("Session","open"),
				array("Session","close"),
				array("Session","read"),
				array("Session","write"),
				array("Session","destroy"),
				array("Session","gc")
		);
		$siteInfo = Qii::getSiteInfo();
		//如果没有配置缓存就抛出异常
		if(Qii::setError($siteInfo['status']['cache'], 115))
		{
			throw new Exception(Qii::$language->gettext(115));
		}
		$policy = array('servers' => self::getCachePolicy($siteInfo[$siteInfo['status']['cache']]));
		if(ini_get('session.gc_maxlifetime'))
		{
			$policy['life_time'] = ini_get('session.gc_maxlifetime');
		}
		Qii::requireOnce(Qii_DIR . DS . 'core' . DS . '_Cache.php');
		self::$_cache = Qii::instance('_Cache', $siteInfo['status']['cache'])->initialization($policy);//载入cache类文件
	}

	/**
	 * 获取缓存策略
	 * @param String $cache
	 * @return Array
	 */
	static public function getCachePolicy($cache)
	{
		$data = array();
		$servers = explode(";", $cache['servers']);
		$ports = explode(";", $cache['ports']);
		for($i = 0; $i < count($servers); $i++)
		{
			$data[] = array('host' => $servers[$i], 'port' => $ports[$i]);
		}
		return $data;
	}
	/**
	 * 开启Session
	 * @param String $save_path
	 * @param String $session_name
	 * @return Boolean
	 */
	static function open($save_path, $session_name)
	{
		return false;
	}
	static function close()
	{
		return true;
	}
	/**
	 * 生成缓存ID
	 * @param String $id
	 * @return string
	 */
	static private function sessionId($id)
	{
		return 'ssid_'. $id;
	}
	/**
	 * 读取缓存
	 * @param Int $id
	 */
	static function read($id)
	{
		return self::$_cache->get(self::sessionId($id));
	}
	
	static function write($id,$sess_data)
	{
		self::$_cache->set(self::sessionId($id), $sess_data);
	}
	
	static function destroy($id)
	{
		self::$_cache->set(self::sessionId($id), '', -1);
	}
	/**
	 * 回收空间
	 * @param Int $maxlifetime
	 * @return boolean
	 */
	static function gc($maxlifetime)
	{
		return true;
	}
	// proceed to use sessions normally
}