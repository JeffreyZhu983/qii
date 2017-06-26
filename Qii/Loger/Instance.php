<?php
namespace Qii\Loger;
/**
 * 注册一个写日志的插件，由项目本身去实现是否写日志，当框架在抛出异常的时候会触发写日志的操作
 */
class Instance
{
	const VERSION = '1.2';
	/**
	 * loger writer
	 */
	private $logerHooker;

	public function __construct(\Qii\Loger\Writer $hooker)
	{
		$this->setHooker($hooker);
	}

	/**
	 * 设置写日志的类
	 */
	public function setHooker(\Qii\Loger\Writer $hooker)
	{
		$this->logerHooker = $hooker;
	}

	/**
	 * 设置日志文件名称
	 */
	public function setFileName($fileName)
	{
		if (method_exists($this->logerHooker, 'setFilename')) {
			$this->logerHooker->setFilename($fileName);
		}
	}

	/**
	 * 调用写日志的方法
	 */
	public function writeLog($loger)
	{
		if (method_exists($this->logerHooker, 'writeLog')) {
			$this->logerHooker->writeLog($loger);
		}
	}
}