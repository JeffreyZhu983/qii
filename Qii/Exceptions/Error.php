<?php

namespace Qii\Exceptions;

/**
 *
 * Class Qii_Exceptions_Error
 * @package Qii
 */
class Error
{
    const VERSION = '1.2';
    
    public function __construct()
    {
    
    }
    
    public static function index()
    {
        $args = func_get_args();
        echo \Qii::i('1108', $args[0], $args[1]);
    }
    
    /**
     * 返回程序的调用栈
     */
    static public function getTrace()
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
    
    /**
     * 程序的调用栈生成字符串
     */
    static public function getTraceAsString()
    {
        $e = new \Exception();
        $trace = explode("\n", $e->getTraceAsString());
        $trace = array_reverse($trace);
        array_shift($trace);
        array_pop($trace);
        $length = count($trace);
        $result = array();
        
        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1) . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }
        
        return "\t" . implode("\n\t", $result);
    }
    
    /**
     * 错误描述
     * @return string
     */
    static public function getMessage()
    {
        $e = new \Exception();
        return $e->getMessage();
    }
    
    /**
     * 错误码
     * @return int
     */
    static public function getCode()
    {
        return __LINE__;
    }
    
    /**
     * 错误设置，如果满足给定的条件就直接返回false，否则在设置了错误页面的情况下返回true
     * 如果出错后要终止的话，需要自行处理，此方法不错停止不执行
     *
     * @param bool $condition
     * @param int $line 出错的行数，这样以便轻松定位错误
     * @param string $msg
     * @param int|string $code
     * @param string $args ...
     * @return bool
     */
    public static function setError($condition, $line = 0, $code, $args = null, $msg = null)
    {
        if ($condition) {
            return false;
        }
        $appConfigure = \Qii::appConfigure();
        //如果是调试模式就直接抛出异常
        $isDebug = $appConfigure['debug'];
        $message = array();
        $message[] = explode("\n", self::getTraceAsString());
        if (\Qii::getInstance()->logerWriter != null) {
            $message[] = 'Referer:' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : \Qii::getInstance()->request->url->getCurrentURL());
            \Qii::getInstance()->logerWriter->writeLog($message);
        }
        if ($isDebug) {
            $args = array_slice(func_get_args(), 2);
            throw new \Exception(call_user_func_array(array('\Qii', 'i'), $args), $line);
        }
        $errorPage = $appConfigure['errorPage'];
        $env = \Qii\Config\Register::get(\Qii\Config\Consts::APP_ENVIRON, 'dev');
        if ($env != 'product' && $errorPage != null) {
            list($controller, $action) = explode(':', $appConfigure['errorPage']);
            $controllerCls = \Qii\Config\Register::get(\Qii\Config\Consts::APP_DEFAULT_CONTROLLER_PREFIX) . '\\' . $controller;
            $action = preg_replace('/(Action)$/i', "", $action);
            $filePath = \Qii\Autoloader\Psr4::getInstance()->searchMappedFile($controllerCls);
            if (!is_file($filePath)) {
                if ($env == 'product') return '';
                \Qii\Autoloader\Import::requires(Qii_DIR . DS . 'Exceptions' . DS . 'Error.php');
                call_user_func_array(array('\Qii\Exceptions\Error', 'index'), array($controller, $action));
                die();
            } else {
                \Qii::getInstance()->request->setControllerName($controller);
                \Qii::getInstance()->request->setActionName($action);
                \Qii::getInstance()->dispatcher->setRequest(\Qii::getInstance()->request);
                \Qii::getInstance()->dispatcher->dispatch($controller, $action, new \Exception($msg ? $msg : self::getTraceAsString(), $code));
                die();
            }
            return;
        }
        return true;
    }
    
    /**
     * 显示错误信息
     * @param Array $message
     */
    public static function showError($message)
    {
        include(join(DS, array(Qii_DIR, 'Exceptions', 'view', 'error.php')));
    }
    
    /**
     * 显示错误信息
     * @param Array $message
     */
    public static function showMessage($message)
    {
        include(join(DS, array(Qii_DIR, 'Exceptions', 'view', 'message.php')));
    }
    
    public function __call($method, $args)
    {
        if (method_exists(self, $method)) return call_user_func_array(array('Qii\Exceptions\Error', $method), $args);
    }
}