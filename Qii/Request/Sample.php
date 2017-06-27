<?php
namespace Qii\Request;

final class Simple extends Base
{
	/**
	 * __construct
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @param string $method
	 * @param array $params
	 */
	public function __construct ($module, $controller, $action, $method, $params = null)
	{
		if ($params && !is_array($params)) {
			unset($this);
			trigger_error('Expects the params is an array', E_USER_ERROR);
			return false;
		}

		if (is_string($method)) {
			$this->method = $method;
		} else {
			if (isset($_SERVER['REQUEST_METHOD'])) {
				$this->method = $_SERVER['REQUEST_METHOD'];
			} else {
				if (!strncasecmp(PHP_SAPI, 'cli', 3)) {
					$this->method = 'CLI';
				} else {
					$this->method = 'Unknown';
				}
			}
		}

		if ($module || $controller || $action) {
			if ($module && is_string($module)) {
				$this->module = $module;
			} else {
				$this->module = YAF_G('default_module');
			}

			if ($controller && is_string($controller)) {
				$this->controller = $controller;
			} else {
				$this->controller = YAF_G('default_controller');
			}

			if ($action && is_string($action)) {
				$this->action = $action;
			} else {
				$this->controller = YAF_G('default_action');
			}

			$this->routed = true;
		} else {
			$argv = $this->getServer('argv');
			if (is_array($argv)) {
				foreach($argv as $value) {
					if (is_string($value)) {
						if (strncasecmp($value, 'request_uri=', 12)) {
							continue;
						}
						$query = substr($value, 12);
						break;
					}
				}
			}

			if (empty($query)) {
				$this->uri = '';
			} else {
				$this->uri = $query;
			}
		}

		if ($params && is_array($params)) {
			$this->params = $params;
		} else {
			$this->params = array();
		}
        parent::__construct();
	}
	
	/**
	 * getQuery
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getQuery($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_GET;
		} elseif (isset($_GET[$name])) {
			return $_GET[$name];
		}
		return $default;
	}

	/**
	 * getRequest
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getRequest($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_REQUEST;
		} elseif (isset($_REQUEST[$name])) {
			return $_REQUEST[$name];
		}
		return $default;
	}

	/**
	 * getPost
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_POST;
		} elseif (isset($_POST[$name])) {
			return $_POST[$name];
		}
		return $default;
	}

	/**
	 * getCookie
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getCookie($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_COOKIE;
		} elseif (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return $default;
	}

	/**
	 * getFiles
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getFiles($name = null, $default = null)
	{
		if (is_null($name)) {
			return $_FILES;
		} elseif (isset($_FILES[$name])) {
			return $_FILES[$name];
		}
		return $default;
	}

	/**
	 * get [params -> post -> get -> cookie -> server]
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */	
	public function get($name, $default = null)
	{
		if (isset($this->params[$name])) {
			return $this->params[$name];
		} elseif (isset($_POST[$name])) {
			return $_POST[$name];
		} elseif (isset($_GET[$name])) {
			return $_GET[$name];
		} elseif (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		} elseif (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}
		return $default;
	}

	/**
	 * isXmlHttpRequest
	 *
	 * @param void
	 * @return boolean
	 */	
	public function isXmlHttpRequest()
	{
		$header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['X-Requested-With'] : '';
		if (is_string($header) && strncasecmp('XMLHttpRequest', $header, 14) == 0) {
			return true;
		}
		return false;
	}

	/**
	 * __clone
	 *
	 * @param void
	 */
	private function __clone()
	{
		
	}

}