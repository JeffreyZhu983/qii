<?php
/**
 * System Inoformation
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: System.php,v 1.1 2010/04/23 06:02:12 Jinhui.Zhu Exp $
 */
if(class_exists('Qii_controller'))
{
	return;
}
class Qii_controller extends Controller
{
	public $version = '1.1.0';
	/**
	 * 定义需要加载的模块
	 *
	 * @var Array
	 */
	protected $core = array('view', 'model');
	public $password;
	public function __construct()
	{
		Benchmark::set('site', true);
		Qii::setPrivate('debug', 'Instance Control');
		parent::__construct();
		$this->password = Qii::segment(2);
		$siteInfo = Qii::getSiteInfo();
		if(Qii::segment(1) != 'seccode' && !empty($siteInfo['status']['password']) && $this->password != $siteInfo['status']['password'])
		{
			die("Type Password First");
		}
	}
	public function index()
	{
		if(!empty($this->password))
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'index', 'password' => $this->password));
			$hrefOne = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'fileList', 'password' => $this->password));
			$hrefTwo = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'checkEnvironment', 'password' => $this->password));
			$hrefThree = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'Example', 'password' => $this->password));
			$hreFour = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'printURL', 'password' => $this->password));
			$hreFive = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'xml2php', 'password' => $this->password));
		}
		else 
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii'));
			$hrefOne = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'fileList'));
			$hrefTwo = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'checkEnvironment'));
			$hrefThree = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'Example'));
			$hreFour = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'printURL'));
			$hreFive = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'xml2php'));
		}
		
		
		Qii::setPrivate('debug', 'Execute Method' . __METHOD__);
		Qii::setPrivate('global', array('System' => array(
													"<a href=\"{$hrefOne}\">载入的文件列表</a>", 
													"<a href=\"{$hrefTwo}\">检查系统需求</a>", 
													"<a href=\"{$hrefThree}\">帮助文件</a>",
													"<a href=\"{$hreFour}\">示例链接</a>",
													"<a href=\"{$hreFive}\">网站配置文件生成到包含文件</a>",
													)
										)
						);
		require(Qii_DIR .DS. 'view' . DS . 'index.php');
	}
	public function Example()
	{
		if(!empty($this->password))
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'index', 'password' => $this->password));
		}
		else 
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii'));
		}
		require(Qii_DIR .DS. 'view' . DS . 'Example.php');
	}
	/**
	 * out print filelist
	 *
	 */
	public function fileList()
	{
		if(!empty($this->password))
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'index', 'password' => $this->password));
		}
		else 
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii'));
		}
		//清除之前所有的输出
		//ob_end_clean();
		require(Qii_DIR .DS. 'view' . DS . 'fileList.php');
	}
	/**
	 * 检查PHP需要的系统环境
	 *
	 * @param Bool $isClear
	 */
	public function checkEnvironment($isClear = false)
	{
		if(!empty($this->password))
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'index', 'password' => $this->password));
		}
		else 
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii'));
		}
		//清除之前所有的输出
		//ob_end_clean();
		require(Qii_DIR .DS. 'view' . DS . 'checkEnvironment.php');
	}
	public function printURL()
	{
		
		if(!empty($this->password))
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'index', 'password' => $this->password));
			$hrefOne = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'fileList', 'password' => $this->password));
			$hrefTwo = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'checkEnvironment', 'password' => $this->password));
			$hrefThree = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'Example', 'password' => $this->password));
			$hreFour = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'printURL', 'password' => $this->password));
			$hreFive = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'xml2php', 'password' => $this->password));
		}
		else 
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii'));
			$hrefOne = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'fileList'));
			$hrefTwo = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'checkEnvironment'));
			$hrefThree = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'Example'));
			$hreFour = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'printURL'));
			$hreFive = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'xml2php'));
		}
		Qii::showMessage(array("示例链接, 当前模式为'<font color=\"red\">" . Qii::load('Router')->get('_mode') . "</font>'", $href, $hrefOne, $hrefTwo, $hrefThree, $hreFour, $hreFive), true, $href);
	}
	public function xml2php()
	{
		if(!empty($this->password))
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii', 'action' => 'index', 'password' => $this->password));
		}
		else 
		{
			$href = Qii::load('Router')->URI(array('control' => 'Qii'));
		}
		$fileName = Qii::getPrivate('qii_site_xpath');
		$res = Qii::xml2Cache($fileName, true);
		if($res)
		{
			$msg = "成功";
		}
		else
		{
			$msg = "失败";
		}
		Qii::showMessage(array("生成配置文件" . $msg), true, $href);
	}
	public function seccode()
	{
		$seccodePlugin = new seccode_sys_plugin();
		$seccodePlugin->prepare();
		$code = $seccodePlugin->code;
		$seccodePlugin->display();
	}
	public function qr()
	{
		$sid = 'KJMDIE-KDKGLKMSD-0230-2300-KDJEMIKD';
		$qr = new qr_sys_plugin();
		$qr->out($sid);
	}
	public function install()
	{
		/*
		ob_implicit_flush(true);//设置有输出就输出到浏览器
		ob_end_clean();
		flush();     
		sleep(1);*/
		
	}
}
?>