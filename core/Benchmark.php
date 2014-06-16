<?php
/**
 * Benchmark
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Benchmark.php,v 1.1 2010/04/23 06:02:12 Jinhui.Zhu Exp $
 * 
 * Usage:
 * 
 * Benchmark::set('stdclass');
 * Benchmark::set('stdclass', true);
 * 
 * echo Benchmark::Caculate('stdclass');
 * print_r(Benchmark::Caculate());
 */
if(class_exists('Benchmark'))
{
	return;
}
class Benchmark
{
	static $version = '1.1.0';
	static  $_timer = array();
	/**
	 * 设置时间
	 *
	 * @param String $k
	 * @param Bool $isEnd
	 */
	static public function set($k, $isEnd = false)
	{
		if(!$isEnd)
		{
			self::$_timer['START_' . $k] = microtime(true);
		}
		else 
		{
			self::$_timer['END_' . $k] = microtime(true);
		}
	}
	/**
	 * 计算消耗时间
	 *
	 * @param String $k
	 * @return Float
	 */
	static public function Caculate($k)
	{
		if(is_null($k))
		{
			return self::CaculateAll();
		}
		if(!isset(self::$_timer['END_'.$k]))
		{
			self::$_timer['END_'.$k] = microtime(true);
		}
		if(isset(self::$_timer['END_'.$k]) && isset(self::$_timer['START_'. $k])) return round((self::$_timer['END_'.$k] - self::$_timer['START_'. $k]), 4);
		return round(0, 4);
	}
	/**
	 * 计算所有设置过的时间点
	 *
	 * @return Array
	 */
	static protected function CaculateAll()
	{
		$keys = array();
		$cost = array();
		foreach (self::$_timer AS $k => $v)
		{
			if(substr($k, 0, 6) == 'START_')
			{
				$k = str_replace('START_', '', $k);
				$cost[$k] = self::Caculate($k);
			}
		}
		return $cost;
	}
}
?>