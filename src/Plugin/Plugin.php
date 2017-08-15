<?php
namespace Qii\Plugin;
/**
 * 插件管理
 *
 * @author Zhu Jinhui<jinhui.zhu@live.cn>2015-08-12 15:04
 * @version 1.2
 *
 * 必须实现 __construct(\Qii\Plugins &$pluginManger)方法
 * 用法:
 * class demo implements \Qii\PluginIntf
 * {
 *    public function __construct(\Qii\Plugins &$pluginManger)
 *    {
 *        $pluginManger->register('test1', $this, 'test');
 *        $pluginManger->register('test2', $this, 'haha');
 *    }
 *    public function test()
 *    {
 *        echo __METHOD__;
 *        print_r(func_get_args());
 *    }
 *    public function haha()
 *    {
 *        echo __METHOD__;
 *    }
 * }
 *
 *
 *    $plugins = new Qii\Plugins();
 *    $plugins->addPlugin('demo');
 *  执行插件方法 带参数
 *    $plugins->trigger('test1', 'argvs1', 'argvs2');
 *  不带参数
 *    $plugins->trigger('test2');
 */

class Plugin
{
    const VERSION = '1.2';
    //插件列表
    protected $_listeners;

    /**
     * 注册插件到系统
     */
    public function __construct(array $plugins = array())
    {
        if (is_array($plugins)) {
            foreach ($plugins AS $plugin) {
                new $plugin($this);
            }
        }
    }

    /**
     * 添加插件
     * @param string $class 插件类
     */
    public function addPlugin($class)
    {
        new $class($this);
    }

    /**
     * 注册插件
     * @param $hook 钩子名称
     * @param $reference 插件的引用
     * @param $method 钩子对应的方法名
     */
    public function register($hook, &$reference, $method)
    {
        //获取插件要实现的方法
        $key = get_class($reference) . '->' . $method;
        //将插件的引用连同方法push进监听数组中
        $this->_listeners[$hook][$key] = array(&$reference, $method);
        #此处做些日志记录方面的东西
    }

    /**
     * 触发一个钩子
     *
     * @param string $hook 钩子的名称
     * @param mixed $data 钩子的入参
     * @return mixed
     */
    function trigger($hook)
    {
        $result = '';
        $argvs = func_get_args();
        $hook = array_shift($argvs);
        //查看要实现的钩子，是否在监听数组之中
        if (isset($this->_listeners[$hook]) && is_array($this->_listeners[$hook]) && count($this->_listeners[$hook]) > 0) {
            //循环调用开始指定hook中注册的方法，并将结果拼接起来返回
            foreach ($this->_listeners[$hook] as $listener) {
                //取出插件对象的引用和方法
                $class =& $listener[0];
                $method = $listener[1];
                if (method_exists($class, $method)) {
                    //动态调用插件的方法
                    $result .= call_user_func_array(array($class, $method), $argvs);
                }
            }
        }
        return $result;
    }
}