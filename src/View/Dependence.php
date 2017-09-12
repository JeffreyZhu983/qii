<?php
/**
 * 模板里边添加依赖文件
 * 
 */
namespace Qii\View;

class Dependence
{
	public static $dependence = array();
	public static $blockCss = array();
	public static $blockHTML = array();
	public static $blockJS = array();

	public static $assignBlocks = array();

	public function __construct(){
	}
	public static function getAssignBlock($blockName)
	{
		if (isset(self::$assignBlocks[$blockName])) {
			return self::$assignBlocks[$blockName];
		}
		return;
	}

	public static function addCssBlock()
	{
		ob_start();
	}

	public static function addCssBlockEnd()
	{
		$block = ob_get_contents();
		ob_end_clean();
		self::$blockCss[] = $block;
	}

	public static function addHtmlBlock()
	{
		ob_start();
	}

	public static function addHtmlBlockEnd($assignBlock = null)
	{
		$block = ob_get_contents();
		ob_end_clean();
		if ($assignBlock) {
			self::$assignBlocks[$assignBlock] = $block;
			return;
		}
		self::$blockHTML[] = $block;
	}

	public static function addJsBlock()
	{
		ob_start();
	}

	public static function addJsBlockEnd()
	{
		$block = ob_get_contents();
		ob_end_clean();
		self::$blockJS[] = $block;
	}

	/**
	 * 设置js文件依赖
	 * @param string $file js文件路径
	 */
	public static function setDependenceJS($file)
	{
		if (is_array($file)) {
			foreach ($file AS $f) {
				self::setDependenceJS($f);
			}
		} else {
			self::$dependence['blockLinkJs'][] = $file;
		}
	}

	/**
	 * 设置js方法块依赖
	 * @param string $file js文件路径
	 */
	public static function setDependenceBlockJS($file)
	{
		if (is_array($file)) {
			foreach ($file AS $f) {
				self::setDependenceBlockJS($f);
			}
		} else {
			self::$dependence['blockJs'][] = $file;
		}
	}

	/**
	 * 设置css文件依赖
	 * @param string $file css文件路径
	 */
	public static function setDependenceCss($file)
	{
		if (is_array($file)) {
			foreach ($file AS $f) {
				self::setDependenceCss($f);
			}
		} else {
			self::$dependence['blockLinkCss'][] = $file;
		}
	}

	/**
	 * 设置css方法块依赖
	 * @param string $file css文件路径
	 */
	public static function setDependenceBlockCss($file)
	{
		if (is_array($file)) {
			foreach ($file AS $f) {
				self::setDependenceBlockCss($f);
			}
		} else {
			self::$dependence['blockCss'][] = $file;
		}
	}
}