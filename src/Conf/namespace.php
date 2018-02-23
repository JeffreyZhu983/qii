<?php
/**
 * Qii 名称空间配置
 * @author zjh
 * @version 1.3
 */
return [
    //设置是否使用名称空间
    'setUseNamespace' => [
        ['Qii\\', true],
        ['Qii\Action', true],
        ['Qii\Autoloader', true],
        ['Qii\Bootstrap', true],
        ['Qii\Config', true],
        ['Qii\Consts', true],
        ['Qii\Controller', true],
        ['Qii\Exceptions', true],
        ['Qii\Language', true],
        ['Qii\Library', true],
        ['Qii\Loger', true],
        ['Qii\Plugin', true],
        ['Qii\Request', false],
        ['Qii\Router', true],
        ['Qii\View', true],
        ['WhichBrowser', true],
        ['BigPipe', true],
        ['Smarty\\', false],
        ['Smarty\\Internal', false],
    ],
    //设置指定名称空间的文件路径，如按照namespace的不用指定
    'addNamespace' => [
        ['Qii\\', Qii_DIR . DS],
        ['Qii\Action', Qii_DIR . DS . 'Action'],
        ['Qii\Autoloader', Qii_DIR . DS . 'Autoloader'],
        ['Qii\Controller', Qii_DIR . DS . 'Controller'],
        ['Qii\Bootstrap', Qii_DIR . DS . 'Bootstrap'],
        ['Qii\Config', Qii_DIR . DS . 'Config'],
        ['Qii\Consts', Qii_DIR . DS . 'Consts'],
        ['Qii\Exceptions', Qii_DIR . DS . 'Exceptions'],
        ['Qii\Language', Qii_DIR . DS . 'Language'],
        ['Qii\Library', Qii_DIR . DS . 'Library'],
        ['Qii\Loger', Qii_DIR . DS . 'Loger'],
        ['Qii\Plugin', Qii_DIR . DS . 'Plugin'],
        ['Qii\Request', Qii_DIR . DS . 'Request'],
        ['Qii\Response', Qii_DIR . DS . 'Response'],
        ['Qii\Router', Qii_DIR . DS . 'Router'],
        ['Qii\View', Qii_DIR . DS . 'View'],
        ['Smarty', Qii_DIR . DS . 'View' . DS . 'smarty'],
        ['Smarty', Qii_DIR . DS . 'View' . DS . 'smarty' . DS . 'sysplugins'],
        ['WhichBrowser', Qii_DIR . DS . 'Library'. DS . 'Third'. DS . 'WhichBrowser'],
        ['BigPipe', Qii_DIR . DS . 'Library'. DS .'BigPipe'. DS .'BigPipe']
    ]
];
