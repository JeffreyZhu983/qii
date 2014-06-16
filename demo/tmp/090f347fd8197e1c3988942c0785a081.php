<?php 
 return array (
  'root attr' => 
  array (
    'name' => 'MVC相关配置',
  ),
  'root' => 
  array (
    'index' => 
    array (
      'status' => 
      array (
        'password' => '',
        'enabled' => '1',
        'search' => '1',
        'debug' => '1',
        'publish' => '0',
        'callDefault' => '0',
        'closePage' => 'error:index',
        'errorPage' => 'error:index',
        'uri' => 'short',
        'message' => '站点升级，暂时关闭，请网民谅解。',
        'timezone' => 'Asia/Shanghai',
        'contentType' => 'text/html',
        'charset' => 'UTF-8',
        'autoCreate' => '1',
        'dbModel' => 'pdo',
        'view' => 'smarty',
        'cache' => 'memcache',
        'security' => 
        array (
          'enable' => '1',
          'key' => 'security_sid',
          'discription' => 'enable:是否开启安全验证；key:POST数据的时候安全字符串用到的key',
        ),
        'discription' => 'password:用于将此文件生成数组的密码;enabled:1(开启站点), 0(关闭站点)，如果关闭站点，设置错误页面后将会跳转到错误页面，如果错误页面不存在就显示404错误;debug:是否开启调试;callDefault:如果调用的controller没有的话是否调用默认action, 如果是publish模式的话debug开启后, 如果没有找到action就调用默认action，只要是publish為1就不報錯, publish 等於1的時候如果指定了錯誤頁面就跳轉至錯誤頁面，否则就404错误。; uri为url模式，分为:short, middle, short三种模式.；autoCreate:是否自动创建目录，目录的指定在xpath中,security：是否启用安全设置，如果启用了安全设置，在POST的时候需要带上安全验证支付串。',
      ),
      'memcache' => 
      array (
        'servers' => '127.0.0.1',
        'ports' => '11211',
        'discription' => '多个服务器IP和端口以;隔开',
      ),
      'uri' => 
      array (
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
      'query' => 
      array (
        'map' => 
        array (
          0 => 'controller',
          1 => 'action',
          2 => 'param',
        ),
        'discription' => 'map:为参数的顺序,当启用短URI标签的情况下会按照这个顺序去遍历参数；',
      ),
      'config' => 
      array (
        'name' => 'config',
        'path' => 'config',
        'ext' => 'config',
        'discription' => 'path:路径，默认config的路径，ext:config的后缀.',
      ),
      'model' => 
      array (
        'name' => 'model',
        'path' => 'model',
        'ext' => 'model',
        'discription' => 'path:路径，默认model的路径，ext:model的后缀.',
      ),
      'controller' => 
      array (
        'name' => 'controller',
        'path' => 'controller',
        'ext' => 'controller',
        'default' => 'index',
        'discription' => 'name:controller配置, path：路径，默认为controller下, ext为controller类的后缀，default:默认控制器执行的方法',
      ),
      'action' => 
      array (
        'name' => 'action',
        'default' => 'index',
        'discription' => 'name:默认执行的动作',
      ),
      'view' => 
      array (
        'name' => 'view',
        'ldelimiter' => '{#',
        'rdelimiter' => '#}',
        'path' => 'view',
        'compile' => 'tmp/compile',
        'cache' => 'tmp/cache',
        'lifetime' => '3600',
        'assign' => 
        array (
          'title' => 'Qii',
          'skin' => '/i/',
        ),
        'discription' => 'name:view的名字，path模板路径;compile：编译后的模板路径;cache缓存目录路径;lifetime:cache的时间;assign中为view的assign数组',
      ),
      'class' => 
      array (
        'name' => 'class',
        'path' => 'class',
        'ext' => 'class',
        'discription' => 'path:class的路径',
      ),
      'plugin' => 
      array (
        'name' => 'plugin',
        'path' => 'plugin',
        'discription' => 'path:plugin的路径',
      ),
      'xpath' => 
      array (
        'path' => 'model;view;controller;class;plugin',
        'discription' => 'path:搜索文件的路径，包括以上搜有path，可以额外自己添加一些.',
      ),
      'discription' => '
			设置MVC相关配置信息，此文件用于index入口。
		',
    ),
  ),
)
?>