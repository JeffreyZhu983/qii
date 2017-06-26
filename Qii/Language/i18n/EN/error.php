<?php
/**
 *
 * 400 Invalid syntax. 语法问题
 * 401 Access denied. 访问拒绝
 * 402 Payment required. 必须完整
 * 403 Request forbidden. 请求被禁止
 * 404 Object not found. 对象没有找到
 * 405 Method is not allowed. 方法不允许
 * 406 No response acceptable to client found. 客户端没有响应
 * 407 Proxy authentication required. 代理需要验证
 * 408 Server timed out waiting for request. 等等请求时服务器断开连接
 * 409 User should resubmit with more info. 有冲突用户应该进行检查
 * 410 Resource is no longer available. 资源不可用
 * 411 Server refused to accept request without a length. 服务器拒绝接受没有长度的请求
 * 412 Precondition given in request failed. 放弃请求失败的条件
 * 413 Request entity was too large. 请求太大
 * 414 Request Uniform Resource Identifier (URI) too long. 请求的URI 太长
 * 415 Unsupported media type. 不支持MEDIA类型
 * 449 Retry after doing the appropriate action. 在作了适当动作后重试
 * 500 Internal server error. 服务器内部错误
 * 501 Server does not support the functionality required to fulfill the request. 服务器不支持请求的功能
 * 502 Error response received from gateway. 从网关收到错误应答
 * 503 Temporarily overloaded. 过载
 * 504 Timed out waiting for gateway. 等待网关时请求断开
 * 505 HTTP version not supported. 不支持HTTP的版本
 */
return array(
	-1 => '%s',
	0 => 'Unknow error %d',
	//network
	400 => '400 Bad request.',
	401 => '401 Access denied.',
	403 => '403 Request forbidden.',
	404 => '404 Not found.',
	405 => '405 Method is not allowed.',
	406 => '406 No response acceptable to client found.',
	407 => '407 Proxy authentication required.',
	408 => '408 Server timed out waiting for request.',
	409 => '409 User should resubmit with more info.',
	410 => '410 Resource is no longer available. ',
	412 => '412 Precondition given in request failed.',
	413 => '413 Request entity was too large.',
	414 => '414 Request Uniform Resource Identifier (URI) too long.',
	415 => '415 Unsupported media type.',
	449 => '449 Retry after doing the appropriate action.',
	500 => '500 Internal server error ',
	501 => '501 Server does not support the functionality required to fulfill the request',
	502 => '502 Error response received from gateway',
	503 => '503 Temporarily overloaded.',
	504 => '504 Timed out waiting for gateway.',
	505 => '505 HTTP version not supported.',
	//system relate
	1000 => 'The file <font color="red">%s</font> format is wrong',
	1001 => 'Error page <font color="red">%s</font> does not exist',
	1002 => 'Security check failure',
	1003 => 'Undefined variable',
	1004 => 'The memcache extension must be loaded before use',
	1005 => 'Connect memcached server [%s:%s] failed',
	1006 => 'The redis extension must be loaded before use',
	1007 => 'Undefined cache policy',
	1008 => 'The %s extension must be loaded before use',
    1009 => 'Folder "%s" does not exist',
	//class relate
	1100 => 'Two many argements in %s , this method need %d parameter, %d given',
	1101 => 'Call undefined method <font color="red">%s</font>',
	1102 => 'Class <font color="red">%s</font> does not exist',
	1103 => 'Class name couldn\'t be NULL',
	1104 => 'Class <font color="red">%s</font> didn\'t instance',
	1105 => 'Call undefined method <font color="red">%s::%s</font>" with args "<font color="red">%s</font>"',
	1107 => 'Class %s must be the extends/implements of %s',
	1108 => 'Please write this controller<font color="red">%s</font> controller in your project, and add this method <font color="red">%s</font>',
	1109 => 'Please write this <font color="red">%s</font> class in your project, and add this method <font color="red">%s</font>',
	//file and others
	1400 => 'Website configure file <font color="red">%s</font> error',
	1401 => 'Dir %s does not exist',
	1402 => 'No database configure file',
	1403 => 'No database configure',
	1404 => 'Directory <font color="red">%s</font> not included file <font color="red">%s</font>',
	1405 => 'File %s not found',
	1406 => 'Configure file is empty',
	//model
	1500 => 'Connect Database fail, host:%s, use user:%s, password:%s, database:%s error : %s',
	1501 => 'Connect Database fail, %s',
	1506 => 'Model\'s method %s does not exist',
	1507 => 'Please call %s method first',
	1508 => 'Table must have some fields',
	1509 => 'Execute Query error with sql : <font color="red">%s</font>, Error description: <font color="red">%s</font>',
	1510 => 'unknow table name',
	1511 => '%s does exist',
	1512 => '%s does not exist',
	1513 => 'private key is not set',

	5001 => '%s undefined',
	5002 => '%s not allowed',
	5003 => '%s is null',
	5004 => '%s is invalid',
	5005 => '%s regular does not exist'
);