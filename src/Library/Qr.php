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
        ['Zxing', Qii_DIR . DS . 'Library' . DS . 'QrReader'],
        ['QrCode', Qii_DIR . DS . 'Library' . DS . 'QrCode'],
    ]);
use Zxing\QrReader;

_require(__DIR__ . DS . 'QrReader' . DS . 'Common' . DS . 'customFunctions.php');
_require(__DIR__ . DS . 'QrReader' . DS . 'QrReader.php');


_require(__DIR__ . DS . 'QrCode' . DS . 'QRconst.php');
_require(__DIR__ . DS . 'QrCode' . DS . 'QREncode.php');

class Qr
{
    use \QrCode\Traits\Fill;
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
        if (!file_exists($image)) {
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
    public function creator($txt, $pointSize = 8, $margin = 1, $errorLevel = 4)
    {
        if (!$txt) return;
        return \QrCode\QRcode::png($txt, false, $errorLevel, $pointSize, $margin);
    }

    /**
     * 生成带颜色的二维码
     * 注意：背景色和前景色不能一样，否则识别不出来
     *
     * @param string $txt 需要生成的内容
     * @param int $pointSize 每个点的大小
     * @param int $margin 边距
     * @param int $errorLevel 错误级别
     * @param array $options 额外选型
     */
    public function creatorColor($txt, $pointSize = 10, $margin = 1, $errorLevel = 4, $options = array())
    {
        $defaults = array(
            'width' => 240, //图片大小
            'margin' => 2,
            'logo' => '', //logo
            'bg' => '',
            'pointColor' => '', //定点颜色
            'inPointColor' => '',//内定点
            'frontColor' => '#000000',//前景色
            'bgColor' => '#FFFFFF', //背景色
            'contentColor' => '', //内容颜色
            'style' => 1,//直角 1， 液态 2 ，圆角 0
        );
        $options = array_merge($defaults, $options);

        \QrCode\QRencode::factory($errorLevel, $pointSize, $margin);

        $qrCls = new \QrCode\QRencode();
        $data = $qrCls->encode($txt);

        switch($options['style'])
        {
            case 2:
                $handle = new \QrCode\Widget\Liquid($pointSize, $options);
                break;
            case 1:
                $handle = new \QrCode\Widget\Rectangle($pointSize, $options);
                break;
            case 0:
                $handle = new \QrCode\Widget\Edellipse($pointSize, $options);
                break;
        }
        $qrImage = $handle->handle($data);

        //保存图片
        $im = $this->resizeImage($qrImage, $options['width'], $options['width']);

        //增加logo
        if (!empty($options['logo'])) {
            $im = $this->imageAddLogo($im, $options['logo']);
        }

        //添加背景图
        if (!empty($options['bg'])) {
            $im = $this->imageAddBG($im, $options['bg']);
        }
        //保存图片
        header('Content-type:image/png');
        imagepng($im);
    }

    public function __call($method, $args)
    {
        $qrCls = new QRencode();
        return call_user_func_array(array($qrCls, $method), $args);
    }
}
