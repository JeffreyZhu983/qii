<?php
namespace Qii\Router\Parse;

/**
 * Route规则文件
 * 兼容以前版本的匹配规则
 *
 * @author Jinhui Zhu<jinhui.zhu@live.cn>2015-10-24 23:11
 * @version 1.2
 */

class Normal
{
    const VERSION = '1.2';
    private $config;

    public function __construct()
    {

    }

    /**
     * 设置路由规则
     * @param Array $config 路由规则
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * 路由转发， 转发对应的规则中xx不能为*
     *
     * @param string $url url链接
     * @param String $controller
     * @param String $action
     * @return Array ($controller, $action);
     *
     * *:* => *:yyy 所有controller和action都转发到 *->yyy
     * *:* => yy:* 所有转发到xxx->*, 这里的*，前边对应的是什么，后边就对应转发到什么，比如: *:xxx => yy:yyy
     * xx:* => yy:* xx中对应的方法转发到yy对应的方法
     * xx:* => yy:yyy xxx Controller转发到 yy->yyy
     * *:xxx => yy:yyy 所有Controller转发到 yy->yyy
     * xxx:*(yy):第三个参数 => {1}:* 转发xxx:yy => yy:第三个参数
     */
    public function parse($url, $controller, $action)
    {
        if (!$this->config) {
            return array('controller' => $controller, 'action' => $action);
        }
        $url = ltrim($url, '/');
        $dirName = pathinfo($url, PATHINFO_DIRNAME);
        $dirInfo = explode('/', $dirName);
        $fileName = pathinfo(ltrim($url, '/'), PATHINFO_FILENAME);
        if ($dirName == '.') {
            $dirInfo = array();
        }
        $dirInfo[] = $fileName;
        $dir = [];
        $match = ['key' => '', 'val' => '', 'url' => $url];
        foreach ($dirInfo AS $path) {
            $dir[] = $path;
            $joinPath = join($dir, ':') . ":*";
            if (isset($this->config[$joinPath])) {
                $config = $this->config[$joinPath];
                //匹配最长的规则
                if (strlen($config) > strlen($match['val'])) {
                    $match = array_merge($match, ['key' => $joinPath, 'val' => $config]);
                }
            }
        }
        $match['dirInfo'] = $dirInfo;
        //如果match到就解析match的内容
        if ($match['val']) {
            $matches = explode(':', $match['val']);
            $match['matches'] = $matches;
            $action = array_pop($matches);
            $controller = join('\\', $matches);
            $controllerExplode = explode('\\', $controller);
            if (stristr($controller, '{1}')) {
                $pad = count($controllerExplode) - count($dirInfo);
                if ($pad > 0) {
                    $dirInfo = array_pad($dirInfo, count($controllerExplode), 'index');
                }
                $controller = join('\\', array_slice($dirInfo, 0, count($controllerExplode)));
            }
            $action = $action == '{1}' || $action == '*' ? isset($dirInfo[count($controllerExplode)]) ? $dirInfo[count($controllerExplode)] : 'index' : $action;
            $match['controller'] = $controller;
            $match['action'] = $action;
        } else {
            $match['controller'] = isset($dirInfo[0]) ? $dirInfo[0] : 'index';
            $match['action'] = isset($dirInfo[1]) ? $dirInfo[1] : 'index';
        }
        return $match;
    }
}