<?php
namespace Qii\Route;
/**
 * 路由规则类
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-10-24 23:11
 */

class Parse
{
    const VERSION = 1.3;

    /**
     * 路由转发， 转发对应的规则中xx不能为*
     *
     * @param String $controller
     * @param String $action
     * @param Array $router
     * @return Array ($controller, $action);
     *
     * *:* => *:yyy 所有controller和action都转发到 *->yyy
     * *:* => yy:* 所有转发到xxx->*, 这里的*，前边对应的是什么，后边就对应转发到什么，比如: *:xxx => yy:yyy
     * xx:* => yy:* xx中对应的方法转发到yy对应的方法
     * xx:* => yy:yyy xxx Controller转发到 yy->yyy
     * *:xxx => yy:yyy 所有Controller转发到 yy->yyy
     */
    public static function get($controller, $action = '', $thirdParam = '')
    {
        if ($controller == 'Qii') {
            return array('controller' => $controller, 'action' => $action);
        }
        //如果第一列的是*号则所有的controller都执行对应的x:
        $router =\Qii\Config\Register::getAppConfigure(\Qii\Config\Consts::APP_SITE_ROUTER);
        $rewriteRule = \Qii\Config\Register::getAppConfigure(\Qii\Config\Register::get(\Qii\Config\Consts::APP_INI_FILE), 'rewriteRule');
        if (!$rewriteRule) $rewriteRule = 'Normal';
        \Qii\Autoloader\Import::requires(Qii_DIR . DS . 'Route' . DS . 'Parse' .DS. $rewriteRule . '.php');
        $className = '\Qii\Route\Parse\\' . $rewriteRule;
        if (!class_exists($className, false)) {
            throw new \Qii\Exceptions\ClassNotFound(\Qii::i(1103, $className), __LINE__);
        }
        $class = new $className();
        $class->setConfig($router);
        return $class->parse($controller, $action, $thirdParam);
    }
}

;