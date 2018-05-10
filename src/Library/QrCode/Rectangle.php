<?php
namespace QrCode;

class Rectangle
{
    use Traits\Fill;
    public $defaults = array(
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
        return $this;
    }

    public function handle($data)
    {
        $pointColor = $this->hex2rgb($this->options['pointColor']);
        $inPointColor = $this->hex2rgb($this->options['inPointColor']);
        $frontColor = $this->hex2rgb($this->options['frontColor']);
        $bgColor = $this->hex2rgb($this->options['bgColor']);
        $contentColor = $this->hex2rgb($this->options['contentColor']);

        $w = strlen($data[0]);
        $h = count($data);

        $imageSize = count($data) - 1;
        $s = $this->pointSize;//每一块的大小


        $img = ImageCreate($w * $this->pointSize, $h * $this->pointSize);

        $bgColor = ImageColorAllocate($img, $bgColor['r'], $bgColor['g'], $bgColor['b']);//背景色
        $pointColor = ImageColorAllocate($img, $pointColor['r'], $pointColor['g'], $pointColor['b']);//定点色
        $inPointColor = ImageColorAllocate($img, $inPointColor['r'], $inPointColor['g'], $inPointColor['b']);//内定点
        $frontColor = ImageColorAllocate($img, $frontColor['r'], $frontColor['g'], $frontColor['b']);//前景色
        $contentColor = ImageColorAllocate($img, $contentColor['r'], $contentColor['g'], $contentColor['b']);//内容色
        
        $y = 0;
        foreach ($data as $row) {
            $x = 0;
            while ($x < $w) {
                if (substr($row, $x, 1) == "1") {
                    //返回字符串 string 由 start 和 length 参数指定的子字符串。
                    //左上角定点
                    if ($x < 7 && $y < 7) {
                        //左上角定点的四个大角
                        if ($x === 0 || $y === 0 || $x === 6 || $y === 6) {
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                        } else {
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                        }

                    } elseif ($x > $imageSize - 8 && $y < 7) { //右上角定点

                        if ($x === $imageSize - 7 || $y === 0 || $x === $imageSize - 1 || $y === 6) {
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                        } else {
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                        }

                    } elseif ($y > count($data) - 9 && $x < 7) { //左下角定点
                        if ($x === 0 || $y === $imageSize - 7 || $x === 6 || $y === $imageSize - 1) {
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                        } else {
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                        }

                    } else {
                        imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $frontColor);
                    }
                }
                $x++;
            }
            $y++;
        }
        return $img;
    }

}