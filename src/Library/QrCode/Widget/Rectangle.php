<?php
namespace QrCode\Widget;
use Qrcode\Traits;

class Rectangle
{
    use Traits\Fill;
    public $defaults = array(
        'margin' => 1,
        'pointColor' => '#000000', //定点颜色
        'inPointColor' => '#000000',//内定点
        'frontColor' => '#000000',//前景色
        'bgColor' => '#FFFFFF', //背景色
        'contentColor' => '#000000', //内容颜色
        'style' => 1,
    );

    public $pointSize = 3;

    public $options;

    public function __construct($pointSize, $options)
    {
        $this->pointSize = $pointSize;
        $this->options = array_merge($this->defaults, $options);
        if(!empty($options['frontColor'])) {
            if(empty($options['pointColor'])) $this->options['pointColor'] = $options['frontColor'];
            if(empty($options['inPointColor'])) $this->options['inPointColor'] = $options['frontColor'];
            if(empty($options['contentColor'])) $this->options['contentColor'] = $options['frontColor'];
        }
        return $this;
    }

    public function handle($data)
    {
        $pointColor = $this->hex2rgb($this->options['pointColor']);
        $inPointColor = $this->hex2rgb($this->options['inPointColor']);
        $bgColor = $this->hex2rgb($this->options['bgColor']);
        $contentColor = $this->hex2rgb($this->options['contentColor']);

        $w = strlen($data[0]);
        $h = count($data);

        $imageSize = count($data) - 1;


        $img = ImageCreate($w * $this->pointSize + 2 * $this->options['margin'], $h * $this->pointSize + 2 * $this->options['margin']);

        $bgColor = ImageColorAllocate($img, $bgColor['r'], $bgColor['g'], $bgColor['b']);//背景色
        $pointColor = ImageColorAllocate($img, $pointColor['r'], $pointColor['g'], $pointColor['b']);//定点色
        $inPointColor = ImageColorAllocate($img, $inPointColor['r'], $inPointColor['g'], $inPointColor['b']);//内定点
        $contentColor = ImageColorAllocate($img, $contentColor['r'], $contentColor['g'], $contentColor['b']);//内容色

        imagefill($img, 0, 0, $bgColor);

        $y = 0;
        foreach ($data as $row) {
            $x = 0;
            while ($x < $w) {
                if (substr($row, $x, 1) == "1") {
                    //返回字符串 string 由 start 和 length 参数指定的子字符串。
                    //x左边开始坐标
                    $xPointLeft = ($x * $this->pointSize) + $this->options['margin'];
                    //y左边开始坐标
                    $yPointLeft = ($y * $this->pointSize) + $this->options['margin'];
                    //x右下角坐标
                    $xPointRight = ($x + 1) * $this->pointSize + $this->options['margin'];
                    //y右下角坐标
                    $yPointRight = ($y + 1) * $this->pointSize + $this->options['margin'];
                    //左上角定点
                    if ($x < 7 && $y < 7) {
                        //左上角定点的四个大角
                        if ($x === 0 || $y === 0 || $x === 6 || $y === 6) {
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $pointColor);
                        } else {//里边
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $inPointColor);
                        }
                    } elseif ($x > $imageSize - 8 && $y < 7) { //右上角定点
                        if ($x === $imageSize - 7 || $y === 0 || $x === $imageSize - 1 || $y === 6) {
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $pointColor);
                        }else if($x === $imageSize || ($x === $imageSize - 6 && $y < 6)) {//左|右
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $pointColor);
                        } else {//中间
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $inPointColor);
                        }
                    } elseif ($y > count($data) - 9 && $x < 7) { //左下角定点
                        if ($x === 0 || $y === $imageSize - 7 || $x === 6 || $y === $imageSize - 1) {
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $pointColor);
                        }else if(($y === $imageSize - 6 && $x < 6) || $y === $imageSize && $x < 6) {//上|下
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $pointColor);
                        }else {//中间
                            imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $inPointColor);
                        }
                    } else {
                        imagefilledrectangle($img, $xPointLeft, $yPointLeft, $xPointRight, $yPointRight, $contentColor);
                    }
                }
                $x++;
            }
            $y++;
        }
        return $img;
    }

}