<?php 
 return array (
  'namespace' => 
  array (
    'use' => '1',
    'list' => 
    array (
      'Controller' => '1',
      'Model' => '1',
      'Library' => '1',
      'Action' => '1',
    ),
  ),
  'rewriteMethod' => 'Short',
  'rewriteRules' => 'Normal',
  'admin' => 
  array (
    'user' => 'admin',
    'password' => '119328118',
  ),
  'debug' => '1',
  'errorPage' => 'Error:Index',
  'timezone' => 'Asia/Shanghai',
  'contentType' => 'text/html',
  'charset' => 'UTF-8',
  'view' => 
  array (
    'engine' => 'smarty',
    'path' => 'view',
    'smarty' => 
    array (
      'view' => 'view',
      'path' => 'view',
      'ldelimiter' => '{#',
      'rdelimiter' => '#}',
      'compile' => 'tmp/compile',
      'cache' => 'tmp/cache',
      'lifetime' => '300',
    ),
  ),
  'cache' => 'memcache',
  'security' => 
  array (
    'enable' => '1',
    'name' => 'security_sid',
    'expired' => '3600',
    'key' => '4cd780a986d5c30e03bdcb67d16c8320',
  ),
  'memcache' => 
  array (
    'servers' => '127.0.0.1',
    'ports' => '11211',
  ),
  'xpath' => 
  array (
    0 => 'controller',
    1 => 'model',
    2 => 'class',
    3 => 'plugin',
  ),
  'query' => 
  array (
    0 => 'controller',
    1 => 'action',
    2 => 'param',
  ),
  'controller' => 
  array (
    'name' => 'controller',
    'prefix' => 'Controller',
    'default' => 'index',
  ),
  'action' => 
  array (
    'name' => 'action',
    'suffix' => 'Action',
    'default' => 'index',
  ),
  'password' => '119328118',
  'uri' => 
  array (
    'mode' => 'short',
    'controllerName' => 'controller',
    'actionName' => 'action',
    'normal' => 
    array (
      'mode' => 'normal',
      'trim' => '0',
      'symbol' => '&',
      'extenstion' => '.html',
    ),
    'middle' => 
    array (
      'mode' => 'middle',
      'trim' => '1',
      'symbol' => '/',
      'extenstion' => '.html',
    ),
    'short' => 
    array (
      'mode' => 'short',
      'trim' => '1',
      'symbol' => '/',
      'extenstion' => '.html',
    ),
  ),
)
?>