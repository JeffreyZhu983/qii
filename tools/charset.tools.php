<?php
/**
 *
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: charset.tools.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 */
class charset_sys_tools
{
	public $version = '1.1.0';
	public function __construct($contentType, $charset)
	{
		if(empty($contentType))
		{
			$contentType = "text/html";
		}
		if(empty($charset))
		{
			$charset = "UTF-8";
		}
		header('Content-type:'.$contentType.';charset='. $charset);
	}
}
?>