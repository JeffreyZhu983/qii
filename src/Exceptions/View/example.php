<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>使用方法 - Qii Framework</title>
    <meta name="keywords" content="Qii框架,PHP开发框架">
	<meta name="description" content="Qii 框架，以最简单的方式创建你的Web应用。" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <?php include(Qii_DIR . '/view/style.php'); ?>
</head>
<body>
<h1>使用方法：</h1>
<ul>
    <li><a href="#createProject">创建项目</a></li>
    <li><a href="#env">设置环境</a></li>
    <li><a href="#useFramework">框架的使用</a>
        <ul>
            <li><a href="#useCommandLine">1)命令行运行程序</a></li>
            <li><a href="#autoload">2) 自动加载类</a></li>
            <li><a href="#basicMethod">3) Qii 基本功能：</a></li>
            <li><a href="#supportMultDomain"> 4) 多域名支持：</a></li>
            <li><a href="#modle"> 5) 数据库使用示例：</a></li>
            <li><a href="#view"> 6) View的支持</a></li>
            <li><a href="#controller"> 7) Controller的使用</a></li>
            <li><a href="#cache"> 8) Cache支持</a></li>
        </ul>
    </li>
    <li><a href="#download">下载</a></li>
</ul>
<pre>
<a id="createProject"> 1、创建项目</a>
<code>
    通过命令行进入当前目录，并执行：php -q _cli.php create=yes workspace=../project cache=tmp useDB=1
    Command line usage:
    >php -q _cli.php create=yes workspace=../project cache=tmp useDB=1
    * create: is auto create default:yes;
    * workspace: workspace
    * cache : cache dir
    * useDB : use db or not
    程序将自动创建工作目录，并生成首页及配置相关文件。设置好Web网站目录，开启.htaccess即可直接访问。
    相关的配置文件见 configure/app.ini及configure/db.ini文件
</code>
<a id="env">2、设置环境</a>
<code>
    1、安装服务器软件（详情google或百度）
    2、设置服务器的Rewrite规则：
    Apache:
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond $1 !^(index.php|images|robots.txt)
    RewriteRule (.*)$ index.php/$1 [L,QSA,PT]
    RewriteRule ^(.*)\.phar$ - [F]
    RewriteRule ^(.*)\.ini$ - [F]
    Nginx:
    if (!-f $request_filename){
    rewrite (.*) /index.php;
    }
    location ~* /.(ini|phar){
    deny all;
    }
