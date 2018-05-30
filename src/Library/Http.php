<?php
namespace Qii\Library;
/**
 * A parallel HTTP client written in pure PHP
 *
 * This file just for non-composer user, require this file directly.
 *
 * @author hightman <hightman@twomice.net>
 * @link http://hightman.cn
 * @copyright Copyright (c) 2015 Twomice Studio.
 */
\Qii\Autoloader\Psr4::getInstance()
    ->setUseNamespaces([
        ['hightman\http', true],
    ])
    ->addNamespaces([
        ['hightman\http', Qii_DIR . DS .'Library'. DS . 'Third'. DS .'hightman'],
    ]);
use hightman\http\Client;
use hightman\http\Request;
use hightman\http\Response;

class Http extends Client
{
	
}
