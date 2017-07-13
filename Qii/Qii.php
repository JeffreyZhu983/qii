<?php
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
 * 定义操作系统类型
 */
define('OS', strtoupper(substr(PHP_OS, 0, 3)));

define('IS_CLI', php_sapi_name() == 'cli' ? true : false);
if(IS_CLI) {
    define('PATH_INFO', array_pop($argv));
}else{
    define('PATH_INFO', isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
}

/**
 * EOL 和 SPACE
 */
define('QII_EOL', IS_CLI ? PHP_EOL : '<br />');
define('QII_SPACE', IS_CLI ? ' ' : '&nbsp;');

require Qii_DIR . DS . 'Autoloader' . DS . 'Import.php';
\Qii\Autoloader\Import::setFileLoaded(Qii_DIR . DS . 'Autoloader' . DS . 'Import.php');

\Qii\Autoloader\Import::requires(array(Qii_DIR . DS .'Consts'. DS . 'Config.php',
                                Qii_DIR . DS . 'Functions'. DS . 'Funcs.php',
                                Qii_DIR . DS .'Autoloader'. DS . 'Factory.php',
                                Qii_DIR . DS . 'Application.php',
                                Qii_DIR . DS .'Autoloader'. DS . 'Psr4.php',
                                Qii_DIR . DS .'Config'. DS . 'Arrays.php',
                                )
);

use \Qii\Application;

use \Qii\Autoloader\Factory;
use \Qii\Autoloader\Psr4;

use \Qii\Config\Register;

class Qii extends Application
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Instance func
     * 
     **/
    public static function getInstance()
    {
        if(func_num_args() > 0)
        {
            $args = func_get_args();
            return call_user_func_array(array('\Qii\Autoloader\Factory', 'getInstance'), $args);    
        }
	    return Factory::getInstance('\Qii');
    }
    /**
     * 设置private 属性
     *
     * @param String $name
     * @param Mix $value
     */
    public static function setPrivate($name, $value)
    {
        Psr4::getInstance()->loadClass('\Qii\Config\Arrays')->setPrivate($name, $value);
    }

    /**
     * 获取private属性
     *
     * @param String $name
     * @param String $key
     * @return Mix
     */
    public static function getPrivate($name, $key = '')
    {
        $private = Psr4::getInstance()->loadClass('\Qii\Config\Arrays')->getPrivate($name);
        if (preg_match('/^\s*$/', $key)) {
            return $private;
        }
        if (isset($private[$key])) return $private[$key];
    }
    
	public static function i()
    {
        return call_user_func_array(array(
            Psr4::getInstance()->loadClass('\Qii\Language\Loader'), 'i'),
            func_get_args()
        );
    }
    /**
     * 错误设置，如果满足给定的条件就直接返回false，否则在设置了错误页面的情况下返回true
     * 如果出错后要终止的话，需要自行处理，此方法不错停止不执行
     *
     * @param Bool $condition
     * @param int $line 出错的行数，这样以便轻松定位错误
     * @param String $msg
     * @param Int|String $code
     * @return Bool
     */
    public static function setError($condition, $line = 0, $code, $args = null)
    {
        return call_user_func_array(array('\Qii\Exceptions\Error', 'setError'), func_get_args());
    }
    /**
     * 抛出异常
     *
     * @return mixed
     */
    public static function e()
    {
        return call_user_func_array(array('\Qii\Exceptions\Errors', 'e'), func_get_args());
    }
    /**
     * 返回当前app的配置
     * @param string $key 如果需要返回单独的某一个key就指定一下这个值
     * @return Mix
     */
    public static function appConfigure($key = null)
    {
        return Register::getAppConfigure(\Qii::getInstance()->getAppIniFile(), $key);
    }
    
    /**
     * 当调用Qii不存在的方法的时候，试图通过Autoload去加载对应的类
     * 示例：
     * \Qii::getInstance()->Qii_Autoloader_Psr4('instance', 'Model\User')；
     *    此方法将调用：Qii_Autoloader_Psr4->instance('Model\User');
     */
    public function __call($className, $args)
    {
        return call_user_func_array(array(Psr4::getInstance(), 'loadClass'), $args);
    }
    /**
     * 当调用不存在的静态方法的时候会试图执行对应的类和静态方法
     * 示例：
     * \Qii::Qii_Autoloader_Psr4('getInstance')
     *    此方法将调用：\Qii\Autoloader\Psr4::getInstance静态方法
     */
    public static function __callStatic($className, $args)
    {
        $method = array_shift($args);
        $className = Psr4::getInstance()->getClassName($className);
        return call_user_func_array($className . '::' . $method, $args);
    }
}

