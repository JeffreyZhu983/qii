<?php
namespace Qii\Autoloader;

/**
 * Helper 将自动注册到系统 Helper中，直接调用即可
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-10-24 23:18
 * @version 1.2
 */
class Helper
{
    const VERSION = '1.2';
    /**
     * @var $helpers helper类
     */
    public static $helpers = array();

    public function __construct()
    {
        self::$helpers = array();
    }

    /**
     * 使用属性返回helper类
     * @param string $name 不带helper\的类名
     * @return object
     */
    public function __get($name)
    {
        if (substr($name, 0, 7) == 'Helper\\') return $this->get($name);
        return $this->get('Helper\\' . $name);
    }

    /**
     * 获取helper类，如果没有实例化就抛出异常
     * @param string $helper
     * @return object
     */
    public function get($helper)
    {
        if (isset(self::$helpers[$helper])) return self::$helpers[$helper];
        throw new \Qii\Exceptions\CallUndefinedClass(\Qii::i('1105', $helper), __LINE__);
    }

    /*
     * 自动加载Helper目录中文件，支持自动实例化对象
     * @param string $appPath 自动加载指定目录中文件
     */
    public function load($appPath = '')
    {
        if ($appPath == '') {
            return;
        }
        if (!is_dir($appPath . DS . 'helper')) {
            return;
        }
        foreach (glob(str_replace("//", DS, $appPath . DS . 'helper' . DS . '*.php'), GLOB_BRACE) AS $file) {
            if(\Qii\Autoloader\Import::requires($file)){
                //如果里边包含class的话就将class注册到Qii::instance('class');
                $className = \Qii\Autoloader\Psr4::getInstance()->getClassName('helper\\' . str_replace(array('.php', '.'), array('', '_'), basename($file)));

                if (class_exists($className, false)) {
                    self::$helpers[$className] = \Qii\Autoloader\Psr4::getInstance()->instance($className);
                }
            }
        }
    }
}