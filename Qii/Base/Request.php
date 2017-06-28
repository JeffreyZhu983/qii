<?php
namespace Qii\Base;

abstract class Request
{
    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';
    public $host = '';
    public $module;
    public $controller;
    public $action;
    public $method;

    public $dispatcher;


    protected $params;

    protected $language;

    protected $_exception;

    protected $_baseUri = '';

    protected $uri = '';

    protected $dispatched = false;

    protected $routed = false;

    protected $forward = false;
    /**
     * @var Qii_Request_Url url
     */
    public $url;
    /**
     * 初始化参数，获取链接中对应的参数并存放到$this->params中
     *
     */
    public function __construct()
    {
        foreach ($_GET AS $key => $val) {
            $this->setParam($key, $val);
        }
        $rewriteRule = \Qii::getInstance()->appConfigure(\Qii\Config\Consts::APP_SITE_METHOD);
        $this->url = new \Qii\Request\Url($rewriteRule);
        $this->host = $_SERVER['HTTP_HOST'];
        $params = (array)$this->url->getParams();
        if(count($params) > 0) $this->params = array_merge($this->params, $params);
        //处理url中的数据
        if(ucwords($rewriteRule) == 'Short'){
            $this->setControllerName(
                    isset($this->params[0]) && $this->params[0] != '' ?
                        $this->params[0] :
                        $this->defaultController());
            $this->setActionName(
                    isset($this->params[1]) && $this->params[1] != '' ?
                        $this->params[1] :
                        $this->defaultAction());
        }
        return $this;
    }
    /**
     * 获取POST数据
     */
    public function post($name, $default = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }
    /**
     * 默认controller
     */
    public function defaultController()
    {
        return \Qii\Config\Register::get(\Qii\Config\Consts::APP_DEFAULT_CONTROLLER, 'index');
    }
    /**
     * 默认action
     */
    public function defaultAction()
    {
        return \Qii\Config\Register::get(\Qii\Config\Consts::APP_DEFAULT_ACTION, 'index');
    }

    /**
     * 获取当前数据处理
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * isGet
     *
     * @param void
     * @return boolean
     */
    public function isGet()
    {
        return (strtoupper($this->method) == 'GET');
    }

    /**
     * isPost
     *
     * @param void
     * @return boolean
     */
    public function isPost()
    {
        return (strtoupper($this->method) == 'POST');
    }

    /**
     * isPut
     *
     * @param void
     * @return boolean
     */
    public function isPut()
    {
        return (strtoupper($this->method) == 'PUT');
    }

    /**
     * isHead
     *
     * @param void
     * @return boolean
     */
    public function isHead()
    {
        return (strtoupper($this->method) == 'HEAD');
    }

    /**
     * isOptions
     *
     * @param void
     * @return boolean
     */
    public function isOptions()
    {
        return (strtoupper($this->method) == 'OPTIONS');
    }

    /**
     * isCli
     *
     * @param void
     * @return boolean
     */
    public function isCli()
    {
        return (strtoupper($this->method) == 'CLI');
    }

    /**
     * isXmlHttpRequest
     *
     * @param void
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return false;
    }

    /**
     * getServer
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getServer($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_SERVER;
        } elseif (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        return $default;
    }

    /**
     * getEnv
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getEnv($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_ENV;
        } elseif (isset($_ENV[$name])) {
            return $_ENV[$name];
        }
        return $default;
    }

    /**
     * setParam
     *
     * @param mixed $name
     * @param mixed $value
     * @return boolean | Qii_Request_Abstract
     */
    public function setParam($name, $value = null)
    {
        if (is_null($value)) {
            if (is_array($name)) {
                $this->params = array_merge($this->params, $name);
                return $this;
            }
        } elseif (is_string($name)) {
            $this->params[$name] = $value;
            return $this;
        }
        return false;
    }

    /**
     * getParam
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $dafault = null)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return $dafault;
    }

    /**
     * setParams
     *
     * @param array
     * @return boolean | Qii_Request_Abstract
     */
    public function setParams($params)
    {
        if (is_array($params)) {
            $this->params = $params;
            return $this;
        }
        return false;
    }

    /**
     * getParams
     *
     * @param void
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * setException
     *
     * @param Exception $exception
     * @return boolean | Qii_Request_Abstract
     */
    public function setException($exception)
    {
        if (is_object($exception)
            && ($exception instanceof Exception)
        ) {
            $this->_exception = $exception;
            return $this;
        }
        return false;
    }

    /**
     * getException
     *
     * @param void
     * @return Exception
     */
    public function getException()
    {
        if (is_object($this->_exception)
            && ($this->_exception instanceof Exception)
        ) {
            return $this->_exception;
        }
        return null;
    }


    /**
     * getModuleName
     *
     * @param void
     * @return string
     */
    public function getModuleName()
    {
        return $this->module;
    }

    /**
     * getControllerName
     *
     * @param void
     * @return string
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * getActionName
     *
     * @param void
     * @return string
     */
    public function getActionName()
    {
        return $this->action;
    }

    /**
     * setModuleName
     *
     * @param string $name
     * @return boolean | Qii_Request_Abstract
     */
    public function setModuleName($name)
    {
        if (!is_string($name)) {
            trigger_error('Expect a string module name', E_USER_WARNING);
            return false;
        }
        $this->module = $name;
        return $this;
    }

