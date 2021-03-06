<?php
namespace Qii\Base;

use \Qii\Config\Register;
use \Qii\Config\Consts;

class Dispatcher
{
    public $request;

    public $controllerCls = null;

    public $actionCls = null;

    public function __construct()
    {

    }
    /**
     * 设置请求
     * @param \Qii\Request\Http $request 当前请求
     */
    public function setRequest(\Qii\Request\Http $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * 转发
     * @param string $controller
     * @param string $action
     * @return mixed
     */
    public function dispatch($controller = '', $action = '')
    {
        $args = func_get_args();
        $controller = $controller != '' ? $controller : $this->request->getControllerName();
        $action = $action != '' ? $action : $this->request->getActionName();

        $controllerName = $controller;
        //如果controller以\开头，就不添加默认前缀 Update at 2018-05-29 13:50
        if(substr($controller,0, 1) != '\\') {
            $controllerName = Register::get(Consts::APP_DEFAULT_CONTROLLER_PREFIX) . '_' . $controller;
        }
        $funcArgs = array();
        if (count($args) > 2) {
            $funcArgs = array_slice($args, 2);
        }
        array_unshift($funcArgs, $controllerName);
        $psr4 = \Qii\Autoloader\Psr4::getInstance();
        $controllerCls = call_user_func_array(array($psr4, 'loadClass'), $funcArgs);

        $method = new \ReflectionMethod($this, 'dispatch');
        foreach($method->getParameters() as $property)
        {
        	$param = $property->getName();
        	$this->request->setParam($param, $$param);
        }
        $this->controllerCls = $controllerCls;
        $this->controllerCls->setRequest($this->request);
        $this->controllerCls->controller = $controllerCls;
        $this->controllerCls->controllerId = $controller;
        $this->controllerCls->actionId = $action;
        $response = null;
        //查看是否设置了当前action的对应关系,如果设置了就走对应关系里边的,否则走当前类中的
        if ($this->controllerCls->actions && isset($this->controllerCls->actions[$action]) && $this->controllerCls->actions[$action]) {
            $actionArgs = array();
            $actionArgs[] = $this->controllerCls->actions[$action];
            $actionCls = call_user_func_array(array($psr4, 'loadClass'), $actionArgs);
            $this->actionCls = $actionCls;
            $this->actionCls->setRequest($this->request);
            $this->actionCls->controller = $this->controllerCls;
            $this->actionCls->actionId = $action;
            $this->actionCls->controllerId = $this->controllerCls->controllerId;
            //支持多个action对应到同一个文件，如果对应的文件中存在指定的方法就直接调用
            if (method_exists($this->actionCls, $action . Register::get(Consts::APP_DEFAULT_ACTION_SUFFIX))) {
                $this->actionCls->response = $response = call_user_func_array(array($this->actionCls, $action. Register::get(Consts::APP_DEFAULT_ACTION_SUFFIX)), $funcArgs);
            }
            if(method_exists($this->actionCls, 'initialization'))
            {
                call_user_func_array(array($this->actionCls, 'initialization'), array($this->actionCls));
            }
            if (!method_exists($this->actionCls, 'run')) {
                throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $this->controllerCls->actions[$action] . '->run'), __LINE__);
            }
            $response = call_user_func_array(array($this->actionCls, 'run'), array_slice($funcArgs, 1));
        } else {
            if(method_exists($this->controllerCls, 'initialization'))
            {
                call_user_func_array(array($this->controllerCls, 'initialization'), array($this->controllerCls));
            }
            array_shift($funcArgs);
            $actionName = $action . Register::get(Consts::APP_DEFAULT_ACTION_SUFFIX);
            if (!method_exists($this->controllerCls, $actionName) && !method_exists($this->controllerCls, '__call')) {
                throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $controller . '->' . $actionName), __LINE__);
            }
            $this->controllerCls->response = $response = call_user_func_array(array($this->controllerCls, $actionName), $funcArgs);
        }
        return $response;
    }
}