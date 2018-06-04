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
     * $rules = [
     * 'api:*' => 'api\client\*:*',
     * 'api:client:admin:*' => 'api\client\{2}:*',
     * 'api:admin:*' => 'api\admin\{2}:*',
     * 'admin:*' => 'admin\{1}:*',
     * 'udp:*' => 'udp\{1}:*',
     * 's:*' => 's:index',
     * 'index:*' => 'ip:*',
     * 'shortURL:*:*' => '\shortURL\*:*'
     * ];
     *
     * $urls = [
     * '/api/client/admin' => ['controller' => 'api\client\client', 'action' => 'index'],
     * '/api/client/admin/remove' => ['controller' => 'api\client', 'action' => 'remove'],
     * '/api/url/add' => ['controller' => 'api\url', 'action' => 'add'],
     * '/api/admin/free/add' => ['controller' => 'api\admin\free', 'action' => 'add'],
     * '/admin/index' => ['controller' => 'admin\index', 'action' => 'index'],
     * '/admin/dir/add' => ['controller' => 'admin\dir', 'action' => 'add'],
     * '/udp/index' => ['controller' => 'udp', 'action' => 'index'],
     * '/udp/add' => ['controller' => 'udp', 'action' => 'index'],
     * '/s/for' => ['controller' => 's', 'action' =>'index'],
     * '/index/for' => ['controller' => 'ip', 'action' =>'for'],
     * '/usr/login/check' => ['controller' => 'usr\login', 'action' => 'check'],
     * '/shortURL/free/sss' => ['controller' => '\shortURL\free', 'action' => 'sss'],
     * ];
     */
    public function parse($url, $controller, $action)
    {
        if (!$this->config) {
            return array('controller' => $controller, 'action' => $action);
        }
        if($url == '' || $url == '/') $url = 'index/index.html';
        $url = ltrim($url, '/');
        $dirName = pathinfo($url, PATHINFO_DIRNAME);
        $dirInfo = explode('/', $dirName);
        $fileName = pathinfo(ltrim($url, '/'), PATHINFO_FILENAME);
        if ($dirName == '.') {
            $dirInfo = array();
        }
        $dirInfo[] = $fileName;
        //补全路径
        if(count($dirInfo) == 1)
        {
            $dirInfo[] = 'index';
        }

        $dir = [];
        $match = ['url' => $url, 'controller' => $controller, 'action' => $action];
        if(isset($this->config['*:*'])) {
            list($controller, $action) = explode(':', $this->config['*:*']);
            $match['match'] = '*:*';
            $match['controller'] = $controller ? $controller : 'index';
            $match['action'] = $action ? $action : 'index';
            return $match;
        }
        $match['dirInfo'] = $dirInfo;

        $lastFound = [];
        //将内容和规则做匹配
        foreach ($dirInfo AS $key => $path) {
            $dir[] = $path;
            $register = [];
            $matchLen = 0;
            foreach($this->config as $config => $val) {
                //匹配规则
                $configArr = explode(':', $config);
                $interSet = array_intersect_assoc($configArr, $dir);
                if($interSet) {
                    $countInterSet = count($interSet);
                    if($configArr[$countInterSet] == '*' || $configArr[$countInterSet] == $dirInfo[$dirInfo[$key]]) {
                        if($matchLen < $countInterSet) {
                            $register = ['rule' => $config, 'val' => $val, 'interSet' => $interSet];
                        }else{
                            $matchLen = $countInterSet;
                        }
                    }
                }
            }
        }
        if(!empty($register)){
            $lastFound = $register;
        }
        $match['match'] = $lastFound;
        //没有匹配到就直接使用/url做匹配
        if(!$match['match']) {
            $info = $this->getInfoFromDirInfo($dirInfo);
            $match['controller'] = $info['controller'];
            $match['action'] = $info['action'];
            return $match;
        }
        //解析规则
        $rulesVal = $match['match']['val'];
        preg_match_all("/\{[\d]{1,}\}|[\*]{1}/", $rulesVal, $rules);

        if(empty($rules) || empty($rules[0])) {
            $info = $this->getInfoFromDirInfo($dirInfo);
            $match['controller'] = $info['controller'];
            $match['action'] = $info['action'];
        }
        $maxIndex = 0;
        //获取*位置最大值索引值
        if(preg_match("/[\*]{1}/", $rulesVal)) {
            foreach($rules[0] as $val) {
                $val = intval(str_replace(array('{', '}'), '', $val));
                if($val > $maxIndex) $maxIndex = $val;
            }
            $maxIndex++;
        }
        $replacements = $rules[0];
        foreach($rules[0] as $key => $val)
        {
            if(preg_match("/\{[\d]{1,}\}/", $val)) {
                $index = str_replace(array('{', '}'), '', $val);
                $replacements[$key] = $dirInfo[$index] ?? 'index';
                $rulesVal = preg_replace("/\{[\d]{1,}\}/", $replacements[$key], $rulesVal, 1);
            }else if($val == '*'){
                $replacements[$key] = $dirInfo[$maxIndex] ?? 'index';
                //一次只替换一个，用于匹配多次
                $rulesVal = preg_replace("/[\*]{1}/", $replacements[$key], $rulesVal, 1);
                $maxIndex++;
            }
        }
        list($controller, $action) = explode(":", $rulesVal);
        $match['controller'] = $controller;
        $match['action'] = $action ?? 'index';
        $match['replacements'] = $replacements;
        $match['rulesVal'] = $rulesVal;
        return $match;
    }

    /**
     * 从路径中获取controller和action
     *
     * @param array $dirInfo 目录路径
     * @return array
     */
    protected function getInfoFromDirInfo($dirInfo)
    {
        if(count($dirInfo) >= 2) {
            $action = array_pop($dirInfo);
            $controller = join("\\", $dirInfo);
        }else{
            $controller = join("\\", $dirInfo);
            $action = 'index';
        }
        return ['controller' => $controller, 'action' => $action];
    }
}