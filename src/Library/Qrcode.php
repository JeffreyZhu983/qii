<?php
namespace Qii\Library;

\Qii\Autoloader\Psr4::getInstance()
	->setUseNamespaces([['Zxing', true]])
	->addNamespaces([
		['Zxing', Qii_DIR . DS . 'Library'. DS .'QrReader']
	]);

_require(__DIR__ . DS . 'QrReader'. DS . 'QrReader.php');

class Qrcode
{
	public function __construct()
	{
		
	}
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
}