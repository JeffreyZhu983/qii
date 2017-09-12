<?php
namespace Qii\Request\Url;

class Short extends Base implements Intf
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
		if ($params == '') return;
		$argvArray = explode("/", $params);
		$data = array();
		if (is_array($argvArray)) {
			foreach ($argvArray AS $arg) {
				$data[] = $arg;
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
		if (!empty($k)) return $urlArray[$k] == 'NULL' ? '' : $urlArray[$k];
		return $urlArray;
	}
}