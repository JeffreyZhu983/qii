<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Router.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 
 * 路由类
 * 
 */
if(class_exists('Router'))
{
	return;
}
class Router
{
	public $version = '1.1.0';
	/**
	 * URL 模式
	 *
	 * @var String
	 */
	private $_mode = 'normal';
	/**
	 * 链接字符
	 *
	 * @var String
	 */
	private $_symbol = '&';
	/**
	 * 文件后缀
	 *
	 * @var String
	 */
	private $_extenstion = '.html';
	/**
	 * 允许设置的URL模式
	 *
	 * @var Array
	 */
	private $_allow_mode = array('normal', 'middle', 'short');
	
	private $_urlRewrite = "";
	
	/**
	 * 是否去掉文件名称，如果rewrite不在根目录的时候将此设为true，避免生成URL错误.
	 *
	 * @var Bool
	 */
	private $_fileNameTrim = false;
	
	public function URI($Array, $fileName = '', $extenstion = '', $trimExtension = false)
	{
		$this->checkMode($this->_mode);
		if(empty($fileName))
		{
			$fileName = $_SERVER['SCRIPT_NAME'];
		}
		if(sizeof($Array) == 0)
		{
			return '';
		}
		//2012-03-25新增 path，如果path==$fileName就去掉path，从而避免多个rewrite的时候调用此方法生成的URL错误。
		$path = $this->getPathInfo();
		//去掉文件名并保留路径 去掉加路径出现的bug
		if($this->_fileNameTrim)
		{
			$fileName = rtrim(str_replace(basename($fileName), '', $fileName), "/");
		}
		$notAllowPath = array($fileName, "\\");
		if(in_array($path, $notAllowPath))
		{
			$path = "";
		}
		$realPath = rtrim(str_replace('//', '/', $path . $fileName), '/');
		if($this->_mode == 'normal')
		{
			return $fileName . '?'. $this->{$this->_mode}($Array);
		}
		if(!empty($extenstion))
		{
			return $realPath .$this->_symbol. $this->{$this->_mode}($Array) . ($trimExtension ? '' : $extenstion);
		}
		return $realPath .$this->_symbol. $this->{$this->_mode}($Array) . ($trimExtension ? '' : $this->_extenstion);
	}
	/**
	 * 获取本类中的私有属性
	 *
	 * @param String $key
	 * @return Mix
	 */
	public function get($key)
	{
		return $this->{$key};
	}
	public function getWebHost()
	{
		return 'http://' . rtrim(rtrim(str_replace('//', '/', $_SERVER['HTTP_HOST']), '/'), "\\") . ($_SERVER['SERVER_PORT'] != '80' ? ':' . $_SERVER['SERVER_PORT'] : '');
	}
	public function getDomain()
	{
		return rtrim(rtrim(str_replace('//', '/', $_SERVER['HTTP_HOST']), '/'), "\\");
	}
	/**
	 * 
	 * 获取当前页面URL
	 */
	public function getCurrentURL()
	{
		return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
	/**
	 * 
	 * 获取当前页面的路径相关信息
	 * @param String $path
	 * @param String $index
	 */
	public function getPathInfo($path = '', $index = 'dirname')
	{
		if(empty($path)) $path = $_SERVER['SCRIPT_NAME'];
		//Array ( [dirname] => /Qii [basename] => index.php [extension] => php [filename] => index )
		$pathInfo = pathinfo($path);
		$pathInfo['dirname'] = str_replace("\\", "/", $pathInfo['dirname']);
		if(!empty($index))
		{
			return $pathInfo[$index];
		}
		return $pathInfo;
	}
	/**
	 * 
	 * 获取当前路径
	 * @param String $path
	 */
	public function getPath($path = '')
	{
		if(empty($path))
		{
			$path = $_SERVER['SCRIPT_NAME'];
		}
		return  substr($path, 0, (strrpos($path, '/')));
	}
	/**
	 * 
	 * 设置是否去掉文件名字
	 * @param Bool $trim
	 */
	public function setTrim($trim = false)
	{
		$this->_fileNameTrim = $trim;
	}
	/**
	 * 设置文件后缀名字
	 *
	 * @param String $extenstion
	 */
	public function setExtenstion($extenstion)
	{
		if(!empty($extenstion)) $this->_extenstion = $extenstion;
	}
	/**
	 * 设置连接字符串
	 *
	 * @param String $symbol
	 */
	public function setSymbol($symbol)
	{
		if(!empty($symbol)) $this->_symbol = $symbol;
	}
	/**
	 * 设置URI模式
	 *
	 * @param String $mode
	 */
	public function setMode($mode)
	{
		$this->checkMode($mode);
		$this->_mode = $mode;
	}
	/**
	 *
	 * 增加命令行模式
	 * 支持normal, middle, short格式，备注如果是"&"符号需要换成"/"
	 * 格式如下：
	 * php -q index.php control=index/action=home/id=100 (等于 index.php?control=index&action=home&id=100) normal
	 * php -q index.php control/index/action/home/id/100 middle
	 * php -q index.php index/action/100 short
	 */
	public function parseArgvs($param)
	{
		$this->checkMode($this->_mode);
		//CMD Mode like this : php -q index.php control=index/action=home/id=100
		$fun = $this->_mode . "parseArgvs";
		return $this->{$fun}($param);
	}
	/**
	 * 普通模式下的
	 *
	 * @param String $param
	 * @return Array
	 */
	public function normalParseArgvs($param)
	{
		if(empty($param)) reutrn;
		$argvArray = explode("/", $param);
		$return = array();
		if(is_array($argvArray))
		{
			foreach($argvArray AS $arg)
			{
				$args = explode("=", $arg);
				$return[$args[0]] = $args[1];
			}
		}
		return $return;
	}
	/**
	 * 中等模式
	 *
	 * @param String $param
	 * @return Array
	 */
	public function middleParseArgvs($param)
	{
		if(empty($param)) reutrn;
		$argvArray = explode("/", $param);
		$return = array();
		if(is_array($argvArray))
		{
			foreach($argvArray AS $arg)
			{
				$args = explode("/", $arg);
				$return[$args[0]] = $args[1];
			}
		}
		foreach ($_GET AS $key => $val)
		{
			$return[$key] = $val;
		}
		return $return;
	}
	/**
	 * 精简模式
	 *
	 * @param String $param
	 * @return Array
	 */
	public function shortParseArgvs($param)
	{
		if(empty($param)) reutrn;
		$argvArray = explode("/", $param);
		$return = array();
		if(is_array($argvArray))
		{
			foreach($argvArray AS $arg)
			{
				$return[] = $arg;
			}
		}
		foreach ($_GET AS $key => $val)
		{
			$return[$key] = $val;
		}
		return $return;
	}
	/**
	 * 获取参数
	 *
	 * @param String $fileName
	 * @param String $url
	 * @param String $key
	 * @return Mix
	 */
	public function getParam($key = '', $url = '', $fileName = '', $extension = '')
	{
		$this->checkMode($this->_mode);
		//如果是命令行模式
		$argv = array();
		if(isset($_SERVER['argv'])) $argv = $_SERVER['argv'];
		//修正部分服务器Rewrite 后再加参数不识别的问题（直接进入命令行的模式）
		if($argv && $_SERVER['PHP_SELF'] == $_SERVER['SCRIPT_NAME'])
		{
			array_shift($argv);
			$_AGRV = $this->parseArgvs($argv[0]);
			if($_AGRV)
			{
				if(empty($key)) return $_AGRV;
				return $_AGRV[$key];
			}
			return array();
		}
		if($this->_mode == 'normal')
		{
			if(empty($key)) return $_GET;
			return $_GET[$key];
		}
		if(!isset($_SERVER['PATH_INFO'])) $_SERVER['PATH_INFO'] = '';
		if(empty($url))
		{
			//修正Rewrite以指定目录开头的bug 将$url = $_SERVER['REQUEST_URI'];替换成以下的
			$url = (($_SERVER['PATH_INFO'] != '/') ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI']);
		}
		if(empty($fileName))
		{
			$fileName = $_SERVER['SCRIPT_NAME'];
		}
		//取fileName后边的内容
		$url = str_replace($fileName, "", $url);
		
		$param = parse_url($url);
		//如果到?号的URL则取query，没有?则取path的basename($path);
		if(substr($param['path'], 0, 1) == '?')
		{
			//拆分字符
			if(empty($param['query'])) 
			{
				return '';
			}
			$query = $param['query'];
		}
		else 
		{
			$query = $param['path'];
		}

		$query = $this->comparePath($query, $fileName);
		/*
		if(!empty($extension))
		{
			$query = preg_replace("/\\$extenstion$/", "", $query);
		}
		else 
		{
			$query = preg_replace("/\\$this->_extenstion$/", "", $query);
		}*/
		//添加系统扩展名到返回数组中 2011-10-14 15:26
		preg_match("/(.*)\.(.*)$/", $query, $extenstion);
		//去掉文件后缀名称，修改时间2010-09-03 22:33，以便指定任意后缀名。
		if(!isset($extenstion[2])) $extenstion[2] = '';
		$query = preg_replace("/\.{$extenstion[2]}$/", "",  $query);
		$paramArray = explode($this->_symbol, $query);
		//array_shift($paramArray);
		$fun = $this->_mode. 'Decode';
		$v = $this->{$fun}($paramArray);
		//添加系统扩展名到返回数组中 2011-10-14 15:26
		$v['sysfileExtension'] = $extenstion[2];
		if($_GET) $v = array_merge($v, $_GET);
		if($key != '')
		{
			return $v[$key];
		}
		return $v;
	}
	/**
	 * 对比转发文件的路径
	 *
	 * @param String $path
	 * @param String $scriptName
	 * @return String
	 */
	public function comparePath($path, $f)
	{
		//去掉basename($f)再进行比较,比较的时候将字符串转换成小写;
		$basename = basename($f);
		$path = str_replace($basename, "", $path);
		$f = str_replace($basename, "", $f);
		//对比parseURL后的Path
		$pathArray = explode('/', ltrim(rtrim($path, '/'), '/'));
		$fArray = explode('/', ltrim(rtrim($f, '/'), '/'));
		$fCount = count($fArray);
		$tmpArray = array();
		$tmpArray = $pathArray;
		for($i = 0; $i< $fCount; $i++)
		{
			if(strtolower($pathArray[$i]) != strtolower($fArray[$i]))
			{
				break;
			}
			array_shift($tmpArray);
		}
		return join("/", $tmpArray);
	}
	/**
	 * 普通模式的URL
	 *
	 * @param Array $urlArray
	 * @param String $k
	 * @return Mix
	 */
	public function normalDecode($urlArray, $k = '')
	{
		if(!empty($k)) return $_GET[$k];
		return $_GET;
	}
	/**
	 * 普通路径
	 *
	 * @param Array $Array
	 * @return Array
	 */
	public function normal($Array)
	{
		$urlArray = array();
		foreach ($Array AS $k => $v)
		{
			if(empty($v))
			{
				continue;
			}
			$urlArray[] = $k . '=' . $v;
		}
		return join($this->_symbol, $urlArray);
	}
	/**
	 * 中等长度下解密字符串
	 *
	 * @param Array $urlArray
	 * @return String
	 */
	public function middleDecode($urlArray)
	{
		$urlArrayZie = sizeof($urlArray);
		for($i=0; $i< $urlArrayZie; $i=$i+2)
		{
			if($urlArray[$i+1] == 'NULL')
			{
				continue;
			}
			$url[$urlArray[$i]] = $urlArray[$i+1];
		}
		return $url;
	}
	/**
	 * 中等长度路径 /index.php/<1>control/System</1>/<2>action/checkEnvironment</2>.html
	 *
	 * @param Array $Array
	 * @return Array
	 */
	public function middle($Array)
	{
		$urlArray = array();
		foreach ($Array AS $k=>$v)
		{
			if(empty($v))
			{
				continue;
			}
			$urlArray[] = $k . $this->_symbol . $v;
		}
		return join($this->_symbol, $urlArray);
	}
	/**
	 * 短URL类型
	 *
	 * @param Array $urlArray
	 * @return Array
	 */
	public function shortDecode($urlArray, $k = '')
	{
		if(!empty($k)) return $urlArray[$k] == 'NULL' ? '' : $urlArray[$k];
		return $urlArray;
	}
	/**
	 * short mode /index.php/<control>System</control>/<action>checkEnvironment</action>.html
	 *
	 * @param Array $Array
	 * @return String
	 */
	public function short($Array)
	{
		$urlArray = array();
		foreach ($Array AS $v)
		{
			if(empty($v) && is_int($v))
			{
				continue;
			}
			$urlArray[] = $v;
		}
		return join($this->_symbol, $urlArray);
	}
	/**
	 * 检查生成链接模式
	 *
	 * @param String $mode
	 */
	public function checkMode($mode)
	{
		if(!in_array($mode, $this->_allow_mode))
		{
			Qii::error("链接模式错误，链接格式只能为 '<u><font color=\"green\">". join("', '", $this->_allow_mode) . "</font></u>'，当前模式为 '<font color=\"red\">".$mode."</font>'");
		}
	}
/**
	 * 过滤关键字
	 *
	 * @param String $k
	 * @return String 返回过滤了关键字以后的数据
	 */
	public function fileterKeywords($k)
	{
		$k = str_replace('-', ' ', $k);
		$k = str_replace('_', ' ', $k);
		$k = str_replace('*', ' ', $k);
		return str_replace('.', ' ', $k);
	}
	/**
	 * url encode
	 *
	 * @param String $url
	 * @return String
	 */
	public function urlencode($url)
	{
		$specialChars = Qii::loadFile('config.specialchars');
		if(!is_array($specialChars))
		{
			return $url;
		}
		foreach ($specialChars AS $k => $v)
		{
			$url = str_replace($k, $v, $url);
		}
		return $url;
	}
	/**
	 * 重定向 url
	 *
	 * @param string $url
	 * @param int $time
	 */
	public function redirect($url = '', $msg = '', $time = 0)
	{
		if($url == '')
		{
			$url = $_SERVER['HTTP_REFERER'];
		}
		//如果已经有header输出就用js重定向
		if(headers_sent())
		{
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if($time > 0 )
			{
				$str .= $msg;
			}
			die($str);
		}
		else 
		{
			header("Content-Type:text/html; charset=". OUTPUT_CHARSET);
			if(0 === $time)
			{
				header("Location:{$url}");
           		die();
			}
			else 
			{
				header("Refresh:{$time};url={$url}");
           		die($msg);
			}
		}
	}
}
?>