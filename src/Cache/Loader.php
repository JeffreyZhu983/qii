<?php
/**
 * 缓存类
 *
 * @author Zhu Jinhui<zhujinhui@zhangyue.com>2015-10-31 22:35
 */
namespace Qii\Cache;

class Loader
{
    const VERSION = '1.2';
    private $cache;

    public function __construct($cache)
    {
        $this->setCache($cache);
    }

    /**
     * 初始化缓存类
     *
     * @param Array $policy
     * @return Object
     */
    public function initialization($policy)
    {
        return \Qii\Autoloader\Psr4::getInstance()->loadClass('Qii\Cache\\' . ucwords($this->cache), $policy);
    }

    /**
     * 设置用于缓存的类
     *
     * @param String $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        $cacheFile = dirname(__FILE__) . DS . DS . ucwords($cache) . '.php';
        if (!is_file($cacheFile)) {
            throw new \Exception('Unsupport cache class '. $cacheFile);
        }
        \Qii\Autoloader\Import::requires($cacheFile);
    }
}