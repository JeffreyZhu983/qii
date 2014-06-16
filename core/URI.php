<?php
/**
 * URI
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: URI.php 188 2011-08-01 09:18:34Z zjh $
 * 
 * useage:
 * $this->Qii('URI');
 * $this->URI->GET('control');
 */
if(class_exists('URI'))
{
	return;
}
class URI
{
	public $version = '1.1.0';
	public $uriFormat = 'normal';

	public function method($method)
	{
		if($_SERVER['REQUEST_METHOD'] == strtoupper($method))
		{
			return true;
		}
		return false;
	}
	/**
	 * GET Method
	 *
	 * @param String $name
	 * @return MIX
	 */
	public function GET($name)
	{
		return self::filter(Qii::load('Router')->getParam($name));
	}
	/**
	 * GET Method
	 *
	 * @param String $name
	 * @return MIX
	 */
	public function POST($name)
	{
		if($_POST[$name]) return self::filter($_POST[$name]);
	}
	/**
	 * Segment
	 * 
	 * 获取_GET[$key]的值;
	 *
	 */
	public function segment($key)
	{
		$segment = Qii::getPrivate('qii_segment');
		if($key == '' && !is_int($key))
		{
			return $segment;
		}
		if(is_int($key) && isset($segment[$key]))
		{
			$i = 0;
			foreach ($segment AS $k => $value)
			{
				if($i == $key && $k != 'sysfileExtension')
				{
					return urldecode($value);
				}
				$i++;
			}
		}
		if(isset($segment[$key])) return urldecode($segment[$key]);
	}
	/**
	 * 过滤传入的字符
	 *
	 * @param Mix $data
	 * @return Mix
	 */
	public function filter($data)
	{
		if(!get_magic_quotes_gpc()) 
		{
			return is_array($data)? call_user_func_array(array($this, 'filter'), $data) : htmlspecialchars(addslashes($data));

		}
		return $data;
	}
	/**
	 * 实现parseURL功能
	 *
	 * @param String $url
	 * @param String $pageName
	 * @return Array
	 */
	static public function parseURL($url)
	{
		return parse_url($url);
	}
	/**
	 * 实现跳转
	 *
	 * @param String $url
	 * @param int $time
	 * @param int $msg
	 * @return Array
	 */
	public function location($url, $time = 0, $msg = '')
	{
		if($url == '')
		{
			$url = $_SERVER['HTTP_REFERER'];
		}
		//如果已经有header输出就用js重定向
		if(headers_sent())
		{
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if($time > 0 && $msg)
			{
				Qii::showMessage($msg, 0);
			}
			die();
		}
		else 
		{
			header("Content-Type:text/html; charset=UTF-8");
			if(0 === $time)
			{
				header("Location:{$url}");
           		die();
			}
			else 
			{
				header("Refresh:{$time};url={$url}");
				if($msg)
				{
					Qii::showMessage($msg);
				}
           		die();
			}
		}
	}
	
}