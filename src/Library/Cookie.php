<?php
namespace Qii\Library;
/**
 * 设置Cookie，cookie内容将会被加密
 */
class Cookie
{
	const VERSION = '1.2';
	/**
	 * @var string $prefix cookie保存的前缀
	 */
	private $prefix = '_';
	/**
	 * cookie的过期时间
	 */
	private $expire = 86400;

	private $securityKey = 'qii.v.1.3';

	public function __construct()
	{
		return $this;
	}

	/**
	 * 设置用于加密解码的密匙
	 * @param $key
	 */
	public function setSecurityKey($key)
	{
		$this->securityKey = $key;
		return $this;
	}

	/**
	 * 设置cookie
	 * @param string $name cookie名
	 * @param string $val cookie值
	 * @param int $expire 过期时间，默认为一天
	 */
	public function set($name, $val, $expire = 0)
	{
		if ($expire <= 0) $expire = $this->expire;
		$crypt = new \Qii\Library\Crypt();
		$crypt->setSecurityKey($this->securityKey);
		$val = trim($crypt->encrypt(urlencode($val)));
		setcookie($this->prefix . $name, $val, time() + $expire, '/');
	}

	/**
	 * 获取cookie
	 */
	public function get($name)
	{
		$val = isset($_COOKIE[$this->prefix . $name]) ? $_COOKIE[$this->prefix . $name] : '';
		if (!$val) return '';
		$crypt = new \Qii\Library\Crypt();
		$crypt->setSecurityKey($this->securityKey);
		return trim(urldecode($crypt->decrypt($val)));
	}
}