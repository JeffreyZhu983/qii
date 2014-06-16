<?php
/**
 *
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: ip.tools.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 获取用户的ip地址
 * 
 */
class ip_sys_tools
{
	public $version = '1.1.0';
	public function __construct()
	{
		return $this;
	}
	public function __toString()
	{
		return $this->getIPAddress();
	}
	public function getLongAddress()
	{
		return ip2long($this->getIPAddress());
	}
	public function getIPAddress()
	{
		if($_SERVER["HTTP_CDN_SRC_IP"])
		{
			return $_SERVER["HTTP_CDN_SRC_IP"];
		}	
		if ($_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['REMOTE_ADDR']) {
			$proxies = preg_split ( '/[\s,]/', "", - 1, PREG_SPLIT_NO_EMPTY );
			$proxies = is_array ( $proxies ) ? $proxies : array ($proxies );
			
			$ipAddress = isset($_SERVER ['HTTP_X_FORWARDED_FOR']) ? $_SERVER ['HTTP_X_FORWARDED_FOR'] : $_SERVER ['REMOTE_ADDR'];
		} elseif ($_SERVER['REMOTE_ADDR'] and $_SERVER['HTTP_CLIENT_IP']) {
			$ipAddress = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif ($_SERVER ['REMOTE_ADDR']) {
			$ipAddress = $_SERVER ['REMOTE_ADDR'];
		} elseif ($_SERVER['HTTP_CLIENT_IP']) {
			$ipAddress = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
			$ipAddress = $_SERVER ['HTTP_X_FORWARDED_FOR'];
		}
		
		if ($ipAddress === FALSE) 
		{
			$ipAddress = '0.0.0.0';
			return $ipAddress;
		}
		if (strstr ( $ipAddress, ',' ))
		{
			$x = explode ( ',', $ipAddress );
			$ipAddress = trim ( end ( $x ) );
		}
		return $ipAddress;
	}
}
?>