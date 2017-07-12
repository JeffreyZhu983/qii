<?php
namespace Plugins;
class loger implements \Qii\Loger\Writer
{
	public $logerPath = 'tmp';
	public $fileName = 'loger.log';

	public function __construct()
	{
		$this->logerPath = dirname(dirname(__FILE__)) .DS. $this->logerPath;
		$this->fileName = $this->logerPath . DS . date('Y-m-d') .'.'. $this->fileName;
		return $this;
	}

	public function setFileName($fileName)
	{
		$this->fileName = $this->logerPath . DS . date('Y-m-d') .'.'. $fileName . '.log';
		return $this;
	}
	protected function trimSpace($text)
	{
	    return  str_replace(array("\r\n", "\r", "\n", "\t", "\s", '　', chr(32)), "", $text);
	}

	public function formatLog($log)
	{
		if(!is_array($log)) return $log;
		return json_encode($log);
		return $this->trimSpace(print_r($log, true));
	}

	public function writeLog($loger)
	{
		if(is_array($loger)) $loger = $this->formatLog($loger);

		file_put_contents($this->fileName, date('Y-m-d H:i:s') ."\n\t". $loger . "\n", FILE_APPEND);
	}
}