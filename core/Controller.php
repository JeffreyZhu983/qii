<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Controller.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 控制器，用于控制程序的处理流程
 * 
 * useage:
 * 
 * Controller中使用model:
 * 
 * $this->Qii('Model');
 * $this->instance('model');
 * $rs = $this->model->setQuery("SELECT * FROM user");
 * print_r($rs->fetch());
 * 
 * 加载方法，通过 $this->类名 访问
 * $this->Qii('new_model');
 * 
 * 调用Model的方法:
 * 
 * $this->Qii('new_model');
 * $this->new_model->method();
 * 
 * 调用新的Controller
 * 
 * $this->dispach('index', 'default');
 * 
 */
if(class_exists('Controller'))
{
	return;
}
abstract class Controller
{
	public $version = '1.1.0';
	/**
	 * 自动加载暂时可提供的方法 model, view, ui
	 */
	protected $core = array();
	protected $cache;
	protected $cacheArray = array();
	/**
	 * 当前执行的Controller及方法
	 *
	 * @var String
	 */
	protected $_router;
	protected $view;
	protected $model;//数据库操作层
	protected $language;
	/**
	 * 网站配置信息
	 *
	 * @var Array
	 */
	protected $siteInfo;
	/**
	 * __setter的变量
	 *
	 * @var Array
	 */
	protected $global;
	/**
	 * 调用的默认
	 *
	 * @var String
	 */
	public $defaultAction = 'index';
	public function __construct()
	{
		$this->_router = Qii::getPrivate('qii_router');
		$this->siteInfo = Qii::getSiteInfo();
	}
	/**
	 * __getter
	 *
	 * @param String $className
	 * @return Mix
	 */
	public function __get($className)
	{
		$name = strtolower($className);
		if(isset($this->global['qii'][$name]))
		{
			return $this->global['qii'][$name];
		}
		return new stdClass();
	}
	/**
	 * __setter
	 *
	 * @param String $name
	 * @param Mix $val
	 */
	public function __set($name, $val)
	{
		$name = strtolower($name);
		$this->global['qii'][$name] = $val;
	}
	/**
	 * 分发到新的controller
	 * @param String $controller
	 * @param String $action
	 * @param $argvs 参数，传递给action的，$action后边所有的参数都将传递给$action方法
	 */
	public function dispatch($controller, $action)
	{
		$funArgvs = func_get_args();
		$argvs = array();
		if(count($funArgvs) > 2)
		{
			$argvs = array_slice($funArgvs, 2);
		}
		call_user_func_array(array('Qii', 'dispatch'), $funArgvs);
	}
	/**
	 * 实例化方法，通过 $this->{类小写}访问
	 *
	 * @return Object
	 */
	final function instance()
	{
		$args = func_get_args();
		$className = $args[0];
		$name = strtolower($className);
		//获取路径
		$fullPath = Qii::AutoPath($className);
		Qii::requireOnce($fullPath);
		if(isset($this->{$name}))
		{
			return clone $this->{$name};
		}
		elseif(!Qii::setError(class_exists($className, false), 103, array($className)))
		{
			/**
			 * 判断类的继承关系, 如果从Model、View、Controller继承的则先包含这几个文件
			 */
			array_shift($args);
			$loader = new ReflectionClass($className);
			$this->{$name} =  call_user_func_array(array($loader, 'newInstance'), $args);
			return $this->{$name};
		}
	}
	/**
	 * get
	 *
	 * @param String $key
	 * @return Mix
	 */
	public function get($key = null, $default = '')
	{
		return $key == null ? Qii::segment() : (Qii::segment($key) ? Qii::segment($key) : $default);
	}
	/**
	 * post
	 *
	 * @param String $key
	 * @return Mix
	 */
	public function post($key = null)
	{
		return $key == null ? array_map('htmlspecialchars', $_POST) : htmlspecialchars($_POST[$key]);
	}
	/**
	 * 检查Cache
	 *
	 * @return Bool
	 */
	public function cacheExist()
	{
		if(count($this->cacheArray) > 0 && isset($this->cacheArray[$this->_router['action']]) && '' != $this->cacheArray[$this->_router['action']])
		{
			return $this->getCache($this->cacheArray[$this->_router['action']]);
		}
		return false;
	}
	/**
	 * 获取缓存策略
	 * @param String $cache
	 * @return Array
	 */
	public function getCachePolicy($cache)
	{
		$data = array();
		$servers = explode(";", $cache['servers']);
		$ports = explode(";", $cache['ports']);
		for($i = 0; $i < count($servers); $i++)
		{
			$data[] = array('host' => $servers[$i], 'port' => $ports[$i]);
		}
		return $data;
	}
	/**
	 * 获取缓存
	 *
	 * @param Int $id
	 */
	public function getCache($id)
	{
		$this->setCache($this->siteInfo['status']['cache']);
		$data = $this->cache->get($id);
		if($data)
		{
			return $data;
		}
		return false;
	}
	/**
	 * 缓存内容
	 *
	 * @param Int $id
	 * @param String $data
	 * @param Array $policy
	 */
	public function cache($id, $data, $policy = null)
	{
		$this->setCache($this->siteInfo['status']['cache']);
		$this->cache->set($id, $data, $policy);
	}
	/**
	 * 设置Cache
	 *
	 * @param String $cache
	 * @param Array $policy
	 * 
	 * useage:
	 * $this->setCache('memcache', array('servers' => array('127.0.0.1')));
	 */
	public function setCache($cache, $policy)
	{
		$basicPolicy = array('servers' => $this->getCachePolicy($this->siteInfo[$this->siteInfo['status']['cache']]));
		if($basicPolicy)
		{
			$policy = array_merge($basicPolicy, $policy);
		}
		Qii::requireOnce(Qii_DIR . DS . 'core' . DS . '_Cache.php');
		$this->cache = Qii::instance('_Cache', $cache)->initialization($policy);//载入cache类文件
		Qii::setPrivate('sysCache', $this->cache);
	}
	/**
	 * 如果没有默认的方法就提示错误并终止执行
	 *
	 */
	public function index()
	{
		$controller= $this->_router['controller'];
		if('' != $controller)
		{
			$this->_router = Qii::getPrivate('qii_router');
			$controller= $this->_router['controller'];
		}
		Qii::error("请添加默认的执行方法\"<font color=\"red\"><u>". $controller ."::index</u></font>\"");
	}
	/**
	 * 加载class
	 *
	 * @return Object
	 */
	final function Qii()
	{
		$args = func_get_args();
		$className = $args[0];
		//如果没有传递class name 的话就直接返回
		if(Qii::setError(('' != $className), 107, array()))
		{
			return;
		}
		//检查是否是当前类中的函数，当前类中的函数默认不支持 "$this->类名->方法" 调用
		$coreMethod = 'Qii' . ucfirst($className);
		if(method_exists($this, $coreMethod))
		{
			array_shift($args);
			if(!isset($args[0])) $args[0] = '';
			return $this->{$coreMethod}($args[0]);
		}
		//获取路径
		$fullPath = Qii::AutoPath($className);
		Qii::requireOnce($fullPath);
		if(isset($this->{$className}))
		{
			return $this->{$className};
		}
		elseif(!Qii::setError(class_exists($className, false), 103, array($className)))
		{
			/**
			 * 判断类的继承关系, 如果从Model、View、Controller继承的则先包含这几个文件
			 */
			array_shift($args);
			$loader = new ReflectionClass($className);
			$this->{$className} =  call_user_func_array(array($loader, 'newInstance'), $args);
			return $this->{$className};
		}
	}
	/**
	 * 初始化Header类
	 *
	 */
 	final function QiiHeader()
 	{
 		Qii::requireOnce(Qii_DIR . "/core/Header.php");
 		$this->Header = Qii::Instance("Header");
 	}
 	/**
 	 * 自定义使用View方法
 	 *
 	 * @param String $view
 	 * @param Array $assign 初始化的时候赋值给view
 	 * @return Object
 	 */
 	final function setView($view, $assign = array())
 	{
		Qii::requireOnce(Qii_DIR . '/core/_View.php');
 		$viewObject = Qii::Instance('_View');
 		$viewObject->setView($view);
 		$this->view = $viewObject->getView();
 		if($assign['class'] && method_exists($this->view, 'registerClass'))
 		{
 			if(is_array($assign['smarty_view_class_plugin']))
 			{
 				foreach($assign['smarty_view_class_plugin'] AS $class)
 				{
 					$this->view->registerClass(Qii::instance($class), $class);
 				}
 			}
 			else
 			{
 					$this->view->registerClass(Qii::instance($assign['smarty_view_class_plugin']), $assign['smarty_view_class_plugin']);
 			}
 			unset($assign['smarty_view_class_plugin']);
 		}
		/**
		 * 根据配置文件中的信息来定位模板路径、编译模板路径、缓存模板路径
		 */
		if(sizeof($this->siteInfo['view']['assign']) > 0)
		{
			foreach($this->siteInfo['view']['assign'] AS $key => $value)
			{
				$this->view->assign($key, $value);
			}
		}
		//增加系统路径及系统当前文件路径 2011-08-30
		$sysPath = 'http://' . rtrim(str_replace('//', '/', $_SERVER['HTTP_HOST'] . Qii::load('Router')->getPath($_SERVER['SCRIPT_NAME'])), '/');
		$this->sysPath = $sysPath;
		$sysURL = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		$assign['sysPath'] = $sysPath;
		$assign['sysURL'] = $sysURL;
		$controller= $this->_router['controller'];
		if('' != $controller)
		{
			$this->_router = Qii::getPrivate('qii_router');
			$controller= $this->_router['controller'];
		}
		$assign['sysController'] = $controller;
		$assign['sysAction'] = $this->_router['action'];
		if(sizeof($assign) > 0)
		{
			$this->view->assign($assign);
		}
		return $this->view; 
 	}
	/**
	 * View方法，此方法默认通过配置文件加载view
	 *
	 */
	final function QiiView($assign = array())
	{
		Qii::requireOnce(Qii_DIR . '/core/_View.php');
		$assign = (array) $assign;
		if(isset($this->siteInfo['status']['view']))
		{
			$this->setView($this->siteInfo['status']['view'], $assign);
		}
		else 
		{
			$this->setView('', $assign);
		}
	}
	/**
	 * Model 方法，通过数据库配置文件自动选择Model，支持PDO、mysql、mysqli，提供统一的接口;
	 * 用的时候再实例化，避免链接数据库产生开销
	 * 示例：Controller中使用 
	 * $this->Qii('Model'); 
	 * Qii::Instance('Model');
	 * 
	 * Model中使用
	 * class user extends Model
	 * {
	 * 		public function __construct()
	 * 		{
	 * 			parent::__construct();
	 * 		}
	 * }
	 */
	final function QiiModel($array = array())
	{
		Qii::requireOnce(Qii_DIR . '/core/_Model.php');
		Qii::instance('_Model');
	}
	/**
	 * 加载language
	 *
	 * @param Array $array
	 * @return Object
	 */
	final function QiiLanguage($array = array())
	{
		return $this->language = Qii::instance('Language');
	}
	/**
	 * 实例化URI方法
	 *
	 * @param Array $array
	 * @return Object
	 */
	final function QiiURI($array = array())
	{
		Qii::requireOnce(Qii_DIR . '/core/URI.php');
		return $this->URI = Qii::Instance('URI');
	}
	/**
	 * __call
	 *
	 * @param String $name 方法
	 * @param Array $args 参数名
	 */
	public function __call($name, $args)
	{
		$controller = $this->_router['controller'];
		if('' == $controller)
		{
			$this->_router = Qii::getPrivate('qii_router');
			$controller= $this->_router['controller'];
		}
		if($this->siteInfo['status']['debug']) 
		{
			//如果是publish模式，是否调用默认的action
			if($this->siteInfo['status']['callDefault'] && $this->siteInfo['status']['publish'] && method_exists($this, $this->defaultAction))
			{
				$this->{$this->defaultAction}();
			}
			else 
			{
				Qii::setError(false, 106, array($controller, $name, print_r($args, true)));
			}
		}
		else 
		{
			Qii::setError(false, 106, array($controller, $name, print_r($args, true)));
			//是否调用默认的action
			if($this->siteInfo['status']['callDefault'] && method_exists($this, $this->defaultAction))
			{
				$this->{$this->defaultAction}();
			}
		}
	}
	/**
	 * 析构函数，释放使用过的内存
	 *
	 */
	public function __destruct()
	{
		unset($this->core);
		unset($this->cache);
		unset($this->cacheArray);
		unset($this->_router);
		unset($this->view);
		unset($this->language);
		unset($this->siteInfo);
		unset($this->global);
		unset($this->defaultAction);
	}
}
?>