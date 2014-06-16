<?php
/**
 *
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: file.tools.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 */
class file_sys_tools
{
	public $version = '1.1.0';
	/**
	 * 锁定文件目录
	 *
	 * @var String 文件目录
	 */
	private $lockPath = '.';
	/**
	 * 允许文件后缀
	 *
	 * @var Array
	 */
	private $allowExtension = array();
	public function setPath($path = '.')
	{
		$this->lockPath = $path;
	}
	/**
	 * 设置允许的文件后缀
	 *
	 * @param Array $extention
	 */
	public function setAllowed(Array $extention)
	{
		$this->allowExtension = $extention;
	}
	/**
	 * 转换路径分隔符
	 *
	 * @param String $fileName
	 * @return String
	 */
	public function filter($fileName)
	{
		$fileName = preg_replace("/[\\|\/|\\\\|\/\/]/", DS, $fileName);
		return $fileName;
	}
	/**
	 * 获取指定路径下的所有文件及目录
	 *
	 * @param String $dir
	 * @return Array
	 */
	public function scanDir($dir)
	{
		$data  = array();
		$path = rtrim($this->lockPath, DS) . DS . ltrim($this->filter($dir), DS);
		$directories = glob($path . '/*', GLOB_ONLYDIR);
		foreach ($directories AS $key => $val)
		{
			$directories[$key] = ltrim(str_replace($path, '', $this->filter($val)), DS);
		}
		$files = glob($path . '/*.*');
		foreach ($files AS $key => $val)
		{
			$files[$key] = ltrim(str_replace($path, '', $this->filter($val)), DS);
		}
		$data['lockPath'] = $this->lockPath;
		$data['subdir'] = ltrim($this->filter($dir), DS);
		$data['directories'] = $directories;
		$data['files'] = $files;
		return $data;
	}
}
?>