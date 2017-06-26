<?php
namespace Qii\Language;
/**
 * 加载语言包
 * @Author Jinhui Zhu
 * @version 1.2
 *
 * Usage:
 *    Qii::Qii_Language_Loader('load', 'error', Qii_DIR); 加载系统目录中的语言
 *    Qii::Qii_Language_Loader('load', 'error'); 加载程序目录中的语言
 * OR
 *    Qii::Qii_Language_Loader()->load('error', Qii_DIR); 加载系统目录中的语言
 *    Qii::Qii_Language_Loader()->load('error'); 加载程序目录中的语言
 */
class Loader
{
	const VERSION = 1.3;
	/**
	 * @var $loaded ;
	 */
	private $loaded;

	protected static $_instance;

	public function __construct()
	{
		return $this;
	}
	/**
	 * 单例模式
	 */
	public static function getInstance()
	{

	    return \Qii\Autoloader\Factory::getInstance('\Qii\Language\Loader');
	}

	/**
	 * 加载系统语言包
	 * @param  string $package 语言包名称
	 * @param string $dir 语言包路径 默认为当前目录
	 * @return  Array 语言包内容
	 */
	public function load($package, $dir = '')
	{
		if(!$dir)
		{
			$dir = \Qii::getInstance()->getWorkspace() . DS;
		}
		else if ($dir == Qii_DIR)
		{
			$dir = Qii_DIR . DS . 'Language' . DS;
		}
		else
		{
			$dir = $dir . DS;
		}

		//先获取语言配置信息
		$language = \Qii\Autoloader\Import::includes($dir . 'i18n' . DS . 'language.php');
		//如果是cli模式就使用英文
		if(IS_CLI) $language = "EN";
		$fileName = $dir . 'i18n' . DS . $language . DS . $package . '.php';
		if (isset($this->loaded[$fileName])) return;
		$this->loaded[$fileName] = true;
		if (is_file($fileName)) {
			return $this->merge($fileName);
		}
		throw new \Qii\Exceptions\FileNotFound(\Qii::i(1405, $fileName), __LINE__);
	}

	/**
	 * 将语言包内容保存到系统Language中
	 * @param string $fileName 文件名
	 */
	protected function merge($fileName)
	{
		$data = \Qii\Config\Register::get(\Qii\Consts\Config::APP_LANGUAGE_CONFIG);
		if (!is_file($fileName)) throw new Exceptions(\Qii::i(1405, $fileName));
		$merge = (array) \Qii\Autoloader\Import::includes($fileName);
		
		if ($data) $merge = $data + $merge;
		\Qii\Config\Register::set(\Qii\Consts\Config::APP_LANGUAGE_CONFIG, $merge);
	}

	/**
	 * 获取语言内容
	 * @param Mix $code
	 * @return String
	 */
	public function get($code)
	{
		$data = \Qii\Config\Register::get(\Qii\Consts\Config::APP_LANGUAGE_CONFIG, array());
		if (isset($data) && isset($data[$code])) {
			return $data[$code];
		}
		return $code;
	}

	/**
	 * sprintf 格式化语言信息内容
	 * Qii::i(key, '格式化语言信息内容');
	 * @return String
	 */
	public function i()
	{
		$args = func_get_args();
		$message = array_shift($args);
		$message = $this->get($message);
		$vmessage = vsprintf($message, $args);

		if ($vmessage == $message && is_array($args) && count($args) > 0 && !(count($args) == 1 && $args[0] == '')) {
			return ' ['. $message .'] ['.join("\t", $args) . ']';
		}
		return $vmessage;
	}

	/**
	 * 获取语言内容，支持vsprintf
	 *
	 * @param String $words
	 * @param String $code
	 * @param argvs vsprintf的格式化参数
	 * @return String
	 */
	public function gettext($code, $argvs = null)
	{
		if ($argvs == null) return $this->get($code);
		return vsprintf($this->get($code), $argvs);
	}

}