if (!function_exists('catch_fatal_error')) {
    function catch_fatal_error()
    {
        // Getting Last Error
        $error = error_get_last();
        // Check if Last error is of type FATAL
        if (isset($error['type']) && $error['type'] == E_ERROR) {
            // Fatal Error Occurs
            $message = array();
            $message[] = 'Error file : ' . ltrim($error['file'], Psr4::realpath($_SERVER['DOCUMENT_ROOT']));
            $message[] = 'Error line : ' . $error['line'] . ' on ' . \Qii\Exceptions\Errors::getLineMessage($error['file'], $error['line']);
            $message[] = 'Error description : ' . $error['message'];
            \Qii\Exceptions\Error::showError($message);
        }
    }
}

\Qii\Autoloader\Psr4::getInstance()
    ->register()
    ->setUseNamespace('Qii\\', true)
    ->setUseNamespace('Qii\Action', true)
    ->setUseNamespace('Qii\Autoloader', true)
    ->setUseNamespace('Qii\Bootstrap', true)
    ->setUseNamespace('Qii\Config', true)
    ->setUseNamespace('Qii\Consts', true)
    ->setUseNamespace('Qii\Controller', true)
    ->setUseNamespace('Qii\Exceptions', true)
    ->setUseNamespace('Qii\Language', true)
    ->setUseNamespace('Qii\Library', true)
    ->setUseNamespace('Qii\Loger', true)
    ->setUseNamespace('Qii\Plugin', true)
    ->setUseNamespace('Qii\Request', false)
    ->setUseNamespace('Qii\Router', true)
    ->setUseNamespace('Qii\View', true)
    ->setUseNamespace('WhichBrowser', true)
    ->setUseNamespace('Smarty\\', false)
    ->setUseNamespace('Smarty\\Internal', false);


\Qii\Autoloader\Psr4::getInstance()
    ->addNamespace('Qii\\', Qii_DIR . DS)
    ->addNamespace('Qii\Action', Qii_DIR . DS . 'Action')
    ->addNamespace('Qii\Autoloader', Qii_DIR . DS . 'Autoloader')
    ->addNamespace('Qii\Controller', Qii_DIR . DS . 'Controller')
    ->addNamespace('Qii\Bootstrap', Qii_DIR . DS . 'Bootstrap')
    ->addNamespace('Qii\Config', Qii_DIR . DS . 'Config')
    ->addNamespace('Qii\Consts', Qii_DIR . DS . 'Consts')
    ->addNamespace('Qii\Exceptions', Qii_DIR . DS . 'Exceptions')
    ->addNamespace('Qii\Language', Qii_DIR . DS . 'Language')
    ->addNamespace('Qii\Library', Qii_DIR . DS . 'Library')
    ->addNamespace('Qii\Loger', Qii_DIR . DS . 'Loger')
    ->addNamespace('Qii\Plugin', Qii_DIR . DS . 'Plugin')
    ->addNamespace('Qii\Request', Qii_DIR . DS . 'Request')
    ->addNamespace('Qii\Response', Qii_DIR . DS . 'Response')
    ->addNamespace('Qii\Router', Qii_DIR . DS . 'Router')
    ->addNamespace('Qii\View', Qii_DIR . DS . 'View')
    ->addNamespace('Smarty', Qii_DIR . DS . 'View' . DS . 'smarty')
    ->addNamespace('Smarty', Qii_DIR . DS . 'View' . DS . 'smarty' . DS . 'sysplugins')
    ->addNamespace('WhichBrowser', Qii_DIR . DS . 'Library'. DS . 'Third'. DS . 'WhichBrowser')
;

//加载默认语言包
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('error', Qii_DIR . DS . 'Language');
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('error', Qii_DIR . DS . 'Language');
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('exception', Qii_DIR . DS . 'Language');
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('resource', Qii_DIR . DS . 'Language');


//捕获FATAL错误，用户可以选择记录到日志，还是直接显示或者不显示错误
register_shutdown_function('catch_fatal_error');
set_exception_handler(array('\Qii\Exceptions\Errors', 'getError'));
set_error_handler(array('\Qii\Exceptions\Errors', 'getError'), E_USER_ERROR);