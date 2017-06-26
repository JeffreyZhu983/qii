<?php
namespace Qii;

use \Qii\Autoloader;

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

include(Qii_DIR . DS .'Autoloader'. DS . 'Factory.php');
include(Qii_DIR . DS .'Config'. DS . 'Arrays.php');

class Application 
{
    /**
     * 存储网站配置文件内容
     *
     * @var array $_config 配置内容
     */
    protected static $_config = [];

	public function __construct()
	{
		
	}

    /**
     * 初始化本实例对象
     *
     * @return object
     */
	public static function getInstance()
	{

	    return \Qii\Autoloader\Factory::getInstance('\Qii\Application');
	}

    /**
     * 设置网站配置文件
     *
     * @param array $config 配置文件
     */
	public function setConfig($config = [])
	{
        \Qii\Autoloader\Factory::getInstance('\Qii\Config\Arrays')
            ->set('Application', $config);
	}

    /**
     * 获取指定配置内容key的值
     *
     * @param string $key 配置内容key
     * @return mixed|null
     */
	public function getConfig($key = null)
    {
        if(!$key) {
            return \Qii\Autoloader\Factory::getInstance('\Qii\Config\Arrays')
                ->get('Application');
        }

        return \Qii\Autoloader\Factory::getInstance('\Qii\Config\Arrays')
            ->get('Application['.$key.']');
    }

	public function setRoute($route = [])
    {
        Application::$_config['route'] = $route;
    }
	
	public function run()
	{
		print_r($this->getConfig());
	}

	public static function _i()
    {

    }

    /**
     * 抛出异常
     *
     * @return mixed
     */
    public static function _e()
    {
        return call_user_func_array(array('\Qii\Exceptions\Errors', 'e'), func_get_args());
    }
}
