<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: smarty.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 视图部分，提供供显示的方法，支持smarty
 * 
 */
Qii::requireOnce(Qii_DIR . DS . 'core'. DS . 'view' .DS. 'smarty' .DS. 'SmartyBC.class.php');
class View extends SmartyBC
{
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
	 * View构造函数
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$siteInfo = Qii::getSiteInfo();
		if(!empty($siteInfo['view']['ldelimiter'])) $this->left_delimiter = $siteInfo['view']['ldelimiter'];//变量左边界
		if(!empty($siteInfo['view']['rdelimiter'])) $this->right_delimiter = $siteInfo['view']['rdelimiter'];//变量右边界
		if(!empty($siteInfo['view']['path'])) $this->template_dir = $siteInfo['view']['path'];
		if(!empty($siteInfo['view']['compile'])) $this->compile_dir = $siteInfo['view']['compile'];
		if(!empty($siteInfo['view']['cache'])) $this->cache_dir = $siteInfo['view']['cache'];
		if(!empty($siteInfo['view']['lifetime'])) $this->cache_lifetime = $siteInfo['view']['lifetime'];
		//将老版本过度到新版本
        $this->setTemplateDir($this->template_dir)
            ->setCompileDir($this->compile_dir)
            ->setPluginsDir(SMARTY_PLUGINS_DIR)
            ->setCacheDir($this->cache_dir)
            ->setConfigDir($this->config_dir);
		$this->disableSecurity();
		$this->allow_php_templates = true;
	}
}
?>