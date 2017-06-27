<?php
namespace Qii\Config;
/**
 * 系统设置
 *
 */
class Setting
{
    protected static $instance;
    public $language;

    private function __construct()
    {

    }

    /**
     * 返回Qii初始化类
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设置默认语言
     *
     * @return $this
     * @throws Exception
     */
    public function setDefaultLanguage()
    {
        $this->language = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Language\Loader');
        //加载语言包
        $this->language->load('error', Qii_DIR);
        $this->language->load('exception', Qii_DIR);
        return $this;
    }

    /**
     * 设置区域
     *
     * @return $this
     */
    public function setDefaultTimeZone()
    {
        //设置时区
        $timezone = \Qii::getInstance()->appConfigure('timezone');
        if ($timezone) date_default_timezone_set($timezone);
        return $this;
    }

    /**
     * 设置默认的controller和action
     */
    public function setDefaultControllerAction()
    {
        //设置默认controller及controller方法前缀
        \Qii\Config\Register::set(\Qii\Consts\Config::APP_DEFAULT_CONTROLLER, \Qii::getInstance()->appConfigure('controller')['default']);
        \Qii\Config\Register::set(\Qii\Consts\Config::APP_DEFAULT_CONTROLLER_PREFIX, \Qii::getInstance()->appConfigure('controller')['prefix']);

        //设置默认action及方法名后缀
        \Qii\Config\Register::set(\Qii\Consts\Config::APP_DEFAULT_ACTION, \Qii::getInstance()->appConfigure('action')['default']);
        \Qii\Config\Register::set(\Qii\Consts\Config::APP_DEFAULT_ACTION_SUFFIX, \Qii::getInstance()->appConfigure('action')['suffix']);
        return $this;
    }

    /**
     * 设置默认的namespace
     */
    public function setDefaultNamespace()
    {
        //配置文件中如果设置了使用namespace就将指定的前缀添加到namespace中
        $namespaces = \Qii::appConfigure('namespace');
        if (is_array($namespaces) && isset($namespaces['use'])
            && $namespaces['use'] && isset($namespaces['list'])
            && is_array($namespaces['list'])
        ) {
            foreach ($namespaces['list'] AS $namespace => $val) {
                \Qii::getInstance()->setUseNamespace($namespace, $val);
            }
        }
        return $this;
    }
}