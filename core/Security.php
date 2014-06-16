<?php
/**
 * 用于安全验证，如果开启了安全认证，程序在POST的时候需要将对应的安全key提交过来，否则报错
 * @author Jinhui.Zhu
 *
 */
if(class_exists('Security'))
{
	return;
}
class Security
{
	public $version = '1.1.0';
	static private $_privateKey = 'Qii.Security';
	static private $_signLength = 2;//数字认证的长度
	static private $_expiredTime = 300;//5分钟过期

	static public function setPrivateKey($key)
	{
		self::$_privateKey = $key;
	}
	
	static public function setSignLength($len = 2)
	{
		self::$_signLength = $len;
	}
	/**
	 * 设置过期时间，如果为0，则永不过期
	 * @param String $expiredTime
	 */
	static public function setExpiredTime($expiredTime = 0)
	{
		self::$_expiredTime = $expiredTime;
	}
	/**
	 * 产生随机数
	 *
	 * @param int $length
	 * @param int $numeric
	 * @return int
	 */
	static public function random($length, $numeric = 0) 
	{
		PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
		$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) 
		{
			$hash .= $seed[mt_rand(0, $max)];
		}
		return $hash;
	}
	/**
	 * 生成加密的key
	 * @return Array
	 */
	static public function create()
	{
		$random = self::random(10, 0);
		$expired = time() + self::$_expiredTime;
		$sign = md5($expired . self::$_privateKey . $random);
		$sid =  array('qii_security_time' => $expired, 'qii_security_sid' => substr($sign, 0, self::$_signLength) . $random . substr($sign, -1* self::$_signLength));
		return $sid;
	}
	/**
	 * 获取安全加密串
	 * @return string
	 */
	static public function getSecurity()
	{
		return join('.', self::create());
	}
	/**
	 * 验证安全串
	 * @param string $key
	 */
	static public function validate($sid)
	{
		$now = time();//当前时间
		$time = $sid['qii_security_time'];
		$key = $sid['qii_security_sid'];
		
		$random = substr($key, 0 + self::$_signLength, -1* self::$_signLength);
		$sign = md5($time . self::$_privateKey . $random);

		//如果过期时间为0，则永不过期
		if(self::$_expiredTime == 0)
		{
			$time = $now + 10;
		}
		
		if(substr($sign, 0, self::$_signLength) . $random . substr($sign, -1* self::$_signLength) == $key && $time > $now)
		{
			return true;
		}
		return false;
	}
	/**
	 * 验证安全串
	 * @param String $sid
	 * @return boolean
	 */
	static public function validateSecurity($sid)
	{
		$sids= array();
		$sidArray = explode('.', $sid);
		$sids['qii_security_time'] = $sidArray[0];
		$sids['qii_security_sid'] = $sidArray[1];
		return self::validate($sids);
	}
}