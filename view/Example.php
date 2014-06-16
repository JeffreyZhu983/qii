<html>
<head>
<title>Welcome to Qii > Example</title>
<?php
	include(dirname(__FILE__) . DS .  'style.php');
?>
</head>
<body>

<h1><a href="<?=$href;?>">Welcome to Qii!</a> >Example</h1>
<p>
	<ul>
	<li><a href="#createProject">自动生成项目</a></li>
	<li><a href="#runInCommand">命令行运行程序</a></li>
	<li><a href="#__autoload">自动加载用法</a></li>
	<li><a href="#Qii">Qii基本功能</a></li>
	<li><a href="#Domains">多域名支持</a></li>
	<li><a href="#Model">Model用法：</a></li>
	<li><a href="#MultDB">连接多数据库：</a></li>
	<li><a href="#View">View用法：</a></li>
	<li><a href="#Router">路由自动转发：</a></li>
	<li><a href="#Controller">Controller用法：</a></li>
	<li><a href="#Cache">Cache用法：</a></li>
	<li><a href="#Language">语言包用法：</a></li>
	<li><a href="#Http">Http状态设置</a></li>
	<li><a href="#XML">XML用法：</a></li>
	<li><a href="#Secode">图片验证码：</a></li>
	<li><a href="#getPath">获取当前页路径</a></li>
	<li><a href="#download">下载地址</a></li>
	</ul>
</p>
<p id="createProject">自动生成项目:</p>
<code>
<pre>
进入Qii框架目录，cmd.php为生成项目文件的主程序，使用方法，进入命令行，执行以下命令：
php -q cmd.php create=yes workspace="../testproject" dbhost=localhost dbname=testproject dbuser=root dbpassword=test charset=UTF8

  1.create: 是否自动创建 
  2.workspace: 程序存放的目录
  3.dbhost: 数据库服务器
  4.dbname: 数据库名称, 如果用户为空就不创建数据库配置文件
  5.dbuser: 数据库用户
  6.dbpassword: 数据库密码
  7.charset: 数据库字符集

</pre>
</code>
<p>入口文件 index.php 或者其他文件</p>
<code>
<pre>
入口文件 index.php
<?php highlight_file(Qii_DIR . '/example/helper.php');?>

configure/site.xml文件内容
<?php highlight_file(Qii_DIR . '/example/site.xml');?>

configure/db.config.php文件
<?php highlight_file(Qii_DIR . '/example/db.config.php');?>
</pre>
</code>

<p id="runInCommand">命令行运行程序</p>
<code>
<pre>
仅支持GET方法
1) normal模式
php -q index.php controller=index/action=home/id=100
php -q index.php controller=plugins/action=index/page=2

2) middle模式
php -q index.php controller/index/action/home/id/100
php -q index.php controller/plugins/action/index/page/2

3) short模式
php -q index.php index/home/100
php -q index.php plugins/page/2.html
</pre>
</code>

<p><a id="__autoload">自动加载类</a></p>
<code>
<pre>
new user_controller(); === require("controller<?=DS?>user.controller.php"); new user_controller();

new user_model(); === require("model<?=DS?>user.model.php"); new user_model();

new user_test_model(); === require("model<?=DS?>test<?=DS?>user.test.model.php"); new user_test_model();

new uuid_sys_plugin(); === require("<?=Qii_DIR.DS;?>plugin<?=DS?>uuid.plugin.php"); new uuid_sys_plugin();

new timezone_sys_helper() === require("<?=Qii_DIR.DS;?>helper<?=DS?>timezone.helper.php"); new timezone_sys_helper();
</pre>
</code>

<p><a id="Qii">Qii 基本功能：</a></p>
<code>
<pre>
Qii::Instance(className, param1, param2, param3[,...]); === ($class = new className(param1, param2, param3[,...]));

Qii::load(className)->method(); === $class->method();

Qii::requireOnce(fileName); 如果指定的文件无法找到则会在get_include_path()和站点配置文件的[path]的目录中去搜索，直到搜索到一个则停止。

Qii::setPrivate($key, $value);保存到私有变量 $_global[$key]中, $value可以为数组,如果是数组的话会将数组合并到已经存在$key的数组中去。

Qii::getPrivate($key, $index);获取$_global[$key][$index]的值

Qii::setError($condition, $code, $argvs); 检查$condition是否成立，成立就没有错，返回false，否则有错，返回true并将错误信息，详细代码错误$code详情见<?php echo Qii::getPrivate('qii_sys_language');?>。
</pre>
</code>

<p><a id="Domains">多域名支持：</a></p>
<code>
<pre>
	配置不同域名访问不同controller目录中的文件，默认访问<controller>标签下的内容
	* 增加多域名支持，在网站配置文件中以下内容(与status标签同一级)：
	<hosts>
		<host>
			<domain>域名</domain>
			<ext>指向的目录</ext>
		</host>
		......
	</hosts>
