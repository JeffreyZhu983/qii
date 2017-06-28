<?php
namespace Qii\Autoloader;

use \Qii\Exceptions;

class Factory
{
    /**
     * @param array $_instance 实例化对象的存储池
     */
    protected static $instance = [];
    /**
     * 以 new \Qii\Autoloader\Factory($className)的方式实例化对象
     */
    public function __construct($className)
    {
        return Factory::getInstance($className);
    }
    /**
     * 实例化对象
     * @param string $className 类名
     */
    public static function getInstance($className)
    {
        if(!$className)
        {
            return \Qii::e('CLASS_NAME_IS_NULL', $className);
        }
        if(isset(Factory::$instance[$className]) &&
            Factory::$instance[$className] != null
        ){
            return Factory::$instance[$className];
        }

        $args = func_get_args();
        array_shift($args);
        
        if(!class_exists($className, false))
        {
            $className = Psr4::getInstance()->getClassName($className);
        }
        $refClass = new \ReflectionClass($className);
        $instance = $refClass->newInstanceArgs($args);
        if ($refClass->hasMethod('_initialize')) {
            call_user_func_array(array($instance, '_initialize'), $args);
        }
        return Factory::$instance[$className] = $instance;
    }
}