    /**
     * setControllerName
     *
     * @param string $name
     * @return boolean | Qii_Request_Abstract
     */
    public function setControllerName($name)
    {
        if (!is_string($name)) {
            trigger_error('Expect a string controller name', E_USER_WARNING);
            return $this;
        }
        $this->controller = $name;
        return $this;
    }

    /**
     * setActionName
     *
     * @param string $name
     * @return boolean | Qii_Request_Abstract
     */
    public function setActionName($name)
    {
        if (!is_string($name)) {
            trigger_error('Expect a string action name', E_USER_WARNING);
            return false;
        }
        $this->action = $name;
        return $this;
    }

    /**
     * getMethod
     *
     * @param void
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * getLanguage
     *
     * @param void
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * setBaseUri
     *
     * @param string $baseUri
     * @return boolean | Qii_Request_Abstract
     */
    public function setBaseUri($baseUri)
    {
        if ($baseUri && is_string($baseUri)) {
            $this->_baseUri = $baseUri;
            return $this;
        }
        return false;
    }

    /**
     * getBaseUri
     *
     * @param void
     * @return string
     */
    public function getBaseUri()
    {
        return $this->_baseUri;
    }

    /**
     * setRequestUri
     *
     * @param string $uri
     * @return boolean | Qii_Request_Abstract
     */
    public function setRequestUri($uri)
    {
        if (is_string($uri)) {
            $this->uri = $uri;
            return $this;
        }
        return false;
    }

    /**
     * getRequestUri
     *
     * @param void
     * @return string
     */
    public function getRequestUri()
    {
        return $this->uri;
    }

    /**
     * isDispatched
     *
     * @param void
     * @return boolean
     */
    public function isDispatched()
    {
        return (boolean)$this->dispatched;
    }

    /**
     * setDispatched
     *
     * @param boolean $flag
     * @return boolean | Qii_Request_Abstract
     */
    public function setDispatched($flag = true)
    {
        if (is_bool($flag)) {
            $this->dispatched = $flag;
            return $this;
        }
        return false;
    }

    /**
     * 设置dispatcher
     *
     * @param Qii_Controller_Dispatcher $dispatcher
     */
    public function setDispatcher(\Qii\Base\Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * isRouted
     *
     * @param void
     * @return boolean
     */
    public function isRouted()
    {
        return $this->routed;
    }

    /**
     * setRouted
     *
     * @param boolean $flag
     * @return boolean | Qii_Request_Abstract
     */
    public function setRouted($flag = true)
    {
        if (is_bool($flag)) {
            $this->routed = $flag;
            return $this;
        }
        return false;
    }
    /**
     * 是否需要转发
     */
    public function isForward()
    {
        return $this->forward;
    }
    /**
     * 设置是否需要转发
     * @param bool $flag
     * @return $this
     */
    public function setForward($flag = true)
    {
        if (is_bool($flag)) {
            $this->forward = $flag;
            return $this;
        }
        return $this;
    }

    /**
     * __setbaseUri
     *
     * @param string $baseUri
     * @param string $request_uri
     * @return boolean
     */
    protected function _setbaseUri($baseUri, $request_uri = null)
    {
        if ($baseUri && is_string($baseUri)) {
            $this->_baseUri = $baseUri;

            return true;
        } elseif ($request_uri && is_string($request_uri)) {
            $scriptFileName = $this->getServer('SCRIPT_FILENAME');

            do {
                if ($scriptFileName && is_string($scriptFileName)) {
                    $fileName = basename($scriptFileName, \Qii::getInstance()->appConfigure('ext', '.php'));
                    $fileNameLen = strlen($fileName);

                    $script_name = $this->getServer('SCRIPT_NAME');
                    if ($script_name && is_string($script_name)) {
                        $script = basename($script_name);

                        if (strncmp($fileName, $script, $fileNameLen) == 0) {
                            $basename = $script_name;
                            break;
                        }
                    }

                    $phpself_name = $this->getServer('PHP_SELF');
                    if ($phpself_name && is_string($phpself_name)) {
                        $phpself = basename($phpself_name);
                        if (strncmp($fileName, $phpself, $fileNameLen) == 0) {
                            $basename = $phpself_name;
                            break;
                        }
                    }

                    $orig_name = $this->getServer('ORIG_SCRIPT_NAME');
                    if ($orig_name && is_string($orig_name)) {
                        $orig = basename($orig_name);
                        if (strncmp($fileName, $orig, $fileNameLen) == 0) {
                            $basename = $orig_name;
                            break;
                        }
                    }
                }
            } while (0);

            if ($basename && strstr($request_uri, $basename) == $request_uri) {
                $this->_baseUri = rtrim($basename, '/');

                return true;
            } elseif ($basename) {
                $dirname = rtrim(dirname($basename), '/');
                if ($dirname) {
                    if (strstr($request_uri, $dirname) == $request_uri) {
                        $this->_baseUri = $dirname;

                        return true;
                    }
                }
            }

            $this->_baseUri = '';

            return true;
        }

        return false;
    }
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->url, $method), $args);
    }
}