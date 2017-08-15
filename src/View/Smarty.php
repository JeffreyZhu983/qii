<?php
/**
 * @author Jinhui.zhu    <jinhui.zhu@live.cn>
 * @version  $Id: smarty.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 *
 * 视图部分，提供供显示的方法，支持smarty
 *
 */
namespace Qii\View;

\Qii\Autoloader\Import::requires(Qii_DIR . DS . 'View' . DS . 'smarty' . DS . 'Autoloader.php');
\Smarty_Autoloader::register(true);
\Qii\Autoloader\Import::requires(Qii_DIR . DS . DS . 'View' . DS . 'smarty' . DS . 'SmartyBC.class.php');
\Qii\Autoloader\Import::requires(Qii_DIR . DS . DS . 'View' . DS . 'smarty' . DS . 'sysplugins' .DS. 'smartyexception.php');
\Qii\Autoloader\Import::requires(Qii_DIR . DS . DS . 'View' . DS . 'smarty' . DS . 'sysplugins' .DS. 'smartycompilerexception.php');

class Smarty extends \SmartyBC implements \Qii\View\Intf
{
	const VERSION = '1.2';
	public $caching = false;//是否缓存
	public $left_delimiter = '{#';//变量左边界
	public $right_delimiter = '#}';//变量右边界
	public $compile_check = true;//是否检查模板有变动
	public $debugging = false;//是否调试
	public $compile_dir = 'tmp/compile';//编译目录
	public $template_dir = 'view';//模板目录
	public $config_dir = 'configure';//配置文件目录
	public $plugins_dir = 'smarty/plugins/';//插件目录
	public $cache_dir = 'tmp/cache/';//缓存目录
	public $cache_id = '';//缓存文件ID
	public $cache_lifetime = 3600;//缓存时间
	public $allowTplExt = array('tpl', 'html', 'twig');//设置允许的文件后缀名，避免把PHP文件给输出出来了

	/**
	 * 用户直接输出这个实例化的类后会输出当前类的名称
	 *
	 * @return String
	 */
	public function __toString()
	{
		return get_class($this);
	}

	/**
	 * 设置块，可以将块放在页面上任意位置，块的开始，setEndBlock为结束，内容将会缓存到$this->_blocks中
	 *
	 * @param String $block
	 */
	public function setStartBlock($block)
	{
		$this->_blocks[$block] = '';
		ob_clean();
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
	public function getBlock($block)
	{
		return isset($this->_blocks[$block]) ? $this->_blocks[$block] : '';
	}

	/**
	 * 显示块里边的内容
	 *
	 * @param String $block
	 * @return null
	 */
	public function displayBlock($block)
	{
		echo $this->getBlock($block);
	}

	/**
	 * View构造函数
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$appConfigure = \Qii\Config\Register::getAppConfigure(\Qii\Config\Register::get(\Qii\Config\Consts::APP_INI_FILE));
		$viewInfo = $appConfigure['view']['smarty'];
		if (isset($viewInfo['ldelimiter']) && !empty($viewInfo['ldelimiter'])) $this->left_delimiter = $viewInfo['ldelimiter'];//变量左边界
		if (isset($viewInfo['rdelimiter']) && !empty($viewInfo['rdelimiter'])) $this->right_delimiter = $viewInfo['rdelimiter'];//变量右边界
		if (isset($viewInfo['path']) && !empty($viewInfo['path'])) $this->template_dir = \Qii\Autoloader\Psr4::realpath(\Qii\Autoloader\Psr4::getInstance()->getFolderByPrefix($viewInfo['path']));
		if (isset($viewInfo['compile']) && !empty($viewInfo['compile'])) $this->compile_dir = \Qii\Autoloader\Psr4::realpath(\Qii\Autoloader\Psr4::getInstance()->getFolderByPrefix($viewInfo['compile']));
		if (isset($viewInfo['cache']) && !empty($viewInfo['cache'])) $this->cache_dir = \Qii\Autoloader\Psr4::realpath(\Qii\Autoloader\Psr4::getInstance()->getFolderByPrefix($viewInfo['cache']));
		if (isset($viewInfo['lifetime']) && !empty($viewInfo['lifetime'])) $this->cache_lifetime = $viewInfo['lifetime'];
		//将老版本过度到新版本
		$this->setTemplateDir($this->template_dir)
			->setCompileDir($this->compile_dir)
			->setPluginsDir(SMARTY_PLUGINS_DIR)
			->setCacheDir($this->cache_dir)
			->setConfigDir($this->config_dir);
		$this->disableSecurity();
		$this->allow_php_templates = true;
	}
	 /**
     * fetches a rendered Smarty template
     *
     * @param  string $template   the resource handle of the template file or template object
     * @param  mixed  $cache_id   cache id to be used with this template
     * @param  mixed  $compile_id compile id to be used with this template
     * @param  object $parent     next higher level of Smarty variables
     *
     * @throws Exception
     * @throws SmartyException
     * @return string rendered template output
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
		$this->checkTplIsValid($template);
        return parent::fetch($template, $cache_id, $compile_id, $parent);
    }
	/**
	 * displays a Smarty template
	 *
	 * @param string $template the resource handle of the template file or template object
	 * @param mixed $cache_id cache id to be used with this template
	 * @param mixed $compile_id compile id to be used with this template
	 * @param object $parent next higher level of Smarty variables
	 */
	public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
	{
		$this->checkTplIsValid($template);
		if (!empty($this->_blocks)) {
			$this->assign($this->_blocks);
		}
		parent::display($template, $cache_id, $compile_id, $parent);
	}
	/**
	 * 设置模板存放路径
	 * @param string $template_dir 模板路径
	 * @param book $isConfig 是否配置
	 */
	/*
	public function setTemplateDir($template_dir, $isConfig = false)
	{
		return parent::setTemplateDir($template_dir, $isConfig = false);
	}*/
	/**
	 * 检查模板文件名称，只允许使用tpl
	 * @param string $template 模板文件
	 * @return bool | throw Exception
	 */
	protected function checkTplIsValid($template)
	{
		$extension = pathinfo($template, PATHINFO_EXTENSION);
		if(!in_array($extension, $this->allowTplExt))
		{
			throw new \Exception('模板文件不合法 : 模板不允许使用除'.join('、', $this->allowTplExt).'以外的后缀名你');
		}
		return true;
	}
}

?>