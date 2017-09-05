<?php
function getRequest()
{
    return \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Request\Http');
}
/**
 * 生成link
 * @param string $url
 */
function _link($url)
{
	return getRequest()->url::getAbsluteUrl($url);
}