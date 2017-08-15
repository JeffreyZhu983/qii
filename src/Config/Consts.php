<?php
namespace Qii\Config;

/**
 * App系统配置
 * @author Jinhui.Zhu <jinhui.zhu@live.cn> 2015-09-21 11:31
 *
 */
class Consts
{
	const VERSION = '1.2';
	const APP_SYS = 'Qii';//框架名，用户保存框架实例化对象
	const APP_ENVIRONS = 'QII_ENVIRONS';//配置文件使用环境
	const APP_ENVIRON = 'QII_ENVIRON';//当前系统使用的环境
	const APP_DEFAULT_ENVIRON = 'product';//默认系统环境
	const APP_DB = 'QII_APP_DB';//数据库配置
	const APP_CONFIGURE = 'AppConfigure';//系统配置，不能修改为其他值，默认会调用此方法
	const APP_LOADED_FILE = 'QII_APP_LOADED_FILE';//加载的文件列表
	const APP_INI_FILE = 'QII_APP_INI_FILE';//网站配置文件列表
	const APP_INCLUDES_FILE = 'QII_APP_INCLUDES_FILE';//保存includes过的文件列表
	const APP_SITE_METHOD = 'rewriteMethod';//网站路由
	const APP_SITE_ROUTER = 'QII_APP_SITE_ROUTER';//网站路由
	const APP_DISPATCHER = 'Qii_Dispatcher';
	const APP_SYS_DISPATCHER = 'QII_APP_SYS_DISPATCHER';
	const APP_LOAD_PREFIX = 'Qii_Load_';//不能随意修改
	const APP_LOAD_SYS_PREFIX = 'Qii_Load_Qii_';//不能随意修改
	const APP_CACHE_PATH = 'QII_CACHE_PATH';//App缓存目录
	const APP_CACHE_SYSTEM_PATH = 'QII_CACHE_SYSTEM_PATH';//框架缓存目录
	const APP_INI = 'app.ini';//网站配置文件
	const APP_LANGUAGE_CONFIG = 'QII_LANGUAGE_CONFIG';//语言包
	const APP_OUTPUT_CHARSET = 'utf-8';
	const APP_VIEW = 'QII_VIEW';//controller->_view
	const APP_DEFAULT_CONTROLLER = 'APP_DEFAULT_CONTROLLER';//默认controller
	const APP_DEFAULT_CONTROLLER_PREFIX = 'APP_DEFAULT_CONTROLLER_PREFIX';//默认controller
	const APP_DEFAULT_ACTION = 'APP_DEFAULT_ACTION';//默认Action
	const APP_DEFAULT_ACTION_SUFFIX = 'APP_DEFAULT_ACTION_SUFFIX';//默认Action后缀
}
