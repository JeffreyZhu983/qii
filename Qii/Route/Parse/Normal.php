<?php
namespace Qii\Route\Parse;

/**
 * Route规则文件
 * 兼容以前版本的匹配规则
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-10-24 23:11
 * @version 1.2
 */

class Normal
{
	const VERSION = '1.2';
	private $config;

	public function __construct()
	{

	}

	/**
	 * 设置路由规则
	 * @param Array $config 路由规则
	 */
	public function setConfig($config)
	{
		$this->config = $config;
	}

	/**
	 * 路由转发， 转发对应的规则中xx不能为*
	 *
	 * @param String $controller
	 * @param String $action
	 * @return Array ($controller, $action);
	 *
	 * *:* => *:yyy 所有controller和action都转发到 *->yyy
	 * *:* => yy:* 所有转发到xxx->*, 这里的*，前边对应的是什么，后边就对应转发到什么，比如: *:xxx => yy:yyy
	 * xx:* => yy:* xx中对应的方法转发到yy对应的方法
	 * xx:* => yy:yyy xxx Controller转发到 yy->yyy
	 * *:xxx => yy:yyy 所有Controller转发到 yy->yyy
	 * xxx:*(yy):第三个参数 => {1}:* 转发xxx:yy => yy:第三个参数
	 */
	public function parse($controller, $action, $thirdParam = '')
	{
		if (!$this->config) {
			return array('controller' => $controller, 'action' => $action);
		}
		$routerArray = array();
		if (is_array($this->config)) {
			foreach ($this->config AS $key => $value) {
				$keyArray = explode(":", $key);
				$valueArray = explode(":", $value);
				if (!isset($keyArray[1])) $keyArray[1] = '';
				if (!isset($valueArray[1])) $valueArray[1] = '';
				if ('' == $keyArray[1]) {
					$keyArray[1] = "*";
				}
				$routerArray['controller'][$keyArray[0] . ":" . $keyArray[1]] = $valueArray[0];
				if ($valueArray[1] == '*') $valueArray[1] = $action;
				if ($keyArray[1] == "*") {
					$routerArray['action'][$keyArray[0] . ":" . $keyArray[1]] = $valueArray[1];
				} else {
					$routerArray['action'][$keyArray[0] . ":" . $keyArray[1]] = $valueArray[1];
				}
			}
		}
		if (count($routerArray) == 0) {
			return array('controller' => $controller, 'action' => $action);
		}
		if (isset($routerArray["controller"]["*:*"]) && '' != $routerArray["controller"]["*:*"])//*:*=>yyy:* or *:* => *:yyy mode
		{
			$controller = ($routerArray['controller']['*:*'] == '*' ? $controller : $routerArray["controller"]["*:*"]);
			$action = ($routerArray['action']['*:*'] == '*' ? $action : $routerArray['action']['*:*']);
		} elseif (isset($routerArray["action"][$controller . ":*"]) && '' != $routerArray["action"][$controller . ":*"])//xx:*=>yy:* mode
		{
			$action = $routerArray['action'][$controller . ":*"];
			$controller = $routerArray["controller"][$controller . ":*"];
			if (stristr($controller, '{1}')) {
				$controller = str_replace('{1}', $action, $controller);
				$action = $thirdParam ? $thirdParam : 'index';
			}
		} elseif (isset($routerArray["action"]["*:" . $action]) && '' != $routerArray["action"]["*:" . $action])//*:xxx=> yy:yyy mode
		{
			$controller = $routerArray["control"]["*:" . $action];
			$action = $routerArray["action"]["*:" . $action];
		} elseif (isset($routerArray["controller"][$controller . ":" . $action])) {
			$tmpAction = $controller . ":" . $action;
			$action = $routerArray["action"][$controller . ":" . $action];
			$controller = $routerArray["controller"][$tmpAction];
			if (stristr($action, '{1}')) {
				$action = str_replace('{1}', $action, $thirdParam);
			}
		}
		$action = !$action ? 'index' : $action;
		return array('controller' => $controller, 'action' => $action);
	}
}