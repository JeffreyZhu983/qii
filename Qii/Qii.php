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

require Qii_DIR . DS . 'Autoloader' . DS . 'Import.php';
\Qii\Autoloader\Import::setFileLoaded(Qii_DIR . DS . 'Autoloader' . DS . 'Import.php');

\Qii\Autoloader\Import::requires(array(Qii_DIR . DS .'Consts'. DS . 'Config.php',
                                Qii_DIR . DS .'Autoloader'. DS . 'Factory.php',
                                Qii_DIR . DS . 'Application.php',
                                Qii_DIR . DS .'Autoloader'. DS . 'Psr4.php',
                                Qii_DIR . DS .'Config'. DS . 'Arrays.php',
                                )
);

use \Qii\Autoloader;
use \Qii\Application;

class Qii extends Application
{

	public static function i()
    {
        return call_user_func_array(array(
            \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Language\Loader'), 'i'),
            func_get_args()
        );
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
    ->setUseNamespace('Qii\Route', true)
    ->setUseNamespace('Qii\View', true)
    ->setUseNamespace('Smarty\\', false)
    ->setUseNamespace('Smarty\\Internal', false);
    ;


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
    ->addNamespace('Qii\Route', Qii_DIR . DS . 'Route')
    ->addNamespace('Qii\View', Qii_DIR . DS . 'View')
    ->addNamespace('Smarty', Qii_DIR . DS . 'View' . DS . 'smarty')
    ->addNamespace('Smarty', Qii_DIR . DS . 'View' . DS . 'smarty' . DS . 'sysplugins');

//加载默认语言包
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('error', Qii_DIR . DS . 'Language');
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('error', Qii_DIR . DS . 'Language');
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('exception', Qii_DIR . DS . 'Language');
\Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader')->load('resource', Qii_DIR . DS . 'Language');