</code>
<a id="useFramework">3、框架的使用</a>
<code>
    <a id="useCommandLine"> 1) 命令行运行程序</a>
    <code>
        仅支持GET方法
        1. normal模式
        php -q index.php controller=index/action=home/id=100
        php -q index.php controller=plugins/action=index/page=2

        2. middle模式
        php -q index.php controller/index/action/home/id/100
        php -q index.php controller/plugins/action/index/page/2

        3. short模式
        php -q index.php index/home/100
        php -q index.php plugins/page/2.html
    </code>
    <a id="autoload">2) 自动加载类</a>
    <code>
        new \controller\user(); === require("controller<?= DS ?>user.php"); new \controller\user();
        new \model\user(); === require("model<?= DS ?>user.php"); new \model\user();
        new \model\test\user(); === require("model<?= DS ?>test<?= DS ?>user.php"); new \model\test\user();
    </code>
    <a id="basicMethod">3) Qii 基本功能：</a>
    <code>
        Qii\Instance::Instance(className, param1, param2, param3[,...]); === ($class = new className(param1, param2,
        param3[,...]));
        Qii\Load::load(className)->method(); === $class->method();
        Qii\Import::requires(fileName); 如果指定的文件无法找到则会在get_include_path()和站点配置文件的[path]的目录中去搜索，直到搜索到一个则停止。
        Qii\Import::includes(fileName); == include(fileName);
        Qii::setPrivate($key, $value);保存到私有变量 $_global[$key]中, $value可以为数组,如果是数组的话会将数组合并到已经存在$key的数组中去。
        Qii::getPrivate($key, $index);获取$_global[$key][$index]的值
        Qii::setError($condition, $code, $argv1, $argv2, ...);
        检查$condition是否成立，成立就没有错，返回false，否则有错，返回true并将错误信息，详细代码错误$code详情见<?php echo Qii::getPrivate('qii_sys_language'); ?>
        。
    </code>
    <a id="supportMultDomain"> 4) 多域名支持：</a>
    <code>
        开启多域名支持，不同域名可以访问不同目录中的controller，在app.ini中的common下添加以下内容，注意：hosts中的内容会覆盖网站中对应的配置
        hosts[0.domain] = test.xxx.wang
        hosts[0.ext] = test

        hosts[1.domain] = admin.xxx.wang
        hosts[1.ext] = admin
    </code>
    <a id="modle"> 5) 数据库使用示例：</a>
    <code>
        1、创建配置文件：configure/istudy.istudy_user.config.php，你可以设置好数据库配置文件或者在这里<a
                href="<?= $this->_request->getFullUrl('main') . '?url=' . urlencode($this->_request->getFullUrl('database/creator')); ?>"><b>生成</b></a>配置文件并下载保存到本地目录。
        <?php highlight_file('configure/istudy.istudy_user.config.php') ?>
        namespace Model;
        use \Qii\Model;

        class user extends Model
        {
        public function __construct()
        {
        parent::__construct();
        }

        public function saveUserInfo()
        {
        $user = (new \Qii\Driver\Easy())->_initialize();
        //设置数据表的主键，保存的时候会自动验证指定数据是否已经存在
        $user->setPrivateKey(array('email'));
        //设置规则
        $user->setRules(new \Qii\Driver\Rules(\Qii\Import::includes('configure/istudy.istudy_user.config.php')));
        $user->nickname = 'antsnet';
        $user->sex = 1;
        $user->email = 'antsnet4@163.com';
        $user->password = 'A123456a';
        $user->add_time = time();
        $user->update_time = time();
        $user->status = 0;
        //此处返回response对象
        $response = $user->_save();

        if($response->isError())
        {
        return $response->getErrors();
        }
        return array('uid' => $response->getResult('_result'));
        }

        /**
        * 获取用户信息
        */
        public function userInfo($email)
        {
        return $this->db->getRow('SELECT * FROM istudy_user WHERE email = "'.$this->db->setQuote($email).'"');
        //或者
        return $this->db->where(array('email' => $email))->selectOne('istudy_user');
        //或者使用以下代码
        $user = (new \Qii\Driver\Easy('istudy_user'))->_initialize();
        //设置规则
        $user->setRules(new \Qii\Driver\Rules(\Qii\Import::includes('configure/istudy.istudy_user.config.php')));
        //操作的时候会根据规则自动验证对应字段是否符合指定规则，不符合就不做查询操作
        $response = $user->_getRowByEmail('antsnet@163.com');
        if($response->isError())
        {
        return array('users' => array(), 'error' => $user->getErrors());
        }
        return array('users' => $response->getResult('_result'));
        }
        /**
        * 登录
        */
        public function login($email, $password)
        {
        $user = (new \Qii\Driver\Easy('istudy_user'))->_initialize();
        //设置规则
        $user->setRules(new \Qii\Driver\Rules(\Qii\Import::includes('configure/istudy.istudy_user.config.php')));
        $user->setPrivateKey(array('email'));
        $user->email = $email;
        $response = $user->_exist();
        if($response->isError())
        {
        return array('login' => false, 'error' => $response->getErrors());
        }
        $data = $response->getResult();
        if($data['password'] != md5($password))
        {
        return array('login' => false, 'error' => 'invalid password');
        }
        return array('login' => true, 'res' => $data);
        }

        public function update($email, $password)
        {
        $user = (new \Qii\Driver\Easy('istudy_user'))->_initialize();
        //设置规则
        $user->setRules(new \Qii\Driver\Rules(\Qii\Import::includes('configure/istudy.istudy_user.config.php')));
        $user->setPrivateKey(array('email'));
        $user->email = $email;
        $user->func('md5', 'password', $password);

        $response = $user->_update();
        if($response->isError())
        {
        return array('update' => false, 'msg' => $response->getErrors());
        }
        return array('update' => true);
        }
        public function remove($email)
        {
        $user = (new \Qii\Driver\Easy('istudy_user'))->_initialize();
        //设置规则
        $user->setRules(new \Qii\Driver\Rules(\Qii\Import::includes('configure/istudy.istudy_user.config.php')));
        $user->setPrivateKey(array('email'));
        $user->email = $email;

        $response = $user->_remove();
        if($response->isError())
        {
        return array('remove' => false, 'msg' => $response->getErrors());
        }
        return array('remove' => true);
        }
        }
        使用方法：
        namespace controller;
        use \Qii\Controller_Abstract;
        class test extends Controller_Abstract
        {
        //启用模板引擎支持
        protected $enableView = true;
        //启用数据库支持
        protected $enableModel = true;
        //修改默认模板引擎
        protected $viewType = array('engine' => 'include');
        public function __construct()
        {
        parent::__construct();
        }

        public function indexAction()
        {
        $this->_model->getRow('SELECT * FROM user WHERE email = "antsnet@163.com"');
        print_r($this->_load->model('user')->saveUserInfo());
        print_r($this->_load->model('user')->userInfo('antsnet@163.com'));
        print_r($this->_load->model('user')->login('antsnet@163.com', 'A123456a'));
        print_r($this->_load->model('user')->update('antsnet@163.com', 'A123456a'));
        print_r($this->_load->model('user')->remove('antsnet@163.com'));
        }
        }
    </code>
    <a id="view"> 6) View的支持</a>
    <code>
        view支持smarty及php
        use \Qii\Controller_Abstract;
        class index extends Controller_Abstract
        {
        //启用view，在调用parent::__construct()时自动初始化view
        protected $enableView = true;
        //启用database在调用parent::__construct()时自动初始化database
        protected $enableModel = true;
        //修改默认模板引擎
        protected $viewType = array('engine' => 'include');
        public function __construct()
        {
        parent::__construct();//默认是app.ini中的view[engine]，你可以在此处选择是用include或者require，只要将参数默认传给enableView即可
        $this->_view->display('tpl'); //$this->view即为使用的模板引擎
        }
        }
    </code>
    <a id="controller"> 7) Controller的使用</a>
    <code>
        namespace controller;
        use \Qii\Controller_Abstract;
        class test extends Controller_Abstract
        {
        //为了避免Controller逻辑过于复杂，可以将Action拆到单独的文件
        //当在调用dummy方法的时候会自动执行actions\dummy中的execute方法
        public $actions = array(
        "dummy" => "actions\dummy",
        );
        public function __construct()
        {
        parent::__construct();
        }
        public function indexAction()
        {
        //转发到\controller\test的indexAction方法上
        $this->dispatch('test', 'index');
        //执行结束后再转发
        $this->forward('test', 'index');
        }
        //在执行parent::__construct()的时候会自动调用，如果return false即直接退出程序
        protected function beforeRun()
        {
        return true;
        }
        /**
        * 执行完dispatch后调用
        */
        protected function afterRun()
        {
        }
        }
    </code>
    <a id="cache"> 8) Cache支持</a>
    <code>
        Controller中使用Cache
        namespace controller;
        use \Qii\Controller_Abstract;
        class cache extends Controller_Abstract
        {
        public function __construct()
        {
        parent::__construct();
        }
        public function cacheTest()
        {
        $cache_id = 'cache_test';
        //文件缓存
        $this->setCache('file', array('path' => 'tmp'));
        //Memcache缓存
        $this->setCache('memcache', array('servers' => array( array('host'=>'127.0.0.1','port'=>11211) )
        ,'life_time'=>600));
        //xcache
        $this->setCache('xcache', array('life_time'=>600));
        //缓存内容
        $this->_cache->set($cache_id, 'cache内容');
        //redis缓存
        $this->setCache('redis', array('servers' => array('127.0.0.1.6379')));
        $this->_cache->set($cache_id, array('cache' => 'cache 内容'));
        //获取缓存内容
        $this->_cache->get($cache_id);
        //移除缓存
        $this->_cache->remove($cache_id);
        }
        }

        Model中使用
        namespace Model;
        use \Qii\Model;
        class cache extends Model
        {
        public function __construct()
        {
        parent::__construct();
        }
        public function cacheTest()
        {
        $cache_id = 'cache_test';
        //文件缓存
        $this->setCache('file', array('path' => 'tmp'));
        //Memcache缓存
        $this->setCache('memcache', array('servers' => array( array('host'=>'127.0.0.1','port'=>11211) )
        ,'life_time'=>600));
        //xcache
        $this->setCache('xcache', array('life_time'=>600));
        //缓存内容
        $this->_cache->set($cache_id, 'cache内容');
        //获取缓存内容
        $this->_cache->get($cache_id);
        //移除缓存
        $this->cache->remove($cache_id);
        }
        }
    </code>
</code>
<a id="download">下载</a>
<code>
    测试阶段暂时不提供下载

</code>
</pre>
<?php include(Qii_DIR . '/view/footer.php'); ?>
</body>
</html>