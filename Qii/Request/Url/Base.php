<?php
namespace Qii\Request\Url;

abstract class Base
{
	const VERSION = '1.2';
	/**
	 * URL 模式
	 *
	 * @var String
	 */
	protected $_mode = 'Normal';
	/**
	 * 链接字符
	 *
	 * @var String
	 */
	private $_symbol = '&';
	/**
	 * 文件后缀
	 *
	 * @var String
	 */
	private $_extenstion = '.html';
	/**
	 * 允许设置的URL模式
	 *
	 * @var Array
	 */
	private $_allowMode = array('Normal', 'Middle', 'Short');

	private $_urlRewrite = "";

	/**
	 * 是否去掉文件名称，如果rewrite不在根目录的时候将此设为true，避免生成URL错误.
	 *
	 * @var Bool
	 */
	private $_fileNameTrim = false;
	/**
	 * URL中匹配到的参数
	 */
	private $params = null;
	/**
	 * 初始化模式
	 * @param string $mode 模式
	 */
	public function __construct($mode)
	{
		$this->checkMode($mode);
		$this->_mode = $mode;
		if(in_array($mode, array('Short', 'Middle')))
		{
			$this->_symbol = '/';
		}
		$this->params = $this->getParams();
	}
	/**
	 * 返回所有params参数
	 * 
	 */
	public function params()
	{
		return $this->params;
	}

