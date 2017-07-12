<?php
namespace Qii;

use \Qii\Base\Dispatcher;

use \Qii\Autoloader\Factory;
use \Qii\Autoloader\Import;
use \Qii\Autoloader\Psr4;
use \Qii\Autoloader\Instance;

use \Qii\Config\Register;
use \Qii\Config\Consts;
use \Qii\Config\Setting;

class Application 
{
    /**
     * 存储网站配置文件内容
     *
     * @var array $config 配置内容
     */
    protected static $config = [];
    /**
     * @var object $logerWriter 写日志工具
     */
    public $logerWriter = null;
    /**
     * @var string $workspace 工作目录
     */
    private static $workspace = './';/**
     * @var string $env 环境变量
     */
    public static $env = 'product';
    /**
     * @var array $paths 网站使用的路径
     */
    public static $paths = array('configure', 'controller', 'model', 'view', 'plugins', 'tmp');

    /**
     * Qii\Request\Url
     */
    public $request;

	public function __construct()
	{
        $this->helper = Psr4::getInstance()->loadClass('\Qii\Autoloader\Helper');
	}

    /**
     * 初始化本实例对象
     *
     * @return object
     */
	public static function getInstance()
	{
        $args = func_get_args();
        if (count($args) > 0) {
            $className = array_shift($args);
            return Psr4::getInstance($className);
        }
	    return Factory::getInstance('\Qii\Application');
	}

    /**
     * 设置网站运行环境
     *
     * @param string $env 网站环境
     * @return $this
     */
    public function setEnv($env)
    {
        self::$env = $env;
        return $this;
    }
    /**
     * 设置缓存文件路径
     * @param string $path 缓存路径
     */
    public function setCachePath($path)
    {
        Register::set(Consts::APP_CACHE_PATH, $this->getCachePath($path));
        return $this;
    }

    /**
     * 保存网站的配置文件
     *
     * @param $iniFile
     */
    public function setAppIniFile($iniFile)
    {
        Register::set(Consts::APP_INI_FILE, $iniFile);
        return $this;
    }

    /**
     * 设置网站的工作目录，可以通过此方法将网站的重要文件指向到其他目录
     *
     * @param string $workspace 工作目录
     * @return $this
     */
    public function setWorkspace($workspace = './')
    {
        //此处转换成真实路径，防止workspace中引入的文件出错
        if (!is_dir($workspace)) {
            throw new \Qii\Exceptions\FolderDoesNotExist(\Qii::i(1045, $workspace), __LINE__);
        }
        $workspace = Psr4::getInstance()->realpath($workspace);
        Psr4::getInstance()->removeNamespace('workspace', self::$workspace);
        //如果配置了使用namespace就走namespace
        self::$workspace = $workspace;
        Psr4::getInstance()->addNamespace('workspace', $workspace, true);
        foreach (self::$paths AS $path) {
            Psr4::getInstance()->addNamespace($path, $workspace . '\\' . $path);
        }

        return $this;
    }
    /**
     * 获取指定路径的缓存绝对路径
     * @param string $path 路径
     * @return string 绝对路径
     */
    public function getCachePath($path)
    {
        if (self::$workspace != '') return self::$workspace . DS . $path;
        $dir = '';
        $workspace = sr4::getInstance()->getNamespace('workspace');
        foreach ($workspace AS $dir) {
            if (is_dir($dir)) $dir = $dir;
        }
        return $dir . DS . $path;
    }
    /**
     * 获取网站运行环境
     *
     * @return string
     */
    public function getEnv()
    {
        return self::$env;
    }
    /**
     * 获取当前工作目录
     */
    public function getWorkspace()
    {
        return self::$workspace;
    }
    /**
     * 获取网站的配置文件
     * @return Mix
     */
    public function getAppIniFile()
    {
        return Register::get(Consts::APP_INI_FILE);
        return $this;
    }

    /**
     * 设置网站配置文件
     * @param string $ini 配置文件路径
     * @param string $env 环境
     *
     */
    public function setAppConfigure($ini, $env = '')
    {
        if ($env == '') $env = $this->getEnv();
        $ini = Psr4::getInstance()->getFileByPrefix($ini);
        $this->setAppIniFile($ini);
        if (!Register::setAppConfigure(
            $ini,
            $env
        )
        ) throw new \Qii\Exceptions\FileNotFound(\Qii::i(1405, $ini), __LINE__);
        //载入request方法
        $this->request = Psr4::getInstance()->loadClass('\Qii\Request\Http');
        Setting::getInstance()->setDefaultTimeZone();
        Setting::getInstance()->setDefaultControllerAction();
        Setting::getInstance()->setDefaultNamespace();
        Setting::getInstance()->setDefaultLanguage();
        return $this;
    }

    /**
     * 合并ini文件生成的数组
     * @param String $iniFile ini文件名
     * @param Array $array
     */
    public function mergeAppConfigure($iniFile, $array)
    {
        if (!is_array($array)) return;
        Register::mergeAppConfigure($iniFile, $array);
        return $this;
    }

    /**
     * 覆盖/添加ini文件的key对应的值
     * @param String $iniFile ini文件名
     * @param String $key
     * @param String $val
     */
    public function rewriteAppConfigure($iniFile, $key, $val)
    {
        Register::rewriteConfig($iniFile, $key, $val);
        return $this;
    }
    
    /**
     * 设置指定的前缀是否使用命名空间
     * @param string $prefix 前缀
     * @param bool $useNamespace 是否使用
     * @return $this
     */
    public function setUseNamespace($prefix, $useNamespace = true)
    {
        Psr4::getInstance()->setUseNamespace($prefix, $useNamespace);
        return $this;
    }

