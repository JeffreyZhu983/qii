<?php
namespace Qii;

class Application 
{
    /**
     * 存储网站配置文件内容
     *
     * @var array $config 配置内容
     */
    protected static $config = [];
    /**
     * @var object $logerWriter 写日志工具
     */
    public $logerWriter = null;
    /**
     * @var string $workspace 工作目录
     */
    private static $workspace = './';

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
     * 设置网站的工作目录，可以通过此方法将网站的重要文件指向到其他目录
     *
     * @param string $workspace 工作目录
     * @return $this
     */
    public function setWorkspace($workspace = './')
    {
        //此处转换成真实路径，防止workspace中引入的文件出错
        if (!is_dir($workspace)) {
            throw new \Qii\Exceptions\FolderDoesNotExist(\Qii::i(1045, $workspace), __LINE__);
        }
        $workspace = \Qii\Autoloader\Psr4::getInstance()->realpath($workspace);
        \Qii\Autoloader\Psr4::getInstance()->removeNamespace('workspace', self::$workspace);
        //如果配置了使用namespace就走namespace
        self::$workspace = $workspace;
        \Qii\Autoloader\Psr4::getInstance()->addNamespace('workspace', $workspace, true);
        foreach (self::$paths AS $path) {
            \Qii\Autoloader\Psr4::getInstance()->addNamespace($path, $workspace . '\\' . $path);
        }

        return $this;
    }

    public function getWorkspace()
    {
        return self::$workspace;
    }
    /**
     * 设置网站配置文件
     *
     * @param array $config 配置文件
     */
	public function setConfig($key, $config = [])
	{
        \Qii\Autoloader\Factory::getInstance('\Qii\Config\Arrays')
            ->set(\Qii\Consts\Config::APP_CONFIGURE . '['. $key.']', $config);
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
                ->get(\Qii\Consts\Config::APP_CONFIGURE);
        }
        return \Qii\Autoloader\Factory::getInstance('\Qii\Config\Arrays')
            ->get(\Qii\Consts\Config::APP_CONFIGURE . '['.$key.']');
    }
    /**
     * 设置Route配置
     * @param array $route
     */
	public function setRoute($route = [])
    {
        \Qii\Autoloader\Factory::getInstance('\Qii\Config\Arrays')
            ->set(\Qii\Consts\Config::APP_SITE_ROUTER, $config);
    }
	
	public function run()
	{
		print_r($this->getConfig());
	}
}
