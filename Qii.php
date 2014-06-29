<?php
/**
 *
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Qii.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 */
ini_set("display_errors", "On");
/**
 * Qii 框架基本库所在路径
 */
define('Qii_DIR', dirname(__FILE__));
/**
 * DIRECTORY_SEPARATOR 的简写
 */
define('DS', DIRECTORY_SEPARATOR);
/**
 * 定义包含的路径分隔符
 */
define('PS', PATH_SEPARATOR);
/**
 * Qii 系统文件路径
 *
 */
define('QII_PATH_ARRAY', 'configure,core,example,helper,plugin,tools,view');
/**
 * 错误模式
 */
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
require_once(Qii_DIR . DS . 'core'. DS .'Benchmark.php');
Benchmark::set('site', false);
include(Qii_DIR . DS . 'core'. DS .'Error.php');
include(Qii_DIR . DS . 'core'. DS .'Language.php');
include(Qii_DIR . DS . 'core'. DS .'XML.php');
include(Qii_DIR . DS . 'core'. DS .'Arrays.php');
include(Qii_DIR . DS . 'core'. DS .'Helper.php');
include(Qii_DIR . DS . 'core'. DS .'Router.php');
include(Qii_DIR . DS . 'core'. DS .'Controller.php');
include(Qii_DIR . DS . 'core'. DS .'Status.php');
include(Qii_DIR . DS . 'core'. DS .'Session.php');
include(Qii_DIR . DS . 'core'. DS .'Security.php');
Qii::instance('Arrays');
Qii::instance('Status');

