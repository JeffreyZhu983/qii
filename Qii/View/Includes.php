<?php
/**
 * @author Jinhui.zhu    <jinhui.zhu@live.cn>
 * @version  $Id: include.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 *
 * 视图部分，提供供显示的方法，支持include
 *
 */
namespace Qii\View;

class Includes implements \Qii\View\Intf
{
	const VERSION = '1.2';
	public $data;
	private $viewPath;
	private $_blocks = array();

	public function __construct()
	{
		$appConfigure = \Qii\Config\Register::getAppConfigure(\Qii\Config\Register::get(\Qii\Consts::APP_INI_FILE));
		$this->viewPath = $appConfigure['view']['path'];
	}

	/**
	 * Assign
	 *
	 * @param Mix $name
	 * @param Mix $val
	 */
	public function assign($name, $val = null)
	{
		if (isset($val)) {
			$this->data[$name] = $val;
		} else if (is_array($name)) {
			foreach ($name AS $k => $v) {
				$this->data[$k] = $v;
			}
		}
	}

	/**
	 * 设置块，可以将块放在页面上任意位置，块的开始，setEndBlock为结束，内容将会缓存到$this->_blocks中
	 *
	 * @param String $block
	 */
	public function setStartBlock($block)
	{
		$this->_blocks[$block] = '';
		ob_start();
	}

	/**
	 * 设置块，此处是结束
	 *
	 * @param String $block
	 */
	public function setEndBlock($block)
	{
		$content = ob_get_contents();
		ob_end_clean();
		$this->_blocks[$block] = $content;
	}

	/**
	 * 返回块里边的内容
	 *
	 * @param String $block
	 * @return String
	 */
	public function displayBlock($block)
	{
		return isset($this->_blocks[$block]) ? $this->_blocks[$block] : '';
	}

	/**
	 * 载入数据和模板
	 *
	 * @param String $tpl
	 */
	public function display($tpl)
	{
		$tpl = $this->viewPath . DS . $tpl;
		extract((array)$this->data);
		if (!\Qii::setError(is_file($tpl), 1405, $tpl . ' does not exist')) {
			include($tpl);
		}
	}
}

?>