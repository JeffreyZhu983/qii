<?php
/**
 * 检测用户是否是通过代理服务器访问
 *
 * 用法：
 * $proxyDetected = \Qii\Library\new ProxyDetected();
 * $isProxy = $proxyDetected->isProxy();
 */
namespace Qii\Library;

class ProxyDetected
{
    
    // HTTP-Proxy-Headers
    protected $proxyHeaders = array(
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_REAL_IP',
        'HTTP_X_CLIENT_IP',
        'HTTP_X_FORWARDED_HOST',
        'HTTP_X_FORWARDED_SERVER',
        'HTTP_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'HTTP_VIA',
        'HTTP_FORWARDED',
        'HTTP_FORWARDED_FOR_IP',
        'VIA',
        'X_FORWARDED_FOR',
        'FORWARDED_FOR',
        'X_FORWARDED',
        'FORWARDED',
        'CLIENT_IP',
        'FORWARDED_FOR_IP',
        'HTTP_PROXY_CONNECTION'
    );
    
    //Ports to scan
    protected $scanPorts = array(
        80,
        443,
        3128,
        8080,
    );
    
    
    //List of ports to compare with the remoteport.
    protected $ports = array(
        78,
        79,
        80,
        81,
        82,
        83,
        443,
        3128,
        8080,
        8081,
        8090,
        8181,
        8282,
        8888,
        9050,
        9999
    );
    
    function isProxy($ip = null)
    {
        if(!$ip){
            $ip = $_SERVER["REMOTE_PORT"];
        }
        foreach ($this->proxyHeaders as $header) {
            if (isset($_SERVER[$header])) {
                return true;
            }
        }
        
        foreach ($this->scanPorts as $port) {
            if (@fsockopen($ip, $port, $errstr, $errno, 1)) {
                return true;
            }
        }
        
        foreach ($this->ports as $port) {
            if ($ip == $port) {
                return true;
            }
        }
        return false;
    }
}