<?php
/**
 * 
 * Header 类
 * 
 * 输入文件的头部信息
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Header.php 2 2011-05-04 08:50:19Z zjh $
 */
if(class_exists('Header'))
{
	return;
}
class Header
{
	public $version = '1.1.0';
	public $extension;
	/**
	 * 根据URL类型检查文件类型
	 *
	 * @return String
	 */
	public function detective()
	{
		$this->extension =  Qii::segment('sysfileExtension');
		$headerInfo = Qii::loadFile(Qii_DIR . "/configure/mime.config.php");
		return $headerInfo[$this->extension];
	}
	/**
	 * 自动输出头部
	 * 
	 */
	public function autoHeader()
	{
		$header = $this->detective();
		if($header != 'unknow')
		{
			header("Content-Type:{$header};charset=UTF-8");
		}
	}
}
?>