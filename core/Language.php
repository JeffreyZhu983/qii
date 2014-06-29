<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Logger.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 语言类
 * 
 */
if(class_exists('Language'))
{
	return;
}
final class Language
{
	public $version = '1.1.0';
	private $data = array();
	
	/**
	 * 获取语言
	 *
	 * @return String Language
	 */
	public function getLanguage()
	{
		$languageConfigure = Qii::loadFile('i18n' . DS . 'language.php');
		$language = array_pop($languageConfigure);
		return $language;
	}
	public function getData()
	{
		return $this->data;
	}
	/**
	 * 加载默认语言， 多次指定语言包，可以覆盖之前指定的语言包或清除之前语言包，默认是覆盖
	 *
	 * @param String $fileName
	 * @param String $extention 扩展名
	 */
	public function loadDefault($fileName, $extention = '.php', $clear = false)
	{
		$defaultLanguage = 'i18n'. DS . $this->getLanguage() . DS . $fileName;
		if(substr($fileName, -(strlen($extention))) != $extention)
		{
			$defaultLanguage .= $extention;
		}
		if(is_file($defaultLanguage) && is_readable($defaultLanguage))
		{
			if($clear)//清除之前语言包
			{
				$this->data = array();
			}
			$this->load($defaultLanguage);
		}
	}
	/**
	 * 加载语言
	 *
	 * @param String $fileName
	 */
	public function load($fileName, $key = null)
	{
		if(is_array($this->data))
		{
			$this->data = $this->data + Qii::loadFile($fileName, $key);//将两个数组合并起来，array_merge会将数字键重新排序，所以这里用+号
		}
		else
		{
			$this->data = Qii::loadFile($fileName, $key);
		}
	}
	/**
	 * 获取语言内容，支持vsprintf
	 *
	 * @param String $words
	 * @param code $code
	 * @param argvs vsprintf的格式化参数
	 * @return String
	 */
	public function gettext($code, $argvs)
	{
		$data = (array) $this->data;
		if(isset($data)  && isset($data[$code]))
		{
			return vsprintf($data[$code], $argvs);
		}
		return vsprintf($code, $argvs);
		//if(!isset($data[0])) $data[0] = '';
		//return $data[0];
	}
}
?>