</pre>
</code>
<p><a id="Model">Model用法：</a></p>
<code>
<pre>
class user_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}
	public function userInfo($uid)
	{
		return $this->getRow("SELECT * FROM user WHERE uid = '{$uid}'");
	}
}


<font color="red">调用user_model的方法:</font>

$userClass = new user_model();
$userInfo = $userClass->userInfo(10);

<font color="red">OR </font>
$this->Qii("user_model")->userInfo(10);
<font color="red">OR </font>
$this->Qii("user_model");
$this->user_model->userInfo(10)

<font color="red">备注：需要在调用model的程序中先加载Model，如果是在controller中可以在构造函数中添加$this->Qii("model");</font>
</pre>
</code>

<code>
<pre>
Qii::instance('Model');
Qii::load('Model')->getRow("SELECT * FROM user WHERE uid = 1");
</pre>
</code>
<code>
<pre>
class index_controller extends Controller 
{
	/**
	 * 定义需要加载的模块
	 *
	 * @var Array
	 */
	//protected $core = array('view', 'model');
	
	
	public function __construct()
	{
		parent::__construct();
		$this->Qii('model');
		$this->Qii('view');
	}
	public function info()
	{
		$userInfo = $this->model->where('uid=2626')->limit(1)->select('user');
		
		//查询所有名字为 "朱金辉"的
		$userInfo = $this->model->where("user_name LIKE '%朱金辉%'")->selectAll('user');
		//更新uid=2626的用户的last_ip = 192.168.1.1
		$this->model->set(array('last_ip' => '192.168.1.1'))->where('uid=2626')->update('user');
	}
}

</pre>
</code>

<p><a id="MultDB">连接多数据库</a></p>
<code>
<pre>
class db_model extends Model
{
	public function __construct($dbFile = 'db')
	{
		//parent::__construct();
		//替换成连接另外一个数据库
		$db = Qii::loadFile("configure/" . $dbFile . ".config.php");
		Qii::setPrivate('qii_site_'. $dbFile, $db);
		parent::__construct('qii_site_'. $dbFile);
	}
}

class multi_model
{
	public $db;
	public $livedb;
	public function __construct()
	{
		$this->db = new db_model();
		$this->livedb = new db_model("livedb");
		return $this;
	}
}

class multidb_controller extends Controller 
{
	public $muti;
	public function __construct()
	{
		$this->Qii("model");
		$this->multi = new multi_model();
	}
	public function index()
	{
		$rs = $this->multi->db->setQuery("SELECT * FROM user WHERE uid = 2626");
		Qii::dump(array('DB' => 'Default DB', 'data' => $rs->fetch()));
		
		$rs = $this->multi->livedb->setQuery("SELECT * FROM uchome_space WHERE uid = 2626");
		Qii::dump(array('DB' => 'live DB', 'data' => $rs->fetch()));
	}
}
</pre>
</code>
<p><a id="View">View:</a></p>
<code>
<pre>
View默认功能由Smarty提供，可选择Smarty及PHP默认的include或require方式，可以由$this->setView(view);来指定view方法。
include和require默认支持方法：
$this->assign($key, $value);
$this->display('tpl.php');

使用方法：
<font color="green">Qii::load('View')->assign('user', $user);</font>
Qii::load('View')->display('view.html');


<font color="red">OR</font>
class index_controller extends Controller 
{
	public $URL;
	public $security;
	public function __construct()
	{
		<font color="green">$this->Qii('view');</font>
	}
	public function index()
	{
		$this->view->assign('user', $user);
		$this->view->display('user.html');
	}
}
<font color="red">OR 使用Smarty以外的View方法：</font>
class index_controller extends Controller 
{
	public $URL;
	public $security;
	public function __construct()
	{
		
	}
	public function index()
	{
		<font color="green">$this->setView('smarty');</font>//Smarty方法
		<font color="green">$this->setView('inlcude');</font>//inlcude方法
		<font color="green">$this->setView('reqiure');</font>//require方法
		
		$this->view->assign('user', $user);
		$this->view->display('user.html');
	}
}
</pre>
</code>

