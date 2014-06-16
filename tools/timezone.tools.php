<?php
/**
 *
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: timezone.tools.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 设置时间区域
 */
class timezone_sys_tools
{
	public $version = '1.1.0';
	public function __construct($timezone)
	{
		if(empty($timezone))
		{
			return;
		}
		date_default_timezone_set($timezone);
	}
}