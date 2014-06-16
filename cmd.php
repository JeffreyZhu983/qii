<?php
ini_set("display_errors", "On");
/**
 * 错误模式
 */
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
/**
 * 自动生成目标目录
 * 
 * >php -q cmd.php create=yes workspace="../iwallpaper" dbhost=localhost dbname=iwallpaper dbuser=root dbpassword=119328118 charset=UTF8
 * create: 是否自动创建 
 * workspace: 程序存放的目录
 * dbhost: 数据库服务器
 * dbname: 数据库名称, 如果用户为空就不创建数据库配置文件
 * dbuser: 数据库用户
 * dbpassword: 数据库密码
 * charset: 数据库字符集
 */
class cmd
{
	public $dir = array("configure", "controller", "model", "view", "tmp");
	
	public 	function __construct($args)
	{
		$param = $this->parseArgvs($args);
		if (sizeof($param) < 1)
		{
			echo "Command line useage:\n
>php -q cmd.php create=yes workspace=../iwallpaper cache=tmp dbhost=localhost dbname=iwallpaper dbuser=root dbpassword=119328118 charset=UTF8\n
 * create: is auto create default:yes; \n
 * workspace: workspace\n
 * dbhost: database host\n
 * dbname: database\n
 * dbuser: database user's name\n
 * dbpassword: database user's password\n
 * charset: database connecting charset\n";
			return;
		}
		if($param['create'] == 'yes')
		{
			if($this->workspace($param['workspace']))
			{
				//创建目录工作区目录
				foreach ($this->dir AS $d)
				{
					$path = $param['workspace'] . '/' . $d;
					if(!is_dir($path))
					{
						$date = date('Y-m-d H:i:s');
						echo "create path {$path} success.\n";
						mkdir($path, 777, true);
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
						file_put_contents($path .'/.htaccess', join("\n", $htaccess));
					}
					else 
					{
						echo "{$path} does exist.\n";
					}
				}
				//拷贝网站配置文件site.xml到项目目录
				copy("example/site.xml", $param['workspace'] . '/configure/site.xml');
				//生成数据库文件
				//--生成首页文件
				//--获取文件的相对路径
				$realPath = $this->getRealPath($param['workspace']);
				echo "Real path " . $realPath . "\n";
				$QiiPath = $this->getRelatePath($realPath . "/index.php", dirname(__FILE__) . "/Qii.php");
				echo "Qii path " . $QiiPath . "\n";
				$cache = $param['cache'];
				if(empty($param['cache'])) $cache = 'tmp';
				$date = date("Y/m/d H:i:s");
				$indexPage = array();
				$indexPage[] = "<?php";
				$indexPage[] = "/**";
				$indexPage[] = " * This is index page auto create by Qii, don't delete";
				$indexPage[] = " * ";
				$indexPage[] = " * @author Jinhui.zhu	<jinhui.zhu@live.cn>";
				$indexPage[] = " * @version  \$Id: index.php,v 1.1 {$date} Jinhui.Zhu Exp $";
				$indexPage[] = " */";
				$indexPage[] = "require(\"{$QiiPath}\");";
				$indexPage[] = "Qii::setCachePath(\"{$cache}\");";
				$indexPage[] = "Qii::setXpath('configure/site.xml', 'helper');";
				if(!empty($param['dbname']) && !empty($param['dbuser']))
				{
					$db = array('readOrWriteSeparation' => true,//是否读写分离
								'driven' => 'mysql', 
								'master' => array('host' => $param['dbhost'], 'user' => $param['dbuser'], 'password' => $param['dbpassword'], 'db' => $param['dbname']), 
								'slave' => array(
											array('host' => $param['dbhost'], 'user' => $param['dbuser'], 'password' => $param['dbpassword'], 'db' => $param['dbname'])	
								),
								'charset'=> $param['charset']);
					$db_config = file_get_contents('example/db.config.php');
					$db_config = str_replace("{dbhost}", $param['dbhost'], $db_config);
					$db_config = str_replace("{dbuser}", $param['dbuser'], $db_config);
					$db_config = str_replace("{dbpassword}", $param['dbpassword'], $db_config);
					$db_config = str_replace("{dbname}", $param['dbname'], $db_config);
					$db_config = str_replace("{charset}", $param['charset'], $db_config);

					file_put_contents($param['workspace'] . "/configure/db.config.php", $db_config);
					$indexPage[] = "Qii::setDB(\"configure/db.config.php\");";
				}
				$indexPage[] = "Qii::dispatch();";
				$indexPage[] = "?>";
				if(!file_exists($realPath . "/index.php"))
				{
					//如果文件不存在就写入
					file_put_contents($realPath . "/index.php", join("\n", $indexPage));
				}
				//写入首页controller
				if(!file_exists($realPath . "/control/index.controller.php"))
				{
					$indexControl = array();
					$indexControl[] = "<?php";
					$indexControl[] = "class index_controller extends Controller";
					$indexControl[] = "{";
					$indexControl[] = "\tpublic function __construct()\n\t{";
					$indexControl[] = "\t\tparent::__construct();";
					$indexControl[] = "\t}";
					$indexControl[] = "}";
					$indexControl[] = "?>";
					file_put_contents($realPath . "/controller/index.controller.php", join("\n", $indexControl));
				}
				copy("example/.htaccess", $realPath ."/.htaccess");
			}
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
		$cur = str_replace('\\','/',$cur);
		$absp = str_replace('\\','/',$absp);
		$sabsp=explode('/',$absp);
		$scur=explode('/',$cur);
		$la=count($sabsp)-1;
		$lb=count($scur)-1;
		$l=max($la,$lb);
	 
		for ($i=0;$i<=$l;$i++)
		{
			if ($sabsp[$i]!=$scur[$i])
				break;
		}
		$k=$i-1;
		$path="";
		for ($i=1;$i<=($lb-$k-1);$i++)
			$path.="../";
		for ($i=$k+1;$i<=($la-1);$i++)
			$path.=$sabsp[$i]."/";
		$path.=$sabsp[$la];
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
		
		if($workspace[0] == '/')
		{
			return $workspace;
		}
		else 
		{
			$workspaceArray = explode("/", $workspace);
			$currentDirArray = explode("/", $currentDir);
			
			$work = array();
			foreach ($workspaceArray AS $k)
			{
				if($k == '..')
				{
					array_pop($currentDirArray);
				}
				elseif($k == '.')
				{
					
				}
				else 
				{
					$work[] = $k;
				}
			}
			if(!empty($currentDirArray))
			{
				return join("/", $currentDirArray) . "/" . join("/", $work);
			}
			else 
			{
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
		return true;
		if(!empty($dir))
		{
			if(!is_dir($dir))
			{
				return mkdir($dir, 777, true);
			}
			else 
			{
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
		foreach ($argvs AS $value)
		{
			$valueArray = explode("=", $value);
			$k = $valueArray[0];
			$v = stripslashes($valueArray[1]);
			$keyValue[$k] = $v;
		}
		return $keyValue;
	}
}
array_shift($argv);
new cmd($argv);
?>