	/**
	 * 根据给定的参数创建URL
	 * @param Array $array {controller:controllerName, action : actionName}
	 * @param string $fileName
	 * @param string $extenstion
	 * @param string $trimExtension
	 * @return string
	 */
	public function bulidURI($params, $fileName = '', $extenstion = '', $trimExtension = false)
	{
		$this->checkMode($this->_mode);
		if (empty($fileName)) {
			$fileName = $_SERVER['SCRIPT_NAME'];
		}
		if (sizeof($params) == 0) {
			return '';
		}
		//2012-03-25新增 path，如果path==$fileName就去掉path，从而避免多个rewrite的时候调用此方法生成的URL错误。
		$path = $this->getPathInfo();
		//去掉文件名并保留路径 去掉加路径出现的bug
		if ($this->_fileNameTrim) {
			$fileName = rtrim(str_replace(basename($fileName), '', $fileName), "/");
		}
		$notAllowPath = array($fileName, "\\");
		if (in_array($path, $notAllowPath)) {
			$path = "";
		}
		$realPath = rtrim(str_replace('//', '/', $path . $fileName), '/');
		if ($this->_mode == 'normal') {
			return $fileName . '?' . $this->{$this->_mode}($params);
		}
		if (!empty($extenstion)) {
			return $realPath . $this->_symbol . $this->{$this->_mode}($params) . ($trimExtension ? '' : $extenstion);
		}
		return $realPath . $this->_symbol . $this->{$this->_mode}($array) . ($trimExtension ? '' : $this->_extenstion);
	}
	/**
	 * 获取当前网址
	 */
	public function getWebHost()
	{
		if (IS_CLI) return '';
		$prefix = 'http://';
		if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $_SERVER['SERVER_PORT'] == 443) $prefix = 'https://';
		return $prefix . rtrim(rtrim(str_replace('//', '/', $_SERVER['HTTP_HOST']), '/'), "\\");
	}
	/**
	 * 获取当前域名
	 */
	public function getDomain()
	{
		return rtrim(rtrim(str_replace('//', '/', $_SERVER['HTTP_HOST']), '/'), "\\");
	}
	/**
	 *
	 * 获取当前页面URL
	 */
	public function getCurrentURL()
	{
		if (IS_CLI) return '';
		return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
	/**
	 *
	 * 获取当前页面的路径相关信息
	 * @param String $path
	 * @param String $index
	 */
	public function getPathInfo($path = '', $index = 'dirname')
	{
		if (empty($path)) $path = $_SERVER['SCRIPT_NAME'];
		//Array ( [dirname] => /Qii [basename] => index.php [extension] => php [filename] => index )
		$pathInfo = pathinfo($path);
		$pathInfo['dirname'] = str_replace("\\", "/", $pathInfo['dirname']);
		if (!empty($index)) {
			return $pathInfo[$index];
		}
		return $pathInfo;
	}
	/**
	 *
	 * 获取当前路径
	 * @param String $path
	 */
	public function getPath($path = '')
	{
		if (empty($path)) {
			$path = $_SERVER['SCRIPT_NAME'];
		}
		return substr($path, 0, (strrpos($path, '/')));
	}
	/**
	 *
	 * 设置是否去掉文件名字
	 * @param Bool $trim
	 */
	public function setTrim($trim = false)
	{
		$this->_fileNameTrim = $trim;
	}

	/**
	 * 设置文件后缀名字
	 *
	 * @param String $extenstion
	 */
	public function setExtenstion($extenstion)
	{
		if (!empty($extenstion)) $this->_extenstion = $extenstion;
	}

	/**
	 * 设置连接字符串
	 *
	 * @param String $symbol
	 */
	public function setSymbol($symbol)
	{
		if (!empty($symbol)) $this->_symbol = $symbol;
	}

	/**
	 * 设置URI模式
	 *
	 * @param String $mode
	 */
	public function setMode($mode)
	{
		$this->checkMode($mode);
		$this->_mode = $mode;
		return $this;
	}
	/**
	 * 获取本类中的私有属性
	 *
	 * @param String $key
	 * @return Mix
	 */
	public function get($key = null)
	{
        if($key === null) return $this->params;
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}
    /**
     * 获取POST数据
     */
    public function post($name = null, $default = null)
    {
        if($name === null) return $_POST;
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }
    
	/**
	 * Cli模式下数据的传输
	 * 
	 * @param string $key
	 */
	protected function CLIParams($key = '')
	{
		$argv = array();
		if (isset($_SERVER['argv'])) $argv = $_SERVER['argv'];
		//修正部分服务器Rewrite 后再加参数不识别的问题（直接进入命令行的模式）
		if ($argv && $_SERVER['PHP_SELF'] == $_SERVER['SCRIPT_NAME']) {
			if (count($argv) == 1) return;
			array_shift($argv);
			$args = (array) $this->parseArgs($argv[0]);
			//处理GET或POST方法 数据结构 key1=value1 key2=value2 键和值中间不能有空格
			if($_SERVER['argc'] > 2){
				for($i = 1; $i < $_SERVER['argc'] - 1; $i++)
				{
					list($index, $val) = explode('=', $argv[$i], 2);
					$args[$index] = $val;
				}
			}
			if ($args && $key != '') {
				return isset($args[$key]) ? $args[$key] : '';
			}
			return $args;
		}
	}
	/**
	 * 获取参数
	 *
	 * @param String $fileName
	 * @param String $url
	 * @param String $key
	 * @return Mix
	 */
	public function getParams($key = '', $url = '', $fileName = '')
	{
	    if($this->params != null) return $this->params;
		$this->checkMode($this->_mode);
		//如果是命令行模式
		if(IS_CLI) return $this->CLIParams($key);

		if ($this->_mode == 'Normal') {
			if (empty($key)) return $_GET;
			return $_GET[$key];
		}
		if (!isset($_SERVER['PATH_INFO'])) $_SERVER['PATH_INFO'] = '';
		if (empty($url)) {
			//修正Rewrite以指定目录开头的bug 将$url = $_SERVER['REQUEST_URI'];替换成以下的
			$url = (($_SERVER['PATH_INFO'] != '' && $_SERVER['PATH_INFO'] != '/') ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI']);
		}
		if (empty($fileName)) {
			$fileName = $_SERVER['SCRIPT_NAME'];
		}
		//取fileName后边的内容
		$url = str_replace($fileName, "", $url);
		$param = parse_url($url);
		//如果到?号的URL则取query，没有?则取path的basename($path);
		if (isset($param['path']) && substr($param['path'], 0, 1) == '?') {
			//拆分字符
			if (empty($param['query'])) {
				return '';
			}
			$query = $param['query'];
		} else {
			$query = isset($param['path']) ? $param['path'] : '';
		}
		$query = $this->comparePath($query, $fileName);
		//添加系统扩展名到返回数组中 2011-10-14 15:26
		preg_match("/(.*)\.(.*)$/", $query, $extenstion);
		//去掉文件后缀名称，修改时间2010-09-03 22:33，以便指定任意后缀名。
		if (!isset($extenstion[2])) $extenstion[2] = '';
		$extenstion[2] = str_replace('/', '\/', $extenstion[2]);
		$query = preg_replace("/\.{$extenstion[2]}$/", "", $query);
		$paramArray = explode($this->_symbol, $query);
		$v = $this->decodeArgs($paramArray);
		//添加系统扩展名到返回数组中 2011-10-14 15:26
		$v['sysfileExtension'] = $extenstion[2];
		if ($_GET) $v = array_merge($v, $_GET);
		if ($key != '' || is_int($key)) {
			return $v[$key];
		}
		return $v;
	}
	/**
	 * 对比转发文件的路径
	 *
	 * @param String $path
	 * @param String $scriptName
	 * @return String
	 */
	public function comparePath($path, $f)
	{
		//去掉basename($f)再进行比较,比较的时候将字符串转换成小写;
		$basename = basename($f);
		$path = str_replace($basename, "", $path);
		$f = str_replace($basename, "", $f);
		//对比parseURL后的Path
		$pathArray = explode('/', ltrim(rtrim($path, '/'), '/'));
		$fArray = explode('/', ltrim(rtrim($f, '/'), '/'));
		$fCount = count($fArray);
		$tmpArray = array();
		$tmpArray = $pathArray;
		for ($i = 0; $i < $fCount; $i++) {
			if (strtolower($pathArray[$i]) != strtolower($fArray[$i])) {
				break;
			}
			array_shift($tmpArray);
		}
		return join("/", $tmpArray);
	}
	/**
	 * 检查生成链接模式
	 *
	 * @param String $mode
	 */
	public function checkMode($mode)
	{
		if (!in_array($mode, $this->_allowMode)) 
		{
			throw new \Qii\Exceptions\Unsupport("链接模式错误，链接格式只能为 '<u><font color=\"green\">" . join("', '", $this->_allowMode) . "</font></u>'，当前模式为 '<font color=\"red\">" . $mode . "</font>'", __LINE__);
		}
	}

	public function __call($method, $args)
	{
		//防止掉用不存在的方法
	}
}