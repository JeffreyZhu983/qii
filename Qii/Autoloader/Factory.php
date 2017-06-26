<?php
namespace Qii\Autoloader;

use \Qii\Exceptions;

class Factory
{
    protected static $_instance = [];

    public static function getInstance($className)
    {
        if(!$className)
        {
            return \_e('CLASS_NAME_IS_NULL', $className);
        }
        if(isset(Factory::$_instance[$className]) &&
            Factory::$_instance[$className] != null
        ){
            return Factory::$_instance[$className];
        }
        Factory::$_instance[$className] = new $className;

        return Factory::$_instance[$className];
    }
}