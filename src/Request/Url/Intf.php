<?php
namespace Qii\Request\Url;

interface Intf
{
	/**
	 * 根据给定的参数创建URL
	 * @param Array $array {controller:controllerName, action : actionName}
	 * @param string $fileName
	 * @param string $extenstion
	 * @param string $trimExtension
	 * @return string
	 */
	public function bulidURI($params, $fileName = '', $extenstion = '', $trimExtension = false);
	/**
	 * 匹配参数
	 */
	public function parseArgs($params);
	/**
	 * 获取参数对应的值
	 */
	public function decodeArgs($params, $k = '');
}