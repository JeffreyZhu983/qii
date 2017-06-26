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
        $className = Psr4::getInstance()->getClassName($className);
        if(isset(Factory::$instance[$className]) &&
            Factory::$instance[$className] != null
        ){
            return Factory::$instance[$className];
        }
        Factory::$instance[$className] = new $className;

        return Factory::$instance[$className];
    }
}