<?php
namespace Qii\Base;

class Dispatcher
{
    public $request;

    public function __construct()
    {

    }

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

        $controllerName = \Qii\Config\Register::get(\Qii\Config\Consts::APP_DEFAULT_CONTROLLER_PREFIX) . '_' . $controller;
        $funcArgs = array();
        if (count($args) > 2) {
            $funcArgs = array_slice($args, 2);
        }
        array_unshift($funcArgs, $controllerName);
        $psr4 = \Qii\Autoloader\Psr4::getInstance();
        $controllerCls = call_user_func_array(array($psr4, 'loadClass'), $funcArgs);
        $controllerCls->setRequest($this->request);
        $controllerCls->controller = $controllerCls;
        $controllerCls->controllerId = $controller;
        $controllerCls->actionId = $action;
        //查看是否设置了当前action的对应关系,如果设置了就走对应关系里边的,否则走当前类中的
        if ($controllerCls->actions && isset($controllerCls->actions[$action]) && $controllerCls->actions[$action]) {
            $actionArgs = array();
            $actionArgs[] = $controllerCls->actions[$action];
            //$actionArgs[] = $controllerCls;
            //$actionArgs[] = $action;
            $actionCls = call_user_func_array(array($psr4, 'loadClass'), $actionArgs);
            $actionCls->setRequest($this->request);
            $actionCls->controller = $controllerCls;
            $actionCls->actionId = $action;
            $actionCls->controllerId = $controllerCls->controllerId;
            //支持多个action对应到同一个文件，如果对应的文件中存在指定的方法就直接调用
            if (method_exists($actionCls, $action . \Qii\Config\Register::get(\Qii\Config\Consts::APP_DEFAULT_ACTION_SUFFIX))) {
                return call_user_func_array(array($actionCls, $action. \Qii\Config\Register::get(\Qii\Config\Consts::APP_DEFAULT_ACTION_SUFFIX)), $funcArgs);
            }
            if (!method_exists($actionCls, 'run')) {
                throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $controllerCls->actions[$action] . '->run'), __LINE__);
            }
            return call_user_func_array(array($actionCls, 'run'), $funcArgs);
        } else {
            array_shift($funcArgs);
            $actionName = $action . \Qii\Config\Register::get(\Qii\Config\Consts::APP_DEFAULT_ACTION_SUFFIX);
            if (!method_exists($controllerCls, $actionName) && !method_exists($controllerCls, '__call')) {
                throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $controller . '->' . $actionName), __LINE__);
            }
            return call_user_func_array(array($controllerCls, $actionName), $funcArgs);
        }
    }
}