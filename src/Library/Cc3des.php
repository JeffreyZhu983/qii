<?php
/**
*
* PHP版3DES加解密类
*
* 可与java的3DES(DESede)加密方式兼容
*
* @Author:蓝凤(ilanfeng.com)
*
* @version: V0.1 2011.02.18
*
*/
namespace Qii\Library;

class Cc3des{
 
    //加密的时候只用替换key就行了，ecb模式不需要提供iv值
    public $key    = "0123456789QWEQWEEWQQ1234";
    public $iv    = "33889955"; //like java: private static byte[] myIV = { 50, 51, 52, 53, 54, 55, 56, 57 };
 
    //解密
    public function decrypt($string) {
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        srand();
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $key = substr($this->key, 0, mcrypt_enc_get_key_size($td));
        mcrypt_generic_init($td, $key, $iv);
        $value = @pack("H*", $string);
        $ret = trim(mdecrypt_generic($td, $value));
        // 去掉多余的补位
        $ret = $this->pkcs5_unpad($ret);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }
 
    //加密
    public function encrypt($value) {
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        srand();
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $key = substr($this->key, 0, mcrypt_enc_get_key_size($td));
        mcrypt_generic_init($td, $key, $iv);
        $value = $this->pkcs5_pad($value, mcrypt_get_block_size(MCRYPT_3DES, 'ecb'));
        $ret = mcrypt_generic($td, $value);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return strtoupper(bin2hex($ret));
    }
 
    /*
     * 位数补齐
     */
    private function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
     
    /*
     * 去除补位
     */
    private function pkcs5_unpad($text) {
        $pad = ord($text{strlen($text) - 1});
        if($pad > strlen($text)) {
            return false;
        }
        if(strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}