<?php
/**
 * Action 
 */
namespace Qii\Base;

class Action extends Controller
{
    public $controllerId;
    public $actionId;
    public $response;
    public function __construct()
    {

    }

    public function __call($method, $args)
    {
    	return call_user_func_array(array($this->controller, $method), $args);
    }
}