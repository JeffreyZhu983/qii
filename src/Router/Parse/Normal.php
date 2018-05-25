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
        if ($url == '' || $url == '/') $url = 'index/index.html';
        $url = ltrim($url, '/');
        $dirName = pathinfo($url, PATHINFO_DIRNAME);
        $dirInfo = explode('/', $dirName);
        $fileName = pathinfo(ltrim($url, '/'), PATHINFO_FILENAME);
        if ($dirName == '.') {
            $dirInfo = array();
        }
        $dirInfo[] = $fileName;
        //补全路径
        if (count($dirInfo) == 1) {
            $dirInfo[] = 'index';
        }

        $dir = [];
        $match = ['key' => '', 'val' => '', 'url' => $url];
        if (isset($this->config['*:*'])) {
            list($controller, $action) = explode(':', $this->config['*:*']);
            $match['match'] = '*:*';
            $match['controller'] = $controller ? $controller : 'index';
            $match['action'] = $action ? $action : 'index';
            return $match;
        }
        foreach ($dirInfo AS $path) {
            $dir[] = $path;
            $notAll = join($dir, ':');
            if (isset($this->config[$notAll])) {
                $config = $this->config[$notAll];
                //匹配最长的规则
                if (strlen($config) > strlen($match['val'])) {
                    $match = array_merge($match, ['key' => $notAll, 'val' => $config]);
                }
            }
            $joinPath = join($dir, ':') . ":*";
            if (isset($this->config[$joinPath])) {
                $config = $this->config[$joinPath];
                //匹配最长的规则
                if (strlen($config) > strlen($match['val'])) {
                    $match = array_merge($match, ['key' => $joinPath, 'val' => $config]);
                }
            } else if (isset($this->config[$joinPath . ':*'])) {
                $match = array_merge($match, ['key' => $joinPath, 'val' => $this->config[$joinPath . ':*']]);
            } else if (isset($this->config[$joinPath . ':*:*'])) {
                $match = array_merge($match, ['key' => $joinPath, 'val' => $this->config[$joinPath . ':*:*']]);
            }
        }
        $match['dirInfo'] = $dirInfo;
        //如果match到就解析match的内容
        if ($match['val']) {
            $real = $match['val'];
            $matches = explode(':', $match['val']);
            $match['matches'] = $matches;
            $maxIndex = 0;
            foreach ($match['matches'] as $val) {
                preg_match_all("/[\d]/", $val, $index);
                if ($index && $index[0]) {
                    foreach ($index[0] as $i) {
                        if($maxIndex < $i) $maxIndex = $i;
                        if (isset($dirInfo[$i])) $real = str_replace('{' . $i . '}', $dir[$i], $real);
                    }
                }
            }
            $maxIndex++;
            $match['real'] = $real;
            $matches = explode(':', $real);
            $action = array_pop($matches);
            $controller = join('\\', $matches);
            $match['controller'] = $controller;
            //如果 action == * 那就取路径中的配置 或默认为index
            $match['action'] = $action == '*' && isset($dirInfo[$maxIndex])? $dirInfo[$maxIndex] : 'index';
        } else {
            $controller = 'index';
            $action = 'index';
            if (count($dirInfo) > 1) {
                $action = array_pop($dirInfo);
                $controller = join('\\', $dirInfo);
            } else if (count($dirInfo) == 1 && !empty($dirInfo[0])) {
                $controller = $dirInfo[0];
            }
            $match['controller'] = $controller;
            $match['action'] = $action;
            //匹配配置文件中以 * 开头的规则
            foreach ($this->config as $key => $config) {
                if (substr($key, 0, 2) == '*:') {
                    list($sourceController, $sourceAction) = explode(':', $key);
                    list($destController, $destAction) = explode(":", $config);
                    $match['controller'] = $destController;
                    if ($sourceAction == '*') {
                        $map['action'] = $destAction;
                    } else if ($map['action'] == $sourceAction) {
                        $map['action'] = $destAction;
                    }
                }
            }
        }
        return $match;
    }
}