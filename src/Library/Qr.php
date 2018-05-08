<?php
/**
 * 二维码工具
 * 提供读取和生成二维码方法
 */
namespace Qii\Library;

\Qii\Autoloader\Psr4::getInstance()
	->setUseNamespaces([
		['Zxing', true],
		['QrCode', true],
	])
	->addNamespaces([
		['Zxing', Qii_DIR . DS . 'Library'. DS .'QrReader'],
		['QrCode', Qii_DIR . DS . 'Library'. DS .'QrCode'],
	]);
use Zxing\QrReader;

_require(__DIR__ . DS . 'QrReader'. DS . 'Common'. DS .'customFunctions.php');
_require(__DIR__ . DS . 'QrReader'. DS . 'QrReader.php');


_require(__DIR__ . DS . 'QrCode'. DS . 'QRconst.php');
_require(__DIR__ . DS . 'QrCode'. DS . 'QREncode.php');

class Qr
{
	public function __construct()
	{
		
	}
	/**
	 * 读取二维码中的内容
	 * @param string $image 图片地址
	 * @return string 内容
	 */
	public function reader($image)
	{
		if(!file_exists($image))
		{
			throw new \Exception('Unknow image file', __LINE__);
		}
		$qrcode = new QrReader($image);
		$text = $qrcode->text();
		return $text;
	}
	/**
	 * 生成二维码
	 * @param string $txt 二维码文案
	 * @param int $pointSize 图片尺寸
	 * @param int $margin 图片边距
	 * @param int $errorLevel 错误级别
	 * @return null
	 */
	public function creater($txt, $pointSize = 8, $margin = 1, $errorLevel = 4)
	{
		if(!$txt) return;
		return \QrCode\QRcode::png($txt, false, $errorLevel, $pointSize, $margin);
	}
}