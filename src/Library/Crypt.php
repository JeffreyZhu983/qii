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
 * 带过期时间加密
 * echo $crypt->encryptWithTime('加密串', 过期时间,单位：秒);
 * 解密字符串
 * echo $crypt->decryptWithTime('需要解密的字符串');
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
     * 带过期时间加密
     *
     * @param string $string 加密串
     * @param int $expired 过期时间，单位 : 秒
     * @return string
     * @throws \Exception
     */
	public function encryptWithTime($string, $expired = 100)
    {
        if($expired > 86400) {
            throw new \Exception('过期时间不能大于 86400秒');
        }
        $expiredAt = time() + $expired;
        $string = $expiredAt . $string;

        return $this->encrypt($string);
    }
    /**
     * 解密后验证是否已经过期, 过期后返回空字符串
     *
     * @param  string $string 需要解密的字符串
     * @return bool|string
     */
    public function decryptWithTime($string)
    {
        $passCrypt = $this->decrypt($string);
        if(!$passCrypt) return '';

        $expiredAt = substr($passCrypt, 0, 10);

        if(time() > $expiredAt) return  '';

        return substr($passCrypt, 10);
    }

    /**
     * 检查加密串是否已经过期
     *
     * @param string $string
     * @return bool
     */
    public function checkDecryptExpired($string)
    {
        $passCrypt = $this->decrypt($string);
        if(!$passCrypt) return false;

        $expiredAt = substr($passCrypt, 0, 10);

        if(time() > $expiredAt) return  false;

        return true;
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
		$string = $this->verifyString($string);
		if(!$string)
        {
            return '';
        }
		$string = base64_decode($string);
		$passCrypt = openssl_decrypt($string, 'aes-256-cbc', $this->securityKey, OPENSSL_RAW_DATA, $this->getIv());
		return substr($passCrypt, 10);
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