<p><a id="Control">Controller Class</a></p>
<code>
<pre>
class index_controller extends Controller 
{
	/**
	 * 定义需要加载的模块
	 *
	 * @var Array
	 */
	public function __construct()
	{
		parent::__construct();
		$this->Qii('model');
		$this->Qii('view');
	}
	public function index()
	{
		/*$userClass = new user_model();
		$userInfo = $userClass->userInfo(2626);
		*/
		$userInfo = $this->model->where('uid=2626')->limit(1)->select('user');
		
		//查询所有名字为 "朱金辉"的
		$userInfo = $this->model->where("user_name LIKE '%朱金辉%'")->selectAll('user');
		$this->model->set(array('last_ip' => '192.168.1.1'))->where('uid=2626')->update('user');
		$this->model->set(array('last_ip' => '127.0.0.1'))->where('uid=2626')->update('user');
		
		$hrefOne = Qii::load('Router')->URI(array('controller' => 'System', 'action' => 'fileList'));
		$hrefTwo = Qii::load('Router')->URI(array('controller' => 'System', 'action' => 'checkEnvironment'));
		$hrefThree = Qii::load('Router')->URI(array('controller' => 'System', 'action' => 'Example'));
		$hrefFour = Qii::load('Router')->URI(array('controller' => 'System', 'action' => 'printURL'));
		$hrefFive = Qii::load('Router')->URI(array('controller' => 'plugins'));
		
		$href = Qii::load('Router')->URI(array('controller' => 'System'));
		$this->view->assign('System', array(
													"<a href=\"{$hrefOne}\">载入的文件列表</a>", 
													"<a href=\"{$hrefTwo}\">检查系统需求</a>", 
													"<a href=\"{$hrefThree}\">帮助文件</a>",
													"<a href=\"{$hrefFour}\">示例链接</a>",
													"<a href=\"{$hrefFive}\">测试代码</a>"
										)
						);
		/*
		$this->view->assign('_queryTimes', $userClass->querySQL('_queryTimes'));
		$this->view->assign('_querySeconds', $userClass->querySQL('_querySeconds'));
		
		OR
		
		$this->view->assign('_queryTimes', Qii::getPrivate('model', '_queryTimes'));
		$this->view->assign('_querySeconds', Qii::getPrivate('model', '_querySeconds'));
		*/
		$this->view->assign('_queryTimes', Qii::getPrivate('model', '_queryTimes'));
		$this->view->assign('_querySeconds', Qii::getPrivate('model', '_querySeconds'));
		
		$this->view->assign('useMemory', Qii::useMemory());
		Benchmark::set('site', true);
		$this->view->assign('Benchmark', Benchmark::Caculate('site'));
		$this->view->display('index.php');
	}
	public function getYoutubeJson()
	{
		/**
		 * 
		 * Get Youtube BY JSON (alt=json)
		 * @var unknown_type
		 */
		Qii::requireOnce(Qii_DIR . '/core/HttpClient.php');
		$HttpClient = new HttpClient();
		//$youTuBeFeed = $HttpClient->quickGet('http://gdata.youtube.com/feeds/api/videos?q='.urlencode('刘德华|志明與春嬌|楊千嬅|余文樂'). '&alt=json');
		$youTuBeFeed = (json_decode(file_get_contents('tmp/top_rated')));
		
		$youTuBeFeedArray = (json_decode($youTuBeFeed));
		$feedList = array();
		foreach ($youTuBeFeedArray->feed->entry AS $feed)
		{
			$list = array();
			$list['category'] = $feed->category[1]->label;
			$t = "\$t";
			$list['keyword'] = array();
			for($i = 2; $i < sizeof($feed->category); $i++)
			{
				$list['keyword'][] = $feed->category[$i]->term;
			}
			$list['title'] = $feed->title->$t;
			$list['content'] = $feed->content->$t;
			$list['link'] = $feed->link[0]->href;
			$feedList[] = $list;
		}
		print_r($feedList);
	}
	public function getYoubebeXML()
	{
		/*
		Qii::requireOnce(Qii_DIR . '/core/HttpClient.php');
		
		$HttpClient = new HttpClient();
		$HttpClient->debug = true;
		$youTuBeFeed = $HttpClient->quickGet('http://gdata.youtube.com/feeds/api/videos?q='.urlencode('刘德华|志明與春嬌|楊千嬅|余文樂'));	
		*/
		$youTuBeFeed = file_get_contents('tmp/youtube.xml');
		
		Qii::load("XML")->setData($youTuBeFeed);
		$youTuBeFeedArray = Qii::load('XML')->XML2Array();
		foreach($youTuBeFeedArray['feed']['entry'] AS $feed)
		{
			$list = array();
			$list['category'] = $feed['category']['1 attr']['label'];
			$list['keyword'] = array();
			for($i = 2; $i < sizeof($feed['category'])/2; $i++)
			{
				$list['keyword'][] = trim($feed['category'][$i . ' attr']['term']);
			}
			$list['title'] = trim($feed['title']);
			$list['content'] = trim($feed['content']);
			$list['link'] = $feed['link']['0 attr']['href'];
			$feedList[] = $list;
		}
		print_r($feedList);
	}
	public function userList()
	{
		Benchmark::set('site', true);
		$userClass = new user_model();
		$data = $userClass->userList();
		$this->view->assign('data', $data['data']);
		$this->view->assign('page', $data['page']);
		$this->view->assign('_queryTimes', $userClass->querySQL('_queryTimes'));
		$this->view->assign('_querySeconds', $userClass->querySQL('_querySeconds'));
		$this->view->assign('useMemory', Qii::useMemory());
		$this->view->assign('Benchmark', Benchmark::Caculate('site'));
		$this->view->display('users.php');
	}
	public function qr()
	{
		$qr = new qr_sys_plugin();
		$qr->out("朱金辉 测试文件");
	}
}
</pre>
</code>
<p id="Cache">Cache:</p>
<code>
<pre>
<font color="red">Controller中使用Cache</font>
class cache_controller extends Controller
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
		$this->setCache('memcache', array('servers' => array( array('host'=>'127.0.0.1','port'=>11211) ) ,'life_time'=>600));
		//xcache
		$this->setCache('xcache', array('life_time'=>600));
		//缓存内容
		$this->cache($cache_id, 'cache内容');
		//获取缓存内容
		$this->getCache($cache_id);
		//移除缓存
		$this->cache->remove($cache_id);
	}
}

