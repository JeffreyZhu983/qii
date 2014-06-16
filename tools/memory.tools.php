<?php
/**
 *
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: memory.tools.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 获取内存使用量
 */
class memory_sys_tools
{
	public $version = '1.1.0';
	public function __construct()
	{
		return $this->memory();
	}
	/**
	 * echo new memory_sys_helper();
	 *
	 * @return String
	 */
	public function __toString()
	{
		return $this->memory();
	}
	/**
	 * 获取内存使用量
	 *
	 * @return String
	 */
	public function memory()
	{
		return ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
		
		if(function_exists('memory_get_usage'))
		{
			$memory = memory_get_usage();
		}
		else
		{
			$output=array();
			if(strncmp(PHP_OS,'WIN',3) === 0)
			{
				exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST',$output);
				$memory = isset($output[5])?preg_replace('/[\D]/','',$output[5])*1024 : 0;
			}
			else
			{
				$pid = getmypid();
				exec("ps -eo%mem,rss,pid | grep $pid", $output);
				$output = explode("  ",$output[0]);
				$memory = isset($output[1]) ? $output[1]*1024 : 0;
			}
		}
		return sprintf('%.4f', $memory/1024/1024).'MB';
	}
}
?>