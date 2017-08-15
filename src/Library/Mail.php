<?php
namespace Qii\Library;
/**
 * Mail 类
 *
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: mail.plugin.php,v 1.1 2010/04/23 06:02:12 Jinhui.Zhu Exp $
 */
_require(Qii_DIR . "/Library/Third/phpmailer/class.phpmailer.php");
class Mail extends \PHPMailer
{
	private $mailConfig;
	private $_error;
	public function __construct()
	{
	}
	/**
	 * 设置SMTP
	 *
	 * @param Array $mailConfig
	 */
	public function sysSet($mailConfig)
	{
		$this->mailConfig = $mailConfig;
		$this->IsSMTP();
		$this->Host     = $mailConfig['server'];
		$this->Port = $mailConfig['port'];
		$this->SMTPAuth = $mailConfig['auth'];
		$this->SMTPDebug = 0;
		$this->Username = $mailConfig['authUsername'];
		$this->Password = $mailConfig['authPassword'];
		$this->From     = $mailConfig['from'];
		$this->FromName = $mailConfig['fromName'];
		$this->CharSet = 'UTF-8';
		$this->IsHTML(true);
	}
	/**
	 * 发送邮件
	 * @param array $mailInfo
	 */
	public function sendMail($mailInfo)
	{
		$this->ClearAddresses();
		$this->AddAddress($mailInfo['to']);
		$this->Subject  =  $mailInfo['subject'];
		$this->Body     =  $mailInfo['content'];
		if(!$this->Send())
		{
			$this->error($this->ErrorInfo);
			return false;
		}
		return true;
	}
	/**
	 * 获取当前SMTP配置
	 *
	 * @return Array
	 */
	public function getCurrentConfig()
	{
		return $this->mailConfig;
	}
	/**
	 * 错误信息
	 *
	 * @param Mix $error
	 */
	public function error($error)
	{
		$this->_error[] = $error;
	}
	/**
	 * 获取错误信息
	 *
	 * @return Array
	 */
	public function getError()
	{
		return $this->_error;
	}
}