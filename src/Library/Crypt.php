<?php
namespace Qii\Library;
/**
 * \Qii\Library\Crypt
 * @author Jinhui Zhu<zhujinhui@zhangyue.com>2015-11-09 16:07
 *
 * 加密类
 * 用法：
 * $crypt = new \Qii\Library\Crypt();
 * 设置密钥
 * $crypt->setSecurityKey('密钥');
 * 加密字符串
 * echo $crypt->encrypt('加密字符串');
 * 解密字符串
 * echo $crypt->decrypt('解密字符串');
 */
class Crypt
{
	const VERSION = '1.2';
	//密匙
	private $securityKey = 'qii.v.1.3';
	private $keyLength = 4;
	private $iv = 'sdEKw2wJCnctEG09';

	public function __construct()
	{
		if (!function_exists('openssl_encrypt')) {
			throw new \Exception(\Qii::i(1008, 'openssl_encrypt'), __LINE__);
		}
		$this->setSecurityKey($this->securityKey);
		return $this;
	}

	/**
	 * 设置用于加密解码的密匙
	 * @param $key
	 */
	public function setSecurityKey($key)
	{
		if(!$key) return;
		$len = strlen($key);
		if ($len < 16) $key = str_pad($key, 16, '.');
		if($len > 16) $key = substr($key, 0, 16);
		$this->securityKey = $key;
		return $this;
	}

	/**
	 * 设置iv字符串
	 */
	public function setIv($iv)
	{
		if(strlen($iv) > 16) $iv = substr($iv, 0, 16);
		if(strlen($iv) < 16) $iv = str_pad($iv, 16, '.');
		$this->iv = $iv;
	}
	/**
	 * 获取iv字符串
	 */
	public function getIv()
	{
		$this->iv = $this->iv;
		if(!$this->iv < 16) $this->iv = str_pad($this->iv, 16, '.');
		if(strlen($this->iv) == 16) return $this->iv;
		return substr($this->iv, 0, 16);
	}


	/**
	 * 加密字符
	 * @param $string
	 * @return string
	 */
	public function encrypt($string)
	{
		$string = time() . $string;
		$passcrypt = openssl_encrypt($string, 'aes-256-cbc', $this->securityKey, OPENSSL_RAW_DATA, $this->getIv());
		return $this->getVerifyString(base64_encode($passcrypt));
	}

	/**
	 * 解密字符
	 *
	 * @param $string
	 * @return string
	 */
	public function decrypt($string)
	{
		$string = str_replace(' ', '+', $string);
		$string = base64_decode($this->verifyString($string));

		$passcrypt = openssl_decrypt($string, 'aes-256-cbc', $this->securityKey, OPENSSL_RAW_DATA, $this->getIv());
		return substr($passcrypt, 10);
	}

	/**
	 * 将字符串做数字签名
	 *
	 * @param $string
	 * @return string
	 */
	public function getVerifyCode($string)
	{
		return substr(md5($string), -1 * $this->keyLength);
	}

	/**
	 * 生成签名字符串并返回 签名+字符串
	 *
	 * @param $string
	 * @return string
	 */
	public function getVerifyString($string)
	{
		return $this->getVerifyCode($string) . $string;
	}

	/**
	 * 验证字符创的数字签名,如果没有通过就返回空字符，否则返回去掉签名的字符
	 *
	 * @param $string
	 * @param $code
	 * @return bool
	 */
	public function verifyString($string)
	{
		$verifyCode = substr($string, 0, $this->keyLength);
		if ($this->getVerifyCode(substr($string, $this->keyLength)) != $verifyCode) return '';
		return substr($string, $this->keyLength);
	}
}