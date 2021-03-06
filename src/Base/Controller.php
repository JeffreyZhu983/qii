<?php
/**
 * 控制器基类
 */

namespace Qii\Base;

use \Qii\Autoloader\Psr4;

use \Qii\Config\Register;
use \Qii\Config\Consts;

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
        $this->load = Psr4::getInstance()->loadClass('\Qii\Autoloader\Loader');
        $this->request = Psr4::getInstance()->loadClass('\Qii\Request\Http');
        $this->controllerId = $this->request->controller;
        $this->actionId = $this->request->action;
        $this->language = \Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader');
        $this->response = \Qii\Autoloader\Factory::getInstance('\Qii\Base\Response');
        $this->cache = new \stdClass();
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
    protected function initView(){}
    
    /**
     * 设置view
     *
     * @param string $engine
     * @param array $policy
     * @return mixed
     */
    public function setView($engine = 'smarty', $policy = array())
    {
        $this->view = \Qii::getInstance()->setView($engine, $policy)->getView();
        $this->initView();
        $this->response->setRender($this->view);
        return $this->view;
    }
    /**
     * 设置缓存
     *
     * @param string $engine 缓存方法
     * @param array $policy 缓存策略
     */
    public function setCache($engine = '', $policy = array())
    {
        return $this->cache->$engine = \Qii::getInstance()->setCache($engine, $policy);
    }
    
    /**
     * 开启数据库操作
     */
    final public function enableDB()
    {
        return $this->db = Psr4::getInstance()->loadClass('\Qii\Driver\Model');
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
     * view assign
     * @param string | array $key
     * @param mix $val 值
     * @return void
     */
    public function assign($key, $val = null)
    {
        if(!$this->view && !($this->view instanceof \Qii\View\Intf)) {
            throw new \Exception(_i(6001), __LINE__);
        }

        if(is_array($key)) {
            $this->view->assign($key);
        }else{
            $this->view->assign($key, $val);
        }
    }

    /**
     * 渲染
     * @param string $tpl 模板路径
     * @param array $arr 需要替换的变量
     */
    public function render($tpl, $arr = [])
    {
        if(!$this->view && !($this->view instanceof \Qii\View\Intf)) {
            throw new \Exception(_i(6001), __LINE__);
        }

        $this->view->assign($arr);
        $this->view->display($tpl);
    }
    
    /**
     * 设置 response
     * @param $request
     */
    public function setResponse(\Qii\Base\Response $response)
    {
        return $this->response = $response;
    }
    
    /**
     * 设置request
     * @param $request
     */
    public function setRequest(\Qii\Base\Request $request)
    {
        return $this->request = $request;
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
        if (!$this->response || !is_object($this->response)) {
            return;
        }
        if ($this->response instanceof \Qii\Base\Response) {
            if ($this->response->needRender() && $this->view && $this->view instanceof \Qii\View\Intf) {
                $this->response->setRender($this->view);
            }
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