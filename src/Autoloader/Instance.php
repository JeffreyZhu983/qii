<?php
namespace Qii\Autoloader;
/**
 * Instance类
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-10-22 19:45
 * @version 1.3
 *
 * 使用方法：
 *
 * $class = Qii\Instance::init('className');
 * 再次使用就可以直接通过Qii\Instance::getInstance('className');
 */
class Instance
{
    const VERSION = '1.2';
    /**
     * @var APP_LOAD_PREFIX 保存类到以APP_LOAD_PREFIX开头的key中
     */
    const APP_LOAD_PREFIX = '__qii_instance';
    /**
     * @var $loadedClass 保存加载过的类
     */
    protected static $loadedClass = array();

    /**
     * 获取初始化的类
     * @param string $className 类名
     * @reutrn object
     */
    public static function getInstance($className)
    {
        if (isset(self::$loadedClass[self::APP_LOAD_PREFIX . $className])) return self::$loadedClass[self::APP_LOAD_PREFIX . $className];
        throw new \Qii\Exceptions\CallUndefinedClass(\Qii::i('1105', $className), __LINE__);
    }

    /**
     * 初始化类保存到_loadedClass中并返回
     */
    public static function initialize()
    {
        $args = func_get_args();
        $className = array_shift($args);
        if (!class_exists($className, false)) throw new \Qii\Exceptions\CallUndefinedClass(\Qii::i('1105', $className), __LINE__);
        if (isset(self::$loadedClass[self::APP_LOAD_PREFIX . $className])
            && self::$loadedClass[self::APP_LOAD_PREFIX . $className]
        ) return self::$loadedClass[self::APP_LOAD_PREFIX . $className];
        $loader = new \ReflectionClass($className);
        try {
            $instance = $loader->newInstanceArgs($args);
            self::$loadedClass[self::APP_LOAD_PREFIX . $className] = $instance;
            //如果有_initialize方法就自动调用_initialize方法，并将参数传递给_initialize方法
            if ($loader->hasMethod('_initialize')) {
                call_user_func_array(array($instance, '_initialize'), $args);
            }
            return self::$loadedClass[self::APP_LOAD_PREFIX . $className];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), __LINE__);
        }
    }

    /**
     * 加载文件后再初始化
     */
    public static function instance()
    {
        $args = func_get_args();
        $className = array_shift($args);
        \Qii\Autoloader\Psr4::getInstance()->loadFileByClass($className);
        return call_user_func_array(array('\Qii\Autoloader\Instance', 'initialize'), array_merge(array($className), $args));
    }
}