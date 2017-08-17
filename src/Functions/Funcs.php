<?php
/**
 * Qii ...
 * @return null|Qii|Qii\Autoloader\Psr4
 */
function _Qii()
{
	return \Qii::getInstance();
}

/**
 * \Qii::i(.., ...)
 * @return mixed
 */
function _i()
{
    return call_user_func_array('\Qii::i', func_get_args());
}
/**
 * throw new Exception
 */
function _e()
{
    return call_user_func_array('\Qii::e', func_get_args());
}
/**
 * 加载语言包
 * @param string $language 语言包
 */
function _language($language)
{
	\Qii\Language\Loader::getInstance()->load($language);
}

/**
 * \Qii_Config_Register:: get or set
 * @param $key
 * @param null $val
 * @return Mix|void
 */
function _config($key, $val = null)
{
	if($val === null)
	{
		return \Qii_Config_Register::get($key);
	}
	return \Qii_Config_Register::set($key, $val);
}
/**
 * Adds a base directory for a namespace prefix.
 *
 * @param string $prefix The namespace prefix.
 * @param string $baseDir A base directory for class files in the
 * namespace.
 * @param bool $prepend If true, prepend the base directory to the stack
 * instead of appending it; this causes it to be searched first rather
 * than last.
 * @return void
 */
function _addNamespace($prefix, $baseDir, $prepend = false)
{
	_qii()->addNamespace($prefix, $baseDir, $prepend);
}
/**
 * 加载loader 可以直接加载指定类
 */
function _loader($class = null)
{
	$args = func_get_args();
	if($class != null){
		return call_user_func_array(array(\Qii\Autoloader\Psr4::getInstance(), 'loadClass'), $args);
	}
	return \Qii\Autoloader\Psr4::getInstance();
}
/**
 * 简便的loadClass方法
 * \Qii\Autoloader\Psr4::getInstance()->loadClass(.., ..);
 */
function _loadClass()
{
	$args = func_get_args();
	return call_user_func_array(array(\_loader(), 'loadClass'), $args);
}

/**
 * 根据文件前缀获取文件路径
 *
 * @param string $file 文件名
 */
function _getFileByPrefix($file)
{
	return \_loader()->getFileByPrefix($file);
}

/**
 * 数据库操作类
 *
 * @param Qii_Driver_Rules $rule 规则
 * @param array|null $privateKey 主键
 * @param array|null $fieldsVal 值
 * @return mixed
 */
function _DBDriver(\Qii_Driver_Rules $rule, $privateKey = null, $fieldsVal = null)
{
    $rules = _loadClass('Qii\Driver\Easy')->_initialize();
    if ($privateKey) $rules->setPrivateKey($privateKey);
    $rules->setRules($rule);
    if ($fieldsVal) $rules->setFieldsVal($fieldsVal);
    return $rules;
}
/**
 * _include include文件
 */
function _include($files){
	return \Qii\Autoloader\Import::includes($files);
}

function _require($files)
{
	return \Qii\Autoloader\Import::requires($files);
}