<font color="red">Model中使用</font>
class cache_model extends Model
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
		$this->setCache('memcache', array('servers' => array( array('host'=>'127.0.0.1','port'=>11211) ) ,'life_time'=>600));
		//xcache
		$this->setCache('xcache', array('life_time'=>600));
		//缓存内容
		$this->cache($cache_id, 'cache内容');
		//获取缓存内容
		$this->getCache($cache_id);
		//移除缓存
		$this->cache->remove($cache_id);
	}
}
</pre>
</code>
<p><a id="Language">语言包的使用</a></p>
<code>
<pre>
step 1:在网站根目录创建一个语言包目录，结构如下
i18n/
     language.php
	 zh-CN 中文
	 zh-TW 繁体
	 ...

language.php中的内容如下
return 'zh-CN';//zh-CN为语言包的名称，在加载的时候会自动加载此目录中的语言包

<font color="red">Controller中使用语言包</font>
$this->Qii('Language');
$this->language->loadDefault('message');//加载语言包 zh-CN/message.php 或 zh-TW/message.php
print_r($this->language->gettext('data %s does exists', 'row 1'));//获取语言包中"data %s does exists"对应的语言,gettext的方法类似于sprint_f;

<font color="red">Model中使用语言包</font>
$this->setLanguage();
$this->language->loadDefault('message');//加载语言包
print_r($this->language->gettext('data %s does exists', 'row 1'));//获取语言包中"data %s does exists"对应的语言,gettext的方法类似于sprint_f;

</pre>

<p><a id="HTTP">Http status code</a></p>
<code>
<pre>
Qii::load('Status')->setStatus(200);
Qii::load('Status')->setStatus(404);
</pre>
</code>


<p><a id="XML">XML Class:</a></p>
<code>
<pre>
Qii::Instance('XML'); 
Qii::load("XML")->setXml('configure/site.config.php');

<font color="green">将XML文件解析成Array</font>
$data =Qii::load("XML")->XML2Array();
print_r($data);

<font color="green">将Array转换成XML文件</font>
echo Qii::load("XML")->array2XML($data);

</pre>
</code>

<p><a id="Throw">Throw Exception:</a></p>
<code>
<pre>
throw new QiiException('测试哈哈');
</pre>
</code>

<p><a id="Secode">Seccode:</a></p>
<code>
<pre>
$seccodePlugin = new seccode_sys_plugin();
$seccodePlugin->prepare();

<p>通过以下方法获取验证码值</p>
<font color="green">$code = $seccodePlugin->code;</font>
$seccodePlugin->display();
<p></p>
<img src="<?php echo Qii::load('Router')->getPath() . '/Qii';?>/seccode.jpg" />
</pre>
</code>

<p><a name="getPath">获取当前页路径信息:</a></p>
<code>
<pre>
<font color="blue">当前页面地址</font>
echo Qii::load('Router')->getCurrentURL();
<font color="blue">当前路径</font>
echo Qii::load('Router')->getPath();
</pre>
</code>
<p><a name="Router">Router:</a></p>
<code>
<pre>
设置路由
在index.php中添加：
Qii::setRouter("configure/router.config.php");

configure/router.config.php文件
<?php highlight_file(Qii_DIR . '/example/router.config.php');?>
</pre>
</code>

<p id="download">下载地址:</p>
<code>
<pre>
无功能插件版本
<a href="/Qii_clear_version.zip">Qii_clear_version.zip</a>

包含功能插件版本
<a href="javascript:;">Qii_include_plugins_version.zip</a>暂停下载
</pre>
</code>

<p>Template AS blew:</p>
<code>
<pre>

</pre>
</code>

<?php
	include(dirname(__FILE__) . DS  . 'footer.php');
?>

</body>
</html>