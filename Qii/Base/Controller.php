<?php
/**
 * 控制器基类
 */
namespace Qii\Base;

/**
 * Qii_Controller_Abstract class
 * @author Zhu Jinhui
 */
abstract class Controller
{
    /**
     * @var array $actions action对应的方法列表，设置了的就转发
     */
    public $actions = array();
    /**
     * @var \Qii\Autoloader\Psr4::getInstance() $load
     */
    public $load;
    /**
     * @var \Qii\Language\Loader $language
     */
    public $language;
    /**
     * @var Qii\Controller\Base $controller
     */
    public $controller;
    /**
     * @var string $controllerId controller名
     */
    public $controllerId = 'index';
    /**
     * @var string $actionId action名
     */
    public $actionId = 'index';
    /**
     * @var Qii\Request\Base $request
     */
    public $request;
    /**
     * @var \Qii\Base\Response $response
     */
    public $response;
    /**
     * @var Qii_Driver_xxx_Connection
     */
    public $db;
    /**
     * @var mixed
     */
    public $view;
    /**
     * @var Qii_Cache_Abslute $cache
     */
    public $cache;
    /**
     * 是否启用view模块
     * @var bool
     */
    public $enableView = false;

    /**
     * 是否启用Model
     * @var bool
     */
    public $enableDB = false;

    public function __construct()
    {
        $this->load = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Autoloader\Loader');
        $this->request = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Request\Http');
        $this->controllerId = $this->request->controller;
        $this->actionId = $this->request->action;
        //载入model
        if ($this->enableDB) {
            $this->enableDB();
        }
        //载入view
        if ($this->enableView) {
            $this->view = $this->setView();
        }

        if (!$this->beforeRun()) {
            exit();
        }
    }

    /**
     * 启用view后调用初始化View方法
     */
    protected function initView()
    {
    }

    /**
     * 设置view
     *
     * @param string $engine
     * @param array $policy
     * @return mixed
     */
    public function setView($engine = 'smarty', $policy = array())
    {
        $viewConfigure = \Qii::appConfigure('view');
        //如果之前实例化过相同的就不再实例化
        if (!$engine) $engine = $viewConfigure['engine'];
        $policy = (array)$policy;
        if (!$policy) {
            $policy = array_merge($policy, $viewConfigure[$engine]);
        }
        $viewEngine = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\View\Loader');
        $viewEngine->setView($engine, $policy);
        $this->view = $viewEngine->getView();
        if(method_exists($this, 'initView'))
        {
            $this->initView();
        }
        return $this->view ;
    }

    /**
     * 设置缓存
     *
     * @param string $engine 缓存方法
     * @param array $policy 缓存策略
     */
    public function setCache($engine = '', $policy = array())
    {
        $engine = $engine == '' ? \Qii::appConfigure('cache') : $engine;
        $basicPolicy = array(
            'servers' => $this->getCachePolicy($engine),
        );
        if ($basicPolicy['servers']) {
            $policy = array_merge($basicPolicy, $policy);
        }
        $loader = new \Qii\Cache\Loader($engine);
        return $this->cache = $loader->initialization($policy);
    }

    /**
     * 获取缓存的策略
     * @param String $cache 缓存的内容
     * @return multitype:multitype:Ambigous <>
     */
    final public function getCachePolicy($cache)
    {
        $data = array();
        if (!$cache) return $data;
        $cacheInfo = \Qi\Config\Register::getAppConfigure(\Qi\Config\Register::get(\Qii\Config\Consts::APP_INI_FILE), $cache);
        if (!$cacheInfo) return $data;

        $servers = explode(";", $cacheInfo['servers']);
        $ports = explode(";", $cacheInfo['ports']);
        for ($i = 0; $i < count($servers); $i++) {
            $data[] = array('host' => $servers[$i], 'port' => $ports[$i]);
        }
        return $data;
    }
    /**
     * 开启数据库操作
     */
    final public function enableDB()
    {
        return $this->db = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Driver\Model');
    }

    /**
     * 获取view
     *
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * 设置 response
     * @param $request
     */
    public function setResponse(\Qii\Base\Response $response)
    {
        $this->response = $response;
    }
    /**
     * 设置request
     * @param $request
     */
    public function setRequest(\Qii\Base\Request $request)
    {
        $this->request = $request;
    }


    /**
     * 只要继承的方法调用parent::__construct()就开始执行
     * 此方法如果返回false，将不再往下继续执行
     */
    protected function beforeRun()
    {
        return true;
    }

    /**
     * 执行完dispatch后调用
     */
    protected function afterRun()
    {
        if(!$this->response || !is_object($this->response))
        {
            return;
        }
        if($this->response instanceof \Qii\Base\Response)
        {
            $this->response->response();
        }
    }

    /**
     * 转发
     * @param String $controller
     * @param String $action
     * @return mixed
     */
    final public function dispatch($controller, $action)
    {
        $this->request->setControllerName($controller);
        $this->request->setActionName($action);
        \Qii::getInstance()->dispatcher->setRequest($this->request);
        return call_user_func_array(array(\Qii::getInstance()->dispatcher, 'dispatch'), func_get_args());
    }

    /**
     * 获取当前使用的controller
     *
     * @return string
     */
    final public function getCurrentController()
    {
        return get_called_class();
    }

    /**
     * 获取 request 方法
     * @return Qii_Request_Http
     */
    final public function getRequest()
    {
        return $this->request;
    }

    /**
     * 获取response类
     * @return mixed
     */
    final public function getResponse()
    {
        return $this->response;
    }

    /**
     * 设置forward
     * @param string $controller controller名
     * @param string $action action名
     */
    public function setForward($controller, $action)
    {
        $this->request->setControllerName($controller);
        $this->request->setActionName($action);
        $this->request->setForward(true);
    }

    /**
     * afterRun 和 forward 执行
     */
    public function __destruct()
    {
        $this->afterRun($this->controllerId, $this->actionId);
        if ($this->request && $this->request->isForward()) {
            $this->request->setForward(false);
            \Qii::getInstance()->dispatcher->setRequest($this->request);
            \Qii::getInstance()->dispatcher->dispatch();
            $this->request->setDispatched(true);
        }
    }
}