    /**
     * 添加命名空间对应的网站目录
     * @param string $prefix 前缀
     * @param string $baseDir 对应的路径
     * @param bool $prepend 是否追加
     * @return $this
     */
    public function addNamespace($prefix, $baseDir, $prepend = false)
    {
        if (!is_dir($baseDir)) {
            throw new \Qii\Exceptions\FolderDoesNotExist(\Qii::i(1009, $baseDir), __LINE__);
        }
        $baseDir = Psr4::getInstance()->realpath($baseDir);
        Psr4::getInstance()->addNamespace($prefix, $baseDir, $prepend);
        return $this;
    }

    /**
     * 设置启动前执行的方法
     *
     * @return $this
     * @throws Exception
     */
    public function setBootstrap()
    {
        Psr4::getInstance()->loadFileByClass('Bootstrap');
        if (!class_exists('Bootstrap', false)) throw new \Qii\Exceptions\ClassNotFound(\Qii::i(1405, 'Bootstrap'), __LINE__);;
        $bootstrap = Psr4::getInstance()->instance('Bootstrap');
        if (!$bootstrap instanceof \Qii\Base\Bootstrap) {
            throw new \Qii\Exceptions\ClassInstanceof(Qii::i(1107, 'Bootstrap', 'Qii\Bootstrap'), __LINE__);;
        }
        $refectionClass = new \ReflectionClass('Bootstrap');
        $methods = $refectionClass->getMethods();
        //自动执行以init开头的公共方法
        foreach ($methods as $method) {
            $name = $method->getName();
            if (substr($name, 0, 4) == 'init' && $method->isPublic()) $bootstrap->$name();
        }
        return $this;
    }

    /**
     * 设置写loger的类
     *
     * @param LogerWriter $logerCls 日志记录类
     */
    public function setLoger($logerCls)
    {
        /*
        if (!class_exists($logerCls, false)) {
            throw new \Qii_Exceptions_ClassNotFound(Qii::i(1405, $logerCls), __LINE__);
        }*/

        $this->logerWriter = Instance::instance(
            '\Qii\Loger\Instance',
            Instance::instance($logerCls)
        );
        return $this;
    }

    /**
     * 设置数据库使用的文件
     *
     * @param $iniFile
     * @throws \Qii_Exceptions_Overwrite
     */
    public function setDBIniFile($iniFile)
    {
        Register::set(Consts::APP_DB, $iniFile);
    }

    /**
     * 获取当前数据库文件
     *
     * @return \Qii_Mix
     * @throws \Qii_Exceptions_Variable
     */
    public function getDBIniFile()
    {
        return Register::get(Consts::APP_DB);
    }

    /**
     * 设置数据库配置文件
     * @param string $ini 配置文件路径
     * @param string $env 环境
     */
    public function setDB($ini, $env = '')
    {
        if ($env == '') $env = $this->getEnv();
        $this->setDBIniFile($ini);
        if (!Register::setAppConfigure(
            Psr4::getInstance()->getFileByPrefix($ini),
            $env
        )
        ) {
            throw new \Qii\Exceptions\FileNotFound(\Qii::i(1405, $ini), __LINE__);
        }
        return $this;
    }

    /**
     * 设置路由规则
     *
     * @param string $router 路由配置文件位置
     * @return $this
     */
    public function setRouter($router)
    {
        Register::set(
            Consts::APP_SITE_ROUTER,
            Import::includes(
                Psr4::realpath(Psr4::getInstance()->getFileByPrefix($router)
                )
            )
        );
        //载入rewrite规则后重写request
        $rewrite = Psr4::loadStatic(
            '\Qii\Router\Parse',
            'get',
            \Qii\Request\Url::getPathInfo(),
            $this->request->controller,
            $this->request->action,
            $this->request->url->get(2)
        );
        $rewrite['controller'] = $rewrite['controller'] ? $rewrite['controller'] : $this->request->defaultController();
        $rewrite['action'] = $rewrite['action'] ? $rewrite['action'] : $this->request->defaultAction();
        //是否已经rewrite，如果和url中的不一样就是已经rewrite
        if ($this->request->controller != $rewrite['controller'] || $this->request->action != $rewrite['action']) {
            $this->request->setRouted(true);
        }
        $this->request->setControllerName($rewrite['controller']);
        $this->request->setActionName($rewrite['action']);
        return $this;
    }
    /**
     * sprintf 格式化语言错误信息内容
     *
     * Qii::e($message, $argv1, $argv2, ..., $line);
     * $message = sprintf($message, $argv1, $argv2, ...);
     * throw new \Qii\Exceptions\Error($message, $line);
     */
    public function showError()
    {
        return call_user_func_array(array('\Qii\Exceptions\Errors', 'e'), func_get_args());
    }
	
	public function run()
	{
        $this->helper->load(self::$workspace);
        $this->dispatcher = Psr4::getInstance()->loadClass('\Qii\Base\Dispatcher');
        if (!$this->dispatcher instanceof \Qii\Base\Dispatcher) {
            throw new \Exception('Dispatcher must instance of Qii\Base\Dispatcher', __LINE__);
        }
        //如果设置了host的话，看host对应的controller路径
        $hosts = $this->appConfigure('hosts');
        if (count($hosts) > 0) {
            foreach ($hosts AS $host) {
                if ($host['domain'] == $this->request->host) {
                    Register::set(
                        Consts::APP_DEFAULT_CONTROLLER_PREFIX,
                        ($host['path'] ? $host['path'] : $host['domain'])
                    );
                    break;
                }
            }
        }
        $this->request->setDispatcher($this->dispatcher);
        //rewrite规则
        $this->dispatcher->setRequest($this->request);
        $this->dispatcher->dispatch();
        $this->request->setDispatched(true);
        return $this;
	}
}
