# 新版Qii

使用方法：

1、创建项目
   通过命令行进入当前目录，并执行：php -q _cli.php create=yes workspace=../project cache=tmp useDB=1
	Command line usage:
	>php -q _cli.php create=yes workspace=../project cache=tmp useDB=1
	* create: is auto create default:yes;
	* workspace: workspace
	* cache : cache dir
	* useDB : use db or not 
	程序将自动创建工作目录，并生成首页及配置相关文件。设置好Web网站目录，开启.htaccess即可直接访问。
	相关的配置文件见 configure/app.ini及configure/db.ini文件

2、框架的使用
	1) 命令行运行程序
		仅支持GET方法
		1. normal模式
		php -q index.php control=index/action=home/id=100
		php -q index.php control=plugins/action=index/page=2

		2. middle模式
		php -q index.php control/index/action/home/id/100
		php -q index.php control/plugins/action/index/page/2

		3. short模式
		php -q index.php index/home/100
		php -q index.php plugins/page/2.html
	2) 自动加载类
		new Controller\User(); === require("Controller<?=DS?>User.php"); new Controller\User();
		new Model\User(); === require("Model<?=DS?>User.php"); new Model\User();
		new Model\Test\User(); === require("Model<?=DS?>Test<?=DS?>User.php"); new Model\Test\User();

	3) Qii 基本功能：
		Qii::Instance(className, param1, param2, param3[,...]); === ($class = new className(param1, param2, param3[,...]));
		Qii::load(className)->method(); === $class->method();
		Qii::import(fileName); 如果指定的文件无法找到则会在get_include_path()和站点配置文件的[path]的目录中去搜索，直到搜索到一个则停止。
		Qii::includes(fileName); == include(fileName);
		Qii::setPrivate($key, $value);保存到私有变量 $_global[$key]中, $value可以为数组,如果是数组的话会将数组合并到已经存在$key的数组中去。
		Qii::getPrivate($key, $index);获取$_global[$key][$index]的值
		Qii::setError($condition, $code, $argvs); 检查$condition是否成立，成立就没有错，返回false，否则有错，返回true并将错误信息，详细代码错误$code详情见<?php echo Qii::getPrivate('qii_sys_language');?>。
	4) 多域名支持：
		开启多域名支持，不同域名可以访问不同目录中的controller，在app.ini中的common下添加以下内容，注意：hosts中的内容会覆盖网站中对应的配置
        hosts[0.domain] = test.xxx.wang
        hosts[0.ext] = test

        hosts[1.domain] = admin.xxx.wang
        hosts[1.ext] = admin
	5) Module用法示例：
		第一步，创建一个user的model
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
		第二步，使用user_model
		class user_controler extends \Qii\Controller\Abstract
		{
			public function __construct()
			{
					$this->Qii('Model');
					$userClass = new user_module();
					$userInfo = $userClass->userInfo(10);

					或
					$this->Qii("user_module")->userInfo(10);
					或
					$this->Qii("user_module");
					$this->user_module->userInfo(10);
			}
		}
	6) ORM的使用示例：
		第一步，创建表对应的ORM模型
		/**
		 * User ORM 模型
		 * @author Zhu Jinhui 2015-02-13 11:26
		 *
		 */
		class User extends Tables
		{
			public function getTableName()//返回User对应的数据表
			{
				return 'istudy_user';
			}
			public function getRelationMap()//返回alias对应的字段名
			{
				return array('id' => 'uid', 'email' => 'email', 'nick' => 'nickname');
			}
			public function getValidateSaveFields()//保存数据需要验证的字段
			{
				return array('email', 'password');
			}
			public function getValidateRules()//验证规则
			{
				return array('email' => array('email' => true), 'password' => array('password' => true, 'length' => 32));
			}
			public function getInvalidMessage()//验证不通过返回的消息内容
			{
				return array();
			}
		}
		第二步，创建User Model：
		class user_model extends Model
		{
		    public function __construct()
		    {
		        parent::__construct();
		    }
		    /**
		     * 注册
		     * @param String $email
		     * @param String $password
		     * @return Array
		     */
		    public function register($email, $password)
		    {
		    	$data = array();
		    	if(!$email || !$password)
		    	{
		    	    $data['code'] = 1;
		    	    $data['error'] = array('result' => '参数不正确');
		    		return $data;
		    	}
		    	$user = new User();
		    	$user->email = $email;
		    	$user->password = md5($password);
		    	$user->active_code = substr(md5(uniqid(rand(), TRUE)), -6);
		    	$user->add_time = time();
		    	$user->update_time = time();
		    	
		    	$user->setPrivateKey('email');
		    	$isExists = $user->isExits();
		    	
		    	if($isExists)
		    	{
		    	    $data['code'] = 10001;
		    	    $data['data'] = $isExists;
		    	}
		    	else
		    	{
		    	   $data['code'] = 0;
		    	   $data['uid'] = $user->execSave();
		    	   if($user->getTablesError())
		    	   {
		    	       $data['error'] = $user->getTablesError();
		    	   }
		    	}
		    	return $data;
		    }
		    
		    public function login($email, $password)
		    {
		    	$data = array();
		    	if(!$email || !$password)
		    	{
		    		return $data;
		    	}
		    	$user = new User();
		    	$user->email = $email;
		    	//$user->password = md5($password);
		    	$userInfo = $user->isExits();
		    	if($userInfo['uid'])
		    	{
		    	    if($userInfo['password'] != md5($password))
		    	    {
		    	        $data['code'] = 1;
		    	        $data['msg'] = '密码不正确';
		    	    }
		    	    else
		    	    {
		    	       $data['code'] = 0;
		    	       $cookie['uid'] = $userInfo['uid'];
		    	       $cookie['email'] = $userInfo['email'];
					   $data['cookie'] = $cookie;
		    	    } 
		    	}
		    	return $data;
		    }
		}
		第三步，使用user_model：
		
		class index_controller extends \Qii\Controller\Abstract
		{
			public function __construct()
			{
				$user = new user_model();
				$status = $user->login('email@test.com', '119328118');
				if($status['code'] === 0) echo '登录成功';
			}
		}
	7) View的支持
		view支持smarty及php
		class index_controller extends \Qii\Controller\Abstract
		{
			public function __construct()
			{
				$this->enableView();//默认是app.ini中的view[engine]，你可以在此处选择是用include或者require，只要将参数默认传给enableView即可
				$this->view->display('tpl'); //$this->view即为使用的模板引擎
			}
		}
	8) Controller的使用
	class test_controller extends \Qii\Controller\Abstract
	{
		//为了避免Controller逻辑过于复杂，可以将Action拆到单独的文件
		//当在调用dummy方法的时候会自动执行actions/dummy_action.php中的execute方法
		public $actions = array(
				"dummy" => "actions/dummy_action.php",
		);
		public function __construct()
		{
			parent::__construct();
		}
	}
	9) Cache支持
	Controller中使用Cache
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
			//redis缓存
			$this->setCache('redis', array('servers' => array('127.0.0.1.6379')));
			$this->cache($cache_id, array('cache' => 'cache 内容'));
			//获取缓存内容
			$this->getCache($cache_id);
			//移除缓存
			$this->cache->remove($cache_id);
		}
	}
	
	Model中使用
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