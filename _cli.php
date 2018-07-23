<?php
/**
 * @author Jinhui Zhu  <jinhui.zhu@live.cn>
 * 通过命令行直接生成项目目录
 */
ini_set("display_errors", "On");
/**
 * 错误模式
 */
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

/**
 * 自动生成目标目录
 *
 * php -q _cli.php create=yes workspace=../project cache=tmp useDB=1
 * create: 是否自动创建
 * workspace: 程序存放的目录
 * cache: 缓存目录
 * useDB: 是否使用数据库,值 0/1
 * 目前已经将配置文件写在db.ini中，配置内容将不在创建项目的时候提供，可以自行在目录中修改
 */
class cmd
{
    const VERSION = '1.2';
    public $dir = array('configure', 'controller', 'model', 'view', 'plugins', 'tmp');

    public function __construct($args)
    {
        $param = $this->parseArgvs($args);
        if (sizeof($param) < 1 || ($param['dbHost'] != '' && sizeof($param) < 7)) {
            $this->stdout("命令行使用如下:\n
>php -q _cli.php create=yes workspace=../project cache=tmp dbHost=localhost dbName=test dbUser=root dbPassword=test\n
 * create: 是否自动创建:yes; \n
 * workspace: 工作目录\n
 * cache : 缓存目录\n
 * dbHost : 数据库服务器IP\n
 * dbName : 数据库名称 : \n
 * dbUser : 数据库用户名\n
 * dbPassword : 数据库密码 : \n
 ");
            $this->stdout("是否自动创建 yes/no:");
            $param['create'] = trim(fgets(\STDIN));
            $this->stdout("工作目录:");
            $param['workspace'] = trim(fgets(\STDIN));
            $this->stdout("缓存目录:");
            $param['cache'] = trim(fgets(\STDIN));
            
            $this->stdout('数据库服务器IP:');
            $param['dbHost'] = trim(fgets(\STDIN));
            if(!$param['dbHost']) $param['dbHost'] = 'localhost';
            $this->stdout("数据库名称:");
            $param['dbName'] = trim(fgets(\STDIN));

            $this->stdout('请输入数据库用户名:');
            $param['dbUser'] = trim(fgets(\STDIN));

            $this->stdout('请输入数据库密码:');
            $param['dbPassword'] = trim(fgets(\STDIN));

            $this->stdout('将要在'. $param['workspace'] .'创建项目，确认请输入yes,取消请输入no:');

            $param['create'] = trim(fgets(\STDIN));
        }
        $param['useDB'] = 0;
        if($param['dbName'] != '')
        {
            $param['useDB'] = 1;
        }
        if ($param['create'] == 'yes') {
            if ($this->workspace($param['workspace'])) {
                $cache = $param['cache'];
                if (empty($param['cache'])) $cache = 'tmp';
                $this->dir[5] = $cache;
                //创建目录工作区目录
                $publicDir = $param['workspace'] . '/public';
                if(!is_dir($publicDir))
                {
                    if(mkdir($publicDir, 0777))
                    {
                        $this->stdout('创建public目录'. $publicDir ."成功\n");
                    }
                    else
                    {
                        $this->stdout('创建public目录'. $publicDir ."失败\n");
                    }
                }
                foreach ($this->dir AS $d) {
                    $path = $param['workspace'] . '/private/' . $d;
                    if (!is_dir($path)) {
                        $date = date('Y-m-d H:i:s');
                        if(mkdir($path, 0777, true))
                        {
                            $this->stdout('创建'. $path . "成功\n");
                        }
                        else
                        {
                            $this->stdout('创建'. $path . "失败\n");
                        }
                        //写入.htaccess文件到包含的目录，不允许通过Apache浏览
                        $htaccess = array();
                        $htaccess[] = "##";
                        $htaccess[] = "#";
                        $htaccess[] = "#	\$Id: .htaccess 268 {$date}Z Jinhui.Zhu $";
                        $htaccess[] = "#";
                        $htaccess[] = "#	Copyright (C) 2010-2012 All Rights Reserved.";
                        $htaccess[] = "#";
                        $htaccess[] = "##";
                        $htaccess[] = "";
                        $htaccess[] = "Options Includes";
                        file_put_contents($path . '/.htaccess', join("\n", $htaccess));
                    } else {
                        $this->stdout("{$path} 已经存在.". PHP_EOL);
                    }
                }
                //拷贝网站配置文件site.xml到项目目录
                if($cache != 'tmp'){
                    $appIni = file_get_contents('_cli/app.ini');
                    $appIni = str_replace('tmp/compile', $cache . '/compile', $appIni);
                    $appIni = str_replace('tmp/cache', $cache . '/cache', $appIni);
                    file_put_contents($param['workspace'] . '/private/configure/app.ini', $appIni);
                }else if (!copy("_cli/app.ini", $param['workspace'] . '/private/configure/app.ini')) {
                     $this->stdout('拷贝 app.ini 到 ' . $param['workspace'] . '/private/configure/app.ini失败, 拒绝访问.');
                    
                }
                if (!copy("_cli/router.config.php", $param['workspace'] . '/private/configure/router.config.php')) {
                    $this->stdout('拷贝 router.config.php 到' . $param['workspace'] . '/private/configure/router.config.php 失败, 拒绝访问.');
                }
                if ($param['useDB'] != 'no') {
                    $dbIni = file_get_contents('_cli/db.ini');
                    $dbIni = str_replace('DB_NAME', $param['dbName'], $dbIni);
                    $dbIni = str_replace('DB_HOST', $param['dbHost'], $dbIni);
                    $dbIni = str_replace('DB_USER', $param['dbUser'], $dbIni);
                    $dbIni = str_replace('DB_PASSWORD', $param['dbPassword'], $dbIni);
                    
                    file_put_contents($param['workspace'] . '/private/configure/db.ini', $dbIni);
                }

                //生成数据库文件
                //--生成首页文件
                //--获取文件的相对路径
                $realPath = $this->getRealPath($param['workspace']);
                $this->stdout("真实路径 " . $realPath . "\n");
                $QiiPath = $this->getRelatePath($realPath . "/index.php", dirname(__FILE__) . "/src/Qii.php");
                $this->stdout("Qii 路径 " . $QiiPath . "\n");
                $date = date("Y/m/d H:i:s");
                $indexPage = array();
                $indexPage[] = "<?php";
                $indexPage[] = "/**";
                $indexPage[] = " * This is index page auto create by Qii, don't delete";
                $indexPage[] = " * ";
                $indexPage[] = " * @author Jinhui.zhu	<jinhui.zhu@live.cn>";
                $indexPage[] = " * @version  \$Id: index.php,v 1.1 {$date} Jinhui.Zhu Exp $";
                $indexPage[] = " */";
                $indexPage[] = 'require("../'.$QiiPath.'");';
                $indexPage[] = '$app = \\Qii::getInstance();';
                $indexPage[] = '//如需更改网站源代码存储路径，请修改此路径';
                $indexPage[] = '$app->setWorkspace(\'../private\');';
                $indexPage[] = '$env = getenv(\'WEB_ENVIRONMENT\') ? getenv(\'WEB_ENVIRONMENT\') : \'product\';';
                $indexPage[] = '$app->setEnv($env);';
                $indexPage[] = '$app->setCachePath(\''.$cache.'\');';
                $indexPage[] = '$app->setAppConfigure(\'../private/configure/app.ini\');';
                if ($param['useDB']) $indexPage[] = '$app->setDB(\'../private/configure/db.ini\');';
                $indexPage[] = '$app->setRouter(\'../private/configure/router.config.php\')';
                $indexPage[] = '->run();';
                if (!file_exists($realPath . "/public/index.php")) {
                    //如果文件不存在就写入
                    file_put_contents($realPath . "/public/index.php", join("\n", $indexPage));
                }
                //写入首页controller
                if (!file_exists($realPath . "/private/controller/index.php")) {
                    $indexContents = array();
                    $indexContents[] = "<?php";
                    $indexContents[] = 'namespace controller;' . PHP_EOL;
                    $indexContents[] = 'use \Qii\Base\Controller;' . PHP_EOL;
                    $indexContents[] = "class index extends Controller";
                    $indexContents[] = "{";
                    $indexContents[] = "\tpublic \$enableView = true;";
                    $indexContents[] = "\tpublic function __construct()\n\t{";
                    $indexContents[] = "\t\tparent::__construct();";
                    $indexContents[] = "\t}";
                    $indexContents[] = "\tpublic function indexAction()\n\t{";
                    $indexContents[] = "\t\t return new \Qii\Base\Response(array('format' => 'html', 'body' => '请重写 '. __FILE__ . ' 中的 indexAction 方法, 第 ' . __LINE__ . ' 行'));";
                    $indexContents[] = "\t}";
                    $indexContents[] = "}";
                    file_put_contents($realPath . "/private/controller/index.php", join("\n", $indexContents));
                }
                //apache rewrite file
                $htaccessFile = $param['workspace'] . "/public/.htaccess";
                if(!file_exists($htaccessFile)){
                    if(!copy('_cli/.htaccess', $htaccessFile)){
                        $this->stdout($this->stdout("拷贝 .htaccess 到 ". $htaccessFile . ' 失败, 拒绝访问') . PHP_EOL);
                    }
                }
            }
        } else if($param['create'] == 'no'){
            $this->stdout('您已经取消');
        }
    }

    /**
     * 获取文件相对路径
     *
     * @param String $cur 路径1
     * @param String $absp 路径2
     * @return String 路径2相对于路径1的路径
     */
    public function getRelatePath($cur, $absp)
    {
        $cur = str_replace('\\', '/', $cur);
        $absp = str_replace('\\', '/', $absp);
        $sabsp = explode('/', $absp);
        $scur = explode('/', $cur);
        $la = count($sabsp) - 1;
        $lb = count($scur) - 1;
        $l = max($la, $lb);

        for ($i = 0; $i <= $l; $i++) {
            if ($sabsp[$i] != $scur[$i])
                break;
        }
        $k = $i - 1;
        $path = "";
        for ($i = 1; $i <= ($lb - $k - 1); $i++)
            $path .= "../";
        for ($i = $k + 1; $i <= ($la - 1); $i++)
            $path .= $sabsp[$i] . "/";
        $path .= $sabsp[$la];
        return $path;
    }

    /**
     * 获取相对目录
     *
     * @param String $workspace 目标路径
     * @return String 目标路径相对于当期路径的相对路径
     */
    public function getRealPath($workspace)
    {
        $currentDir = str_replace("\\", "/", dirname(__FILE__));
        $workspace = str_replace("\\", "/", $workspace);

        if ($workspace[0] == '/') {
            return $workspace;
        } else {
            $workspaceArray = explode("/", $workspace);
            $currentDirArray = explode("/", $currentDir);

            $work = array();
            foreach ($workspaceArray AS $k) {
                if ($k == '..') {
                    array_pop($currentDirArray);
                } elseif ($k == '.') {

                } else {
                    $work[] = $k;
                }
            }
            if (!empty($currentDirArray)) {
                return join("/", $currentDirArray) . "/" . join("/", $work);
            } else {
                return false;
            }
        }
    }

    /**
     * 创建工作区目录
     *
     * @param String $dir
     * @return Bool
     */
    public function workspace($dir)
    {
        if (!empty($dir)) {
            if (!is_dir($dir)) {
                return mkdir($dir, 0777, true);
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * 匹配命令行参数
     *
     * @param String $argvs
     * @return Array 返回参数对应的值
     */
    public function parseArgvs($argvs)
    {
        $keyValue = array();
        foreach ($argvs AS $value) {
            $valueArray = explode("=", $value);
            $k = $valueArray[0];
            $v = stripslashes($valueArray[1]);
            $keyValue[$k] = $v;
        }
        return $keyValue;
    }
    /**
     * 在windows cmd 情况下的中文输出乱码问题
     * @param string $string
     * @return bool|int
     */
    public function stdout($string)
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $string = iconv('utf-8', 'gbk', $string);
        fwrite(\STDOUT, $string);
    }
}

array_shift($argv);
new cmd($argv);
?>
