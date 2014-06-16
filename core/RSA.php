<?php

/**
 * Rsa加密类
 *
 */

class RSA {
	
	/**
	 * 创建公钥和私钥
	 */
	static public function createKey(){
		//php不能用于生产公钥、私钥(效率太低)，同时没有安装crypt_rsa()
		
		$rsaClass = new Crypt_RSA();
		$rsaClass->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		return $rsaClass->createKey();
		
		return false;
	}
	
	/**
	 * 根据KEY对字符串进行加密
	 *
	 * @param unknown_type $rsaKey
	 * @param unknown_type $string
	 * @return unknown
	 */
	static public function encrypt($rsaKey, $string, $type = 'private') {
		$return = null;
		
		if ($type == 'public') {
			$rsaKey = self::formatPublicKey($rsaKey);
			$rsaKeyId = openssl_get_publickey($rsaKey);
			openssl_public_encrypt($string, $return, $rsaKeyId);
		} else {
			$rsaKey = self::formatPrivateKey($rsaKey);
			$rsaKeyId = openssl_get_privatekey($rsaKey);
			openssl_private_encrypt($string, $return, $rsaKeyId);
		}
		
		return $return;
	}
	
	/**
	 * 根据KEY对字符串进行解密
	 * 
	 * @param unknown_type $reaKey
	 * @param unknown_type $string
	 * @return unknown
	 */
	static public function decrypt($rsaKey, $string, $type = 'public') {
		$return = null;
		
		if ($type == 'private') {
			$rsaKey = self::formatPrivateKey($rsaKey);
			$rsaKeyId = openssl_get_privatekey($rsaKey);
			openssl_private_decrypt($string, $return, $rsaKeyId);
		} else {
			$rsaKey = self::formatPublicKey($rsaKey);
			$rsaKeyId = openssl_get_publickey($rsaKey);
			openssl_public_decrypt($string, $return, $rsaKeyId);
		}
		return $return;
	}
	
	/**
	 * 签名检验
	 * @param unknown_type $rsaKey
	 * @param unknown_type $string
	 * @param unknown_type $sign
	 * @return number
	 */
	static public function verify($rsaKey, $string, $sign){
		$rsaKey = self::formatPublicKey($rsaKey);
		
		$rsaKeyId = openssl_get_publickey($rsaKey);
		return openssl_verify($string, $sign, $rsaKeyId);
	}
	
	static public function sign($rsaKey, $string){
		$rsaKey = self::formatPrivateKey($rsaKey);
	
		$rsaKeyId = openssl_get_privatekey($rsaKey);
		$sign = null;
		openssl_sign($string, $sign, $rsaKeyId);
		return $sign;
	}
	
	/**
	 * 去掉KEY里面的换行
	 * @param unknown_type $key
	 * @return mixed
	 */
	static public function formatKey($key){
		$array = array(
				'-----BEGIN PRIVATE KEY-----',
				'-----BEGIN PUBLIC KEY-----',
				'-----END PRIVATE KEY-----',
				'-----END PUBLIC KEY-----',
				"\n", ' '
		);
		return str_replace($array, '', $key);
	}
	
	/**
	 * 格式化公钥
	 * @param unknown_type $key
	 * @return string
	 */
	static public function formatPublicKey($key){
		return "-----BEGIN PUBLIC KEY-----\n"
			. wordwrap($key, 64, "\n", true)
			. "\n-----END PUBLIC KEY-----";
	}
	
	/**
	 * 格式化私钥
	 * @param unknown_type $key
	 * @return string
	 */
	static public function formatPrivateKey($key){
		return "-----BEGIN RSA PRIVATE KEY-----\n"
			. wordwrap($key, 64, "\n", true)
			. "\n-----END RSA PRIVATE KEY-----";
	}
}

