<?php
 namespace Qii\Library;
/**
 * Library idcreator class
 * 可以用于生成订单ID之类的，并带数字校验
 * @author Jinhui.zhu    <jinhui.zhu@live.cn>
 *
 * 用法:
 * $idcreator = new \Qii\Library\IdCreator();
 * 生成Id
 * $id = $idcreator->id();
 * 校验生成的id
 * $result = $idcreate->verify($id);
 *
 */
class IdCreator
{
	const VERSION = '1.2';
	public $security = 8888;

	public function __construct()
	{
	}

	/**
	 * 验证订单号是否正确
	 *
	 * @param Int $orderId
	 * @return Bool
	 */
	public function verify($id)
	{
		$sign = substr($id, -4);
		$sid = substr($id, 0, -4);
		$randNumber = substr($sid, -8);
		$mySign = (string)(substr(hexdec(md5($sid . $this->security)), 0, 5) * 1000);
		if ($mySign == $sign) {
			return true;
		}
		return false;
	}

	/**
	 * 用 日期小时分钟秒+毫秒+uniqid生成订单ID
	 *
	 * @return Int
	 */
	public function id()
	{
		mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
		list($str, $number) = explode(".", uniqid(rand(), true), 2);
		$randNumber = substr(sprintf("%08d", $number), 0, 8);
		list($minsec, $timestamp) = explode(" ", microtime(), 2);
		$sid = date('YmdHis') . sprintf("%06d", substr($minsec, 2, 6)) . $randNumber;
		$sign = substr(hexdec(md5($sid . $this->security)), 0, 5) * 1000;
		return $sid . $sign;//28位+4位校验码
	}
}

?>