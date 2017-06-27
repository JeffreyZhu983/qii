<?php
namespace Qii\Route\Parse;

/**
 * Route规则 支持正则表达式
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-10-24 23:11
 * @version 1.2
 */

class Regx
{
	const VERSION = '1.2';
	private $config;

	public function __construct()
	{

	}

	public function setConfig($config)
	{
		$this->config = $config;
	}

	/**
	 * 匹配路由
	 * @param String $controller 控制器名称
	 * @param String $action 动作名称
	 *
	 * 支持规则：controller/(:any) controller/(:num) controller/(.*) 等一系列正则规则
	 */
	public function parse($controller, $action)
	{
		$uri = $controller . '/' . $action;
		foreach ($this->config AS $key => $val) {
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
			// Does the RegEx match?
			if (preg_match('#^' . $key . '$#', $uri)) {
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE) {
					$val = preg_replace('#^' . $key . '$#', $val, $uri);
				}
				$router = explode('/', $val);
				return array('controller' => $router[0], 'action' => $router[1]);
			}
		}
		return array('controller' => $controller, 'action' => $action);
	}
}

?>