final class Qii
{
	static protected $_global;
	static public $language;
	static private $_securityKey = false;
	static private $_enableSecurity = false;
	/**
	 * 是否启用安全设置
	 * @param String $enabled
	 */
	static public function enableSecurity($key = null, $sid = null, $enabled = false)
	{
		self::$_securityKey = $key;
		self::$_enableSecurity = $enabled;
		if($enabled)
		{
			Security::setPrivateKey($sid);
		}
	}
	/**
	 * 设置安全码过期时间
	 *
	 * @param Int $expired
	 */
	static public function setSecurityExpired($expired = 0)
	{
		if(self::isEnableSecurity())
		{
			Security::setExpiredTime($expired);
		}
	}
	/**
	 * 是否开启了安全验证
	 */
	static public function isEnableSecurity()
	{
		return self::$_enableSecurity;
	}
	static public function getSecurityKey()
	{
		return self::$_securityKey;
	}
	/**
	 * 获取 Security Key
	 * @return String
	 */
	static public function getSecurity()
	{
		return Security::getSecurity();
	}
	/**
	 * 验证 Security string
	 * @param String $value 
	 * @return Book
	 */
	static public function validateSecurityKey($value)
	{
		return Security::validateSecurity($value);
	}
	/**
	 * Qii版本
	 *
	 * @return String
	 */
	static public function version()
	{
		return 'v1.0.1';
	}
	/**
	 * 此方法在5.3.0以上才能使用，否则报错
	 *
	 * @param String $method
	 * @param Mix $arguments
	 */
	static public function __callStatic($method, $arguments)
	{
		//如果PHP版本低于5.3就报错，就执行autoload方法
		if(version_compare(PHP_VERSION,"5.3.0",">="))
		{
			return  new $method($arguments);
		}
		self::setError(false, 101, array($method));
	}
	/**
	 * 获取文件的相关信息
	 *
	 */
	static public function getPath($path, $index = 'dirname')
	{
		//Array ( [dirname] => /Qii [basename] => index.php [extension] => php [filename] => index )
		$pathInfo = pathinfo($path);
		if('' != $index)
		{
			return $pathInfo[$index];
		}
		return $pathInfo;
	}
	/**
	 * 自动生成文件路径
	 *
	 * @param String $className
	 * @return String 文件路径
	 */
	static public function AutoPath($className)
	{
		if(self::getPrivate('AutoPath' . ucfirst($className)))
		{
			return self::getPrivate('AutoPath' . ucfirst($className));
		}
		/**
		 * 如果能在Core中找到就直接调用框架内文件
		 */
		if(file_exists(Qii_DIR . DS . 'core'. DS . ucfirst($className) .'.php'))
		{
			return Qii_DIR . DS . 'core'. DS . ucfirst($className) .'.php';
		}
		$siteInfo = self::getSiteInfo();
		$pathinfo = (array) explode('_', $className);
		/**
		 * 判断是否是配置文件中的路径
		 */
		$isSystem = $pathinfo[count($pathinfo) - 1];
		/**
		 * 分解class，从中将路径提取出来, 支持一下规则
		 * 
		 * 提取规则如下:
		 * new text() => ./text.php;new text_model => model/text.model.php; new text_new_model => model/new/text.new.model.php
		 */
		
		$realpath = array_reverse($pathinfo);
		array_pop($realpath);
		$fileName = join(".", $pathinfo) . '.php';
		$directPath = $fileName;
		if('' != $realpath) $directPath = join(DS, $realpath) .DS. $fileName;
		if(file_exists($directPath))
		{
			return $directPath;
		}
		if(isset($siteInfo[$isSystem]['path']) && in_array($isSystem, array('controller', 'model', 'view')))
		{
			$fullPath = $siteInfo[$isSystem]['path'] . DS . join('.', $pathinfo) .'.php';
			
			return $fullPath;
		}
		//如果直接路径没有找到相关文件就检查下边的
		$path = array_pop($pathinfo);
		if(substr($className, 0, 3) == 'Qii' && count($path) == 1)//如果是以Qii开头的就在框架的core目录中找
		{
			$fileName = $className . '.php';
			$fullPath = rtrim(Qii_DIR, '/') . DS . 'core'. DS . $className . '.php';
			return $fullPath;
		}
		//直接找第一级路径中是否有相关文件,如：new text_new_model => model/text.new.model.php
		$directPath = $path . DS . join('.', $pathinfo) . '.'.$path.'.php';
		if(file_exists($directPath))
		{
			return $directPath;
		}
		//如果pathinfo的倒数第二个值为sys就去将上级目录设置到框架所在目录
		$parentPath = '';
		if($pathinfo[sizeof($pathinfo)-1] == 'sys')//new auto_<strong>sys</strong>_plugin() => <strong>Qii_DIR</strong> . DS . 'plugin'. DS . 'auto.plugin.php'
		{
			array_pop($pathinfo);
			$parentPath = rtrim(Qii_DIR, '/') . DS;
		}
		if($pathinfo[sizeof($pathinfo)-1] == 'Qii')
		{
			$fileName = $className . '.php';
			$fullPath = rtrim(Qii_DIR, '/') . DS . 'core'. DS . $className . '.php';
		}
		elseif(in_array($path, (array)self::getPrivate('qii_search_path')) || in_array($path, explode(',', QII_PATH_ARRAY)))
		{
			$fileName = join('_', $pathinfo). '.'. $path . '.php';
			$fullPath = $parentPath . $path . DS . $fileName;
		}
		else
		{
			$fileName = $className . '.'.$siteInfo['class']['ext'].'.php';
			$fullPath = rtrim($siteInfo['class']['path'], '/') . DS . $fileName;
		}
		/**
		 * 如果是smarty的话就加载Smarty的目录
		 */
		if(strtolower(substr($className, 0, 16)) === 'smarty_internal_' || strtolower($className) == 'smarty_security' || strtolower(substr($className, 0, 16)) == 'smarty_resource_')
		{
			$fullPath = SMARTY_SYSPLUGINS_DIR . strtolower($className) .'.php';
		}
		self::setPrivate(self::getPrivate('AutoPath' . ucfirst($className)), $fullPath);
		return $fullPath;
	}
	/**
	 * 自动加载
	 *
	 * @param String $className
	 */
	static public function __autoload($className)
	{
		//如果已经存在class就不用再加载文件
		if (class_exists($className, false) || interface_exists($className, false)) 
		{
        	return;
    	}
		$siteInfo = self::getSiteInfo();
		$fullPath = self::AutoPath($className);
		if(is_file($fullPath))
		{
			self::requireOnce($fullPath);
			return self::setError(!(!class_exists($className, false) && !interface_exists($className, false)), 103, array($className));
		}
		//查找以 $className 开始的文件
		$fileName = str_replace("_", ".", $className) .'.php';
		$isFind = false;
		//如果设置了搜索目录的话，在指定路径下没找到文件就搜索指定目录
		if(self::getPrivate('qii_is_search_path'))
		{
			foreach (self::getPrivate('qii_search_path') AS $path)
			{
				$fullPath = rtrim($path, '/') . DS . $fileName;
				if(is_file($fullPath))
				{
					self::requireOnce($fullPath);
					return self::setError(!(!class_exists($className, false) && !interface_exists($className, false)), 103, array($className));
					$isFind = true;
					break;
				}
			}
			if(count(self::getPrivate('qii_search_path')) == 0)
			{
				return self::setError($isFind, 105, array(join('、', self::getPrivate('qii_search_path')), $fullPath));
			}
			return self::setError($isFind, 105, array(join('、', self::getPrivate('qii_search_path')), $fileName, $fileName));
		}
	}
	/**
	 * 设置时区
	 * @param String $timeZone
	 */
	static public function getTimeZone($timeZone)
	{
		new timezone_sys_tools($timeZone);
	}
	/**
	 * 设置页面字符集
	 * @param String $contentType
	 * @param String $charset
	 */
	static public function setCharset($contentType = '', $charset = '')
	{
		new charset_sys_tools($contentType, $charset);
	}
	/**
	 * 分发
	 * @param String $controller
	 * @param String $action
	 * @param $argvs 参数，传递给action的，$action后边所有的参数都将传递给$action方法
	 */
	static public function dispatch($controller = '', $action = '')
	{
		//获取dispatch的参数
		$funArgvs = func_get_args();
		$argvs = array();
		if(count($funArgvs) > 2)
		{
			$argvs = array_slice($funArgvs, 2);
		}
		
		self::loadError();
		
		//加载系统helper
		Qii::instance('Helper');
		Qii::load('Helper')->loadHelper(Qii_DIR);
		
		//加载网站helper
		Qii::load('Helper')->loadHelper('.'. DS);

		//获取网站的基本配置
		$siteInfo = self::getSiteInfo();
		//设置区域
		self::getTimeZone($siteInfo['status']['timezone']);
		//设置页面字符集
		self::setCharset($siteInfo['status']['contentType'], $siteInfo['status']['charset']);

		self::Router();//加载Controller
		if(isset($siteInfo['status']['security']) && $siteInfo['status']['security']['enable'] == 1)
		{
			$key = null;
			if(isset($siteInfo['status']['security']['name'])) $key = $siteInfo['status']['security']['name'];
			self::enableSecurity($key, $siteInfo['status']['security']['key'], $siteInfo['status']['security']['enable']);
			if(isset($siteInfo['status']['security']['expired']))
			{
				self::setSecurityExpired((int) $siteInfo['status']['security']['expired']);
			}
		}
		
		//如果开启了安全设置的话，先检查用户在提交数据的时候是否提交了相应的数据(只用于POST的数据时候) 2014-02-07
		if(self::isEnableSecurity() && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
		{
			$security_sid = $argvs[$siteInfo['status']['security']['name']] ? $argvs[$siteInfo['status']['security']['name']] : $_REQUEST[$siteInfo['status']['security']['name']];
			self::setError(Security::validateSecurity($security_sid), 4);
		}
		//设置qii_is_search_path
		self::setPrivate('qii_is_search_path', $siteInfo['status']['search']);
		if(self::getPrivate('qii_is_search_path'))
		{
			//搜索指定路径
			if(!is_array($siteInfo['xpath']['path']))
			{
				$searchPath = explode(PS, $siteInfo['xpath']['path']);
			}
			else
			{
				$searchPath = $siteInfo['xpath']['path'];
			}
			//设置搜索路径
			self::setPrivate('qii_search_path', $searchPath);
		}
		if(!$siteInfo['controller']['name'])
		{
			$siteInfo['controller']['name'] = 'controller';
		}
		//如果URL为short模式的话，参数就从第三个开始。
		if (self::getPrivate('qii_router_class')->get('_mode') == 'short')
		{
			$siteInfo['controller']['name'] = 0;
			$siteInfo['action']['name'] = 1;
		}
		
		$className = Qii::segment($siteInfo['controller']['name']);
		$actionName = Qii::segment($siteInfo['action']['name']);
		
		//如果是直接dispatch的话就直接请求指定的controller和action，controller和action也受router配置影响
		if(!empty($controller)) $className = $controller;
		if(!empty($action)) $actionName = $action;
		if($className == '')
		{
			$className = $siteInfo['controller']['default'];
		}
		if($actionName == '')
		{
			$actionName = $siteInfo['action']['default'];
		}
		
		//载入路由，路由规则由用户指定。return array('index:index'=> 'System:index');那么访问controller=index&action=index 会自动指向到controller=System&action=index
		$router = Qii::parseRouter($className, $actionName);
		/**
		 * 增加多域名支持，在site.xml中添加以下内容(与status标签同一级)：
		 * <hosts>
		 * 	<host>
		 * 		<domain>域名</domain>
		 * 		<ext>指向的目录</ext>
		 *  </host>
		 * </hosts>
		 */
		if(isset($siteInfo['hosts']) && $_SERVER['HTTP_HOST'])
		{
			foreach ($siteInfo['hosts']['host'] AS $key => $value)
			{
				if($value['domain'] == $_SERVER['HTTP_HOST'])
				{
					$siteInfo['controller']['ext'] = $value['ext'];
				}
			}
		}
		$className = $router['controller'];
		$actionName = $router['action'];
		$className = $className . ($siteInfo['controller']['ext'] == '' ? '' : ('_' . $siteInfo['controller']['ext']));
		Qii::setPrivate('qii_router', array('controller' => $className, 'action' => $actionName));
		//如果关闭站点
		if($siteInfo['status']['enabled'] == 0 && $className != 'Qii_controller')
		{
			if('' != $siteInfo['status']['closePage'])
			{
				$pageArray = explode(":", $siteInfo['status']['closePage']);
				$className = $pageArray[0] . "_controller";
				$actionName = $pageArray[1] ? $pageArray[1] : "index";
			}
			else
			{
				self::showMessage(array(date("Y-m-d H:i:s") => $siteInfo['status']['message']), false);
				die();
			}
		}
		//如果是调用系统System的话就直接调用core下边的System文件
		if($className == 'Qii_controller')
		{
			Qii::requireOnce(Qii_DIR . DS . 'core'. DS . 'System.php');
			$myController = new Qii_controller();
			$myController->{$actionName}();
		}
		else 
		{
			//增加判断Class是否存在 2011-06-11 20:28 (由于用到自动加载函数，是否有BUG未知)
			if(!class_exists($className))
			{
				if(!$siteInfo['status']['debug'])
				{
					//eval 中不用能“""”
					eval('class '.$className.' extends Controller{public function '.$actionName.'(){die("Class \'<font color=\'red\'>'.$className.'</font>\' does not exit.");} public function __call($class, $argvs){die("'.$className.' does not exit.");}}');
				}
				else 
				{
					!self::setError(class_exists($className, false), 103, array($className));
				}
			}
			$myController= new $className();
			if(method_exists($myController, $actionName)){
				//如果方法不是公用的就不让用户通过浏览器访问，直接报404错误
				$reflection = new ReflectionMethod($className, $actionName);
				if(!$reflection->isPublic())
				{
					self::setError(false, 4, array('You do not have permission to access this page'));
					return;
				}
			}
			//如果有三个以上的参数，就将后边的参数依次传过去
			//$myControl->{$actionName}();暂时去掉
			call_user_func_array(array($myController, $actionName), $argvs);
		}
	}
	/**
	 * var_dump成容易看的结构
	 * @param Mix $value
	 * @param Int $level
	 * @return string 输出Html
	 */
	static public function dump($value, $level = 0) 
	{
		if ($level == - 1) 
		{
			$trans [' '] = '&nbsp;';
			$trans ["\t"] = '&rArr;';
			$trans ["\n"] = '&para;;';
			$trans ["\r"] = '&lArr;';
			$trans ["\0"] = '&oplus;';
			return strtr ( htmlspecialchars ( $value ), $trans );
		}
		if ($level == 0)
			echo '<html><head><title>Dump Data</title></head><body><pre>';
		$type = gettype ( $value );
		echo $type;
		if ($type == 'string') 
		{
			echo '(' . strlen ( $value ) . ')';
			$value = Qii::dump ( $value, - 1 );
		} 
		elseif ($type == 'boolean')
			$value = ($value ? 'true' : 'false');
		elseif ($type == 'object') 
		{
			$props = get_class_vars ( get_class ( $value ) );
			echo '(' . count ( $props ) . ') <u>' . get_class ( $value ) . '</u>';
			foreach ( $props as $key => $val ) {
				echo "\n" . str_repeat ( "\t", $level + 1 ) . $key . ' => ';
				Qii::dump ( $value->$key, $level + 1 );
			}
			$value = '';
		} 
		elseif ($type == 'array') 
		{
			echo '(' . count ( $value ) . ')';
			foreach ( $value as $key => $val ) 
			{
				echo "\n" . str_repeat ( "\t", $level + 1 ) . Qii::dump ( $key, - 1 ) . ' => ';
				Qii::dump ( $val, $level + 1 );
			}
			$value = '';
		}
		echo " <b>$value</b>";
		if ($level == 0)
			echo '</pre></body></html>';
	}
	/**
	 * 输出错误
	 *
	 * @param Array $messageArray
	 */
	static public function error($messageArray)
	{
		if(!is_array($messageArray))
		{
			$tmp[] = $messageArray;
			unset($messageArray);
			$messageArray = $tmp;
			unset($tmp);
		}
		Benchmark::set('site', true);
		include(Qii_DIR . DS . 'view' . DS . 'Error.php');
		die();
	}
	/**
	 * 转换文件路径，修正路径连接符
	 *
	 * @param String $param
	 * @param String $extension
	 * @return String param fixed
	 */
	static public function filter($param, $extension = '')
	{		
		//转换./开头的文件路径为当前路径，并去掉./
		$fileName = preg_replace("/^.\//", '', $param);
		$fileName = preg_replace("/[\\|\/|\\\\|\/\/]/", DS, $fileName);
		return $fileName;
	}
	/**
	 * require_once
	 *
	 * @param String $fullPath
	 */
	static public function requireOnce($fullPath)
	{
		$fullPath = self::filter($fullPath);
		$sign = md5($fullPath);
		if(self::getPrivate('require_'. $sign))
		{
			return false;
		}
		$realPath = realpath($fullPath);
		if($realPath == '' || !file_exists($realPath) || !is_file($realPath))
		{
			self::setError(false, 102, array($fullPath == '' ? 'NULL' : $fullPath));
			return false;
		}
		require($realPath);
		self::setPrivate('require_'. $sign, $fullPath);
		return true;
	}
	/**
	 * 加载文件
	 *
	 * @param String $fullPath
	 * @param String $key
	 * @return Array
	 */
	static public function loadFile($fullPath, $key = null)
	{
		$realPath = realpath($fullPath);
		$sign = md5($fullPath);
		$data = null;
		if(Qii::getPrivate('loadFile['.$sign.']'))
		{
			$data = Qii::getPrivate('loadFile['.$sign.']');
		}
		else if(!self::setError(('' != $realPath), 102, array($fullPath)) || !self::setError(file_exists($realPath), 102, array($fullPath)))
		{
			Qii::setPrivate('loadFile['.$sign.']', (array) include($realPath));
			$data = Qii::getPrivate('loadFile['.$sign.']');
		}
		if(isset($key) && $key !== null)
		{
			return $data[$key];
		}
		return $data;
	}
	/**
	 * 初始化类 
	 * useage:
	 * Qii::instance($className, $args);
	 *
	 * @return Object
	 */
	static public function instance()
	{
		$args = func_get_args();
		$className = $args[0];
		if(isset(self::$_global['instance'][$className]))
		{
			return clone self::$_global['instance'][$className];
		}
		elseif(!self::setError(class_exists($className, false), 103, array($className)))
		{
			/**
			 * 判断类的继承关系, 如果从Model、View、Controller继承的则先包含这几个文件
			 */
			array_shift($args);
			$loader = new ReflectionClass($className);
			self::$_global['instance'][$className] =  call_user_func_array(array($loader, 'newInstance'), $args);
			return self::$_global['instance'][$className];
		}
	}
	/**
	 * 加载初始化过的class
	 *
	 * @param String $className
	 * @return Object
	 */
	static public function load($className)
	{
		if(!self::setError(isset(self::$_global['instance'][$className]), 104, array($className)))
		{
			return self::$_global['instance'][$className];
		}
	}
	/**
	 * 获取IP地址
	 *
	 * @return String
	 */
	static public function getIPAddress($short = true)
	{
		$ipCls = new ip_sys_tools();
		if(!$short) return $ipCls->getLongAddress();
		return $ipCls->getIPAddress();
	}
	/**
	 * 设置private 属性
	 *
	 * @param String $name
	 * @param Mix $value
	 */
	static public function setPrivate($name, $value)
	{
		Qii::load('Arrays')->setPrivate($name, $value);
		return;
		self::$_global['private'][$name] = $value;
	}
	static public function setSecurity($sid)
	{
		Qii::load('Arrays')->setPrivate('qii_security_id', $sid);
	}
	/**
	 * 获取网站配置信息
	 *
	 * @return Array
	 */
	static public function getSiteInfo()
	{
		$siteInfo = self::getPrivate('qii_site_configure_' . self::getPrivate('qii_site_configure'));
		if(empty($siteInfo))
		{
			$messageArray = array();
			$messageArray[] = ('Website configure <font color="red">'.Qii::getPrivate('qii_site_xpath').'</font> is NULL');
			Benchmark::set('site', true);
			//throw new Exception('Website configure <font color="red">'.Qii::getPrivate('qii_site_xpath').'</font> is NULL');
			require(Qii_DIR . '/view/Error.php');
			die();
		}
		return $siteInfo;
	}
	/**
	 * 获取private属性
	 *
	 * @param String $name
	 * @param String $key
	 * @return Mix
	 */
	static public function getPrivate($name, $key = '')
	{
		$private = Qii::load('Arrays')->getPrivate($name);
		if(preg_match('/^\s*$/', $key))
		{
			return $private;
		}
		if(isset($private[$key])) return $private[$key];
	}
	/**
	 * 记载错误语言类
	 *
	 * @param String $language
	 */
	static public function loadError()
	{
		if(self::getPrivate('qii_sys_language'))
		{
			return;
		}
		//加载语言包
		$languageConfigure = Qii::loadFile(Qii_DIR . DS .'core' . DS . 'i18n' . DS . 'language.php');
		$language = array_pop($languageConfigure);
		$lpath = Qii_DIR . DS .'core' . DS . 'i18n' . DS . $language;
		$fileName = $lpath . DS . 'Error.php';
		self::$language = Qii::instance('Language');
		self::setPrivate('qii_sys_language', $fileName);
		if(is_dir($lpath) && is_file($fileName))
		{
			self::$language->load($fileName);
		}
		else 
		{
			self::$language->load(Qii_DIR . DS .'core' . DS . 'i18n' . DS . 'EN' . DS . 'Error.php');
		}
	}
	/**
	 * 解析XML
	 *
	 * @param String $fileName
	 * @return Array
	 */
	static public function parseXpath($fileName)
	{
		self::instance('XML');
		self::load('XML')->setXml($fileName);
		$data = self::load('XML')->XML2Array();
		if(!$data)
		{
			Qii::setError(false, 102, array($fileName));
		}
		return $data;
	}
	/**
	 * 路由转发， 转发对应的规则中xx不能为*
	 *
	 * @param String $controller
	 * @param String $action
	 * @return Array ($controller, $action);
	 * 
	 * *:* => *:yyy 所有controller和action都转发到 *->yyy
	 * *:* => yy:* 所有转发到xxx->*, 这里的*，前边对应的是什么，后边就对应转发到什么，比如: *:xxx => yy:yyy
	 * xx:* => yy:* xx中对应的方法转发到yy对应的方法
	 * xx:* => yy:yyy xxx Controller转发到 yy->yyy
	 * *:xxx => yy:yyy 所有Controller转发到 yy->yyy
	 */
	static public function parseRouter($controller, $action = '')
	{
		if($controller == 'Qii')
		{
			return array('controller' => $controller, 'action' => $action);
		}
		//如果第一列的是*号则所有的controller都执行对应的x:
		$router = self::getPrivate('qii_site_router');
		if(!$router)
		{
			return array('controller' => $controller, 'action' => $action);
		}
		$routerArray = array();
		if(is_array($router))
		{
			foreach($router AS $key => $value)
			{
				$keyArray = explode(":", $key);
				$valueArray = explode(":", $value);
				if('' == $keyArray[1])
				{
					$keyArray[1] = "*";
				}
				if(!isset($valueArray[1]))
				{
					$valueArray[1] = '';
				}
				$routerArray['controller'][$keyArray[0].":".$keyArray[1]] = $valueArray[0];
				if($keyArray[1] == "*")
				{
					$routerArray['action'][$keyArray[0].":".$keyArray[1]] = $valueArray[1];
				}
				else
				{
					$routerArray['action'][$keyArray[0].":".$keyArray[1]] = $valueArray[1];
				}
			}
		}
		if(isset($routerArray["controller"]["*:*"]) && '' != $routerArray["controller"]["*:*"])//*:*=>yyy:* or *:* => *:yyy mode 
		{
			$controller = ($routerArray['controller']['*:*'] == '*' ? $controller : $routerArray["controller"]["*:*"]);
			$action = ($routerArray['action']['*:*'] == '*' ? $action : $routerArray['action']['*:*']);
		}
		elseif(isset($routerArray["action"][$controller . ":*"]) && '' != $routerArray["action"][$controller . ":*"])//xx:*=>yy:* mode
		{
			$action = $routerArray['action'][$controller .":*"];
			$controller = $routerArray["controller"][$controller .":*"];
		}
		elseif(isset($routerArray["action"]["*:" . $action]) && '' != $routerArray["action"]["*:" . $action])//*:xxx=> yy:yyy mode
		{
			$controller = $routerArray["control"]["*:". $action];
			$action = $routerArray["action"]["*:". $action];
		}
		elseif(isset($routerArray["controller"][$controller .":" .$action]))
		{
			$tmpAction = $controller .":" .$action;
			$action = $routerArray["action"][$controller .":" .$action];
			$controller = $routerArray["controller"][$tmpAction];
		}
		return array('controller' => $controller, 'action' => $action);
	}
	/**
	 * 获取路由
	 *
	 */
	static public function Router()
	{
		self::setPrivate('qii_router_class', self::instance('Router'));
		$siteInfo = self::getSiteInfo();
		if($siteInfo['status']['uri'])
		{
			self::getPrivate('qii_router_class')->setMode($siteInfo['status']['uri']);
			self::getPrivate('qii_router_class')->setTrim($siteInfo['uri'][$siteInfo['status']['uri']]['trim']);
			self::getPrivate('qii_router_class')->setSymbol($siteInfo['uri'][$siteInfo['status']['uri']]['symbol']);
			self::getPrivate('qii_router_class')->setExtenstion($siteInfo['uri'][$siteInfo['status']['uri']]['extenstion']);
		}
		self::setPrivate('qii_segment', self::getPrivate('qii_router_class')->getParam());
	}
	/**
	 * 加载网站配置文件
	 *
	 * @param Sting $file
	 * @param String $index
	 */
	static public function setXpath($file, $index)
	{
		self::setPrivate('qii_site_xpath', $file);
		self::setPrivate('qii_site_configure', $index);
		$siteInfo = self::xml2Cache($file);
		if(!isset($siteInfo['root'][$index])) $siteInfo['root'][$index] = $siteInfo['root']['index'];
		if($siteInfo['root'][$index]) 
		{
			self::setPrivate('qii_site_configure_'. $index, $siteInfo['root'][$index]);
		}
		else 
		{
			self::setPrivate('qii_site_configure_'. $index, $siteInfo['root']['index']);
		}
	}
	/**
	 * getXpath
	 *
	 * @param String $index
	 * @return Mix
	 */
	static public function getXpath($index)
	{
		return self::getPrivate('qii_site_configure_'. $index);
	}
	/**
	 * 设置缓存文件目录
	 *
	 * @param String $path 文件目录
	 */
	static public function setCachePath($path)
	{
		self::setPrivate('qii_site_cache_path', realpath($path));
	}
	/**
	 * 将网站配置文件Cache到临时目录
	 *
	 * @param String $fileName
	 * @param Bool $isRwrite
	 * @return Array
	 */
	static public function xml2Cache($fileName)
	{
		//将文件生成到xCachePath的目录中
		$cachePath = rtrim(self::getPrivate('qii_site_cache_path'), '/') . DS . md5($fileName) . ".php";
		if(file_exists($cachePath))
		{
			if(fileatime($fileName) != filemtime($fileName))
			{
				touch($fileName);
				$data = self::parseXpath($fileName);
				file_put_contents($cachePath, "<?php \n return " . var_export($data, true) . "\n?>", LOCK_EX);
				return $data;
			}
			return Qii::loadFile($cachePath);
		}
		$data = self::parseXpath($fileName);
		file_put_contents($cachePath, "<?php \n return " . var_export($data, true) . "\n?>", LOCK_EX);
		return $data;
	}
	/**
	 * 输出信息
	 *
	 * @param Mix $messageArray
	 * @param Bool $showHeader
	 * @param String $href
	 */
	static public function showMessage($messageArray, $showHeader = true, $href = '')
	{
		if(!is_array($messageArray))
		{
			$tmp[] = $messageArray;
			unset($messageArray);
			$messageArray = $tmp;
			unset($tmp);
		}
		Benchmark::set('site', true);
		include(Qii_DIR .DS. 'view' . DS . 'showMessage.php');
	}
	/**
	 * Segment
	 * 
	 * 获取_GET[$key]的值;
	 *
	 */
	static public function segment($key = '')
	{
		$segment = self::getPrivate('qii_segment');
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
	 * 设置数据库文件
	 * 支持设置使用不同的数据库配置
	 *
	 * @param String $file
	 */
	static public function setDB($file, $key = null)
	{
		if(!self::setError(file_exists($file), 102, array($file)))
		{
			self::setPrivate('qii_site_db', (array) self::loadFile($file, $key));
		}
	}
	/**
	 * 设置路由
	 * 支持设置不同的路由规则
	 * @param String $file
	 */
	static public function setRouter($file, $key = null)
	{
		if(!self::setError(file_exists($file), 102, array($file)))
		{
			self::setPrivate('qii_site_router', (array) self::loadFile($file, $key));
		}
	}
	/**
	 * 错误设置，如果满足给定的条件就直接返回false，否则在设置了错误页面的情况下返回true。
	 *
	 * @param Bool $condition
	 * @param String $msg
	 * @param Int|String $code
	 * @return Bool
	 */
	static public function setError($condition, $code, $argvs)
	{
		if($condition)
		{
			return false;
		}
		self::loadError();
		$msg = self::$language->gettext($code, $argvs);
		$siteInfo = Qii::getSiteInfo();
		//如果是调试模式就直接输出错误信息
		if($siteInfo['status']['debug'] == 1 && $siteInfo['status']['publish'] == 0)
		{
			self::loadError();
			$msg = self::$language->gettext($code, $argvs);
			throw new Error($msg, $code);
		}
		else if($siteInfo['status']['debug'] == 0 && '' != $siteInfo['status']['errorPage'])//如果关闭调试模式，并且设置了错误页面就跳转错误页面
		{
			//如果$siteInfo['status']['errorPage'])中是“:”隔开就将它以":"作为Controller和Action
			$classArray = explode(":", $siteInfo['status']['errorPage']);
			$controller = $classArray[0] . '_'. $siteInfo['controller']['ext'];
			$action = 'index';
			if(count($classArray) == 2)
			{
				$action = $classArray[1];
			}
			//如果文件存在就直接引用并执行相关操作，不存在就直接报错
			$fullPath = Qii::AutoPath($controller);
			if(!file_exists($fullPath))
			{
				$code = 114;
				$msg = self::$language->gettext($code, array($fullPath));
				throw new Error($msg, $code);
			}
			Qii::requireOnce($fullPath);
			if(!class_exists($controller, false))
			{
				$code = 103;
				$msg = self::$language->gettext($code, array($controller));
				throw new Error($msg, $code);
			}
			$controllerCls = new $controller();
			if(!method_exists($controllerCls, $action))
			{
				$code = 106;
				$msg = self::$language->gettext($code, array($controller, $action, 'array()'));
				throw new Error($msg, $code);
			}
			$controllerCls->$action();
			die();
		}
		else
		{
			Qii::load('Status')->setStatus(404);
			die('Status 404');
		}
		return true;
	}
	/**
	 * 内存使用量
	 *
	 * @return String
	 */
	static public function useMemory()
	{
		self::requireOnce(Qii_DIR . DS . 'tools'. DS .'memory.tools.php');
		return new memory_sys_tools();
	}
}
spl_autoload_register(array('Qii','__autoload'));
?>