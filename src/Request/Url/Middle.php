<?php
namespace Qii\Request\Url;

class Middle extends Base implements Intf
{
	public function __construct($mode)
	{
		parent::__construct($mode);
	}
	/**
	 * 解析uri获取参数 => 值
	 * @param string $params uri
	 * @return array
	 */
	public function parseArgs($params)
	{
		$this->checkMode($this->_mode);
		if (empty($params)) return;
		$argvArray = explode("/", $params);
		$data = array();
		if (is_array($argvArray)) {
			foreach ($argvArray AS $arg) {
				$args = explode("/", $arg);
				$data[$args[0]] = $args[1];
			}
		}
		foreach ($_GET AS $key => $val) {
			$data[$key] = $val;
		}
		return $data;
	}
	/**
	 * 获取指定参数的值
	 * @param array $urlArray 参数集合
	 * @param string $k 指定参数
	 */
	public function decodeArgs($urlArray, $k = '')
	{
		$this->checkMode($this->_mode);
		$urlArraySize = sizeof($urlArray);
		for ($i = 0; $i < $urlArraySize; $i = $i + 2) {
			if ($urlArray[$i + 1] == 'NULL') {
				continue;
			}
			$url[$urlArray[$i]] = $urlArray[$i + 1];
		}
		return $url;
	}
}