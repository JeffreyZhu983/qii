<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Error.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 * 
 * 异常处理类
 * 
 */
if(class_exists('Error'))
{
	return;
}
Qii::requireOnce(Qii_DIR .'/core/Header.php');
final class Error extends Exception
{
	public $version = '1.1.0';
	/**
	 * Throw Exception AS String
	 *
	 */
	public function __toString() 
	{
		$messageArray = array();
		$messageArray[] = "'".$this->getMessage() . "'";
		$messageArray[] = "In File '" . $this->getFile() . "'";
		$messageArray[] = self::getLineMessage($this->getFile(), $this->getLine());
		$messageArray[] = "<font color=\"orange\"><strong>Trace Debug AS below</strong></font>";
		$messageArray = array_merge($messageArray, explode("\n", $this->getTraceAsString()));
		Benchmark::set('site', true);
		//提供html, json, xml格式输出
		$header = new Header();
		$header->autoHeader();

		if(strtolower(Qii::segment('sysfileExtension')) == 'json')
		{
			echo json_encode(array('status' => -1 , 'message' => $this->getMessage(), 'desc' => array_map('strip_tags', $messageArray)));
			die();
		}
		if(strtolower(Qii::segment('sysfileExtension')) == 'xml')
		{
			echo '<?xml version="1.0" encoding="utf-8"?>';
			echo '<root>';
			echo '<message><![CDATA['.$this->getMessage().']]></message>';
			$message = array_map('strip_tags', $messageArray);
			echo '<desc><![CDATA['.join("\n", $message).']]></desc>';
			echo '</root>';
			die();
		}
		require(Qii_DIR . '/view/Error.php');
		die();
  	}
  	/**
  	 * 显示错误
  	 *
  	 * @param Object $e
  	 */
  	static public function getError($e)
  	{
		$messageArray = array();
		$messageArray[] = "'".$e->getMessage() . "'";
		$messageArray[] = "In File '" . $e->getFile() . "'"; 
		$messageArray[] = self::getLineMessage($e->getFile(), $e->getLine());
		$messageArray[] = "<font color=\"orange\"><strong>Trace Debug AS below</strong></font>";
		$messageArray = array_merge($messageArray, explode("\n", $e->getTraceAsString()));
		Benchmark::set('site', true);
		//提供html, json, xml格式输出
		$header = new Header();
		$header->autoHeader();

		if(strtolower(Qii::segment('sysfileExtension')) == 'json')
		{
			echo json_encode(array('status' => -1 , 'message' => $this->getMessage(), 'desc' => array_map('strip_tags', $messageArray)));
			die();
		}
		if(strtolower(Qii::segment('sysfileExtension')) == 'xml')
		{
			echo '<?xml version="1.0" encoding="utf-8"?>';
			echo '<root>';
			echo '<message><![CDATA['.$this->getMessage().']]></message>';
			$message = array_map('strip_tags', $messageArray);
			echo '<desc><![CDATA['.join("\n", $message).']]></desc>';
			echo '</root>';
			die();
		}
		require(Qii_DIR . '/view/Error.php');
		die();
   	}
	/**
	 * get the source file
	 *
	 * @param unknown_type $fileName
	 * @param unknown_type $line
	 * @return unknown
	 */
	static public function getLineMessage($fileName, $line)
	{
		$seekline = max(0, $line-1);
		$spl = new SplFileObject($fileName);
		$spl->seek($seekline);
		return 'Line ' . $line . ' ' . trim($spl->current());
	}
}
/**
 * 设置错误处理函数
 */
set_exception_handler(array("Error", "getError"));
set_error_handler(array("Error", "getError"), E_USER_ERROR);
?>