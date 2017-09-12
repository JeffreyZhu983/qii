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

_require(array(
		 __DIR__ . '/Third/hightman/ParseInterface.php',
		__DIR__ . '/Third/hightman/HeaderTrait.php',
		__DIR__ . '/Third/hightman/Client.php',
		__DIR__ . '/Third/hightman/Connection.php',
		__DIR__ . '/Third/hightman/Response.php',
		__DIR__ . '/Third/hightman/Request.php',
		__DIR__ . '/Third/hightman/Processor.php'
	)
);
use hightman\http\Client;
use hightman\http\Request;
use hightman\http\Response;
class Http extends Client
{
	
}
