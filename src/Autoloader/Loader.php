<?php
namespace Qii\Autoloader;

/**
 * Loader类
 */
class Loader
{

    const VERSION = '1.2';
    /**
     * @var $loaded 保存已经加载过的类实例
     */
    private $loaded;
    /**
     * @var $lastCall 最后一次访问的方法
     */
    private $lastCall;

    public function __construct()
    {
        $this->lastCall = null;
        return $this;
    }

    public function __clone()
    {
        $this->lastCall = null;
    }

    public function __get($name)
    {
        $this->lastCall = $name;
        return $this;
    }

    /**
     * 返回load对象
     *
     * @return mixed
     */
    public static function Instance()
    {
        return \Qii\Autoloader\Instance::instance('\Qii\Autoloader\Loader');
    }

    /**
     * 实现自动加载
     *
     * @param $method
     * @param $argvs
     * @return object
     */
    public function __call($method, $args)
    {
        $class = array_shift($args);
        if ($this->lastCall) {
            $className = $this->lastCall . '\\' . $method;
            \Qii\Autoloader\Import::requireByClass($className);
        } else {
            $className = $method . '\\' . $class;
        }
        $this->lastCall = null;
        if (isset($this->loaded[$className])) return $this->loaded[$className];
        return $this->loaded[$className] = call_user_func_array(array('\Qii\Autoloader\Instance', 'Instance'), array_merge(array($className), $args));
    }
}