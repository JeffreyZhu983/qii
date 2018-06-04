<?php
namespace QrCode\Widget;
use Qrcode\Traits;

class Liquid
{
    use Traits\Fill;
    public $defaults = array(
        'margin' => 1,
        'pointColor' => '#000000', //定点颜色
        'inPointColor' => '#000000',//内定点
        'frontColor' => '#000000',//前景色
        'bgColor' => '#FFFFFF', //背景色
        'contentColor' => '#000000', //内容颜色
        'style' => 2,
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
        $s = $this->pointSize;//每一块的大小


        $img = ImageCreate($w * $this->pointSize, $h * $this->pointSize);

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
                    //左上角定点
                    if ($x < 7 && $y < 7) {
                        //左上角定点的四个大角
                        if ($x === 0 || $y === 0 || $x === 6 || $y === 6) {
                            //液态
                            $xPointLeft = $x;
                            $yPointLeft = $y;
                            if ($x === 0 && $y === 0) {
                                $this->roundedCorner($img, $xPointLeft, $yPointLeft, $s, $pointColor, true, false, false, false);
                            } else if ($x === 0 && $y === 6) {
                                $this->roundedCorner($img, $xPointLeft, $yPointLeft, $s, $pointColor, false, true, false, false);
                            } else if ($x === 6 && $y === 6) {
                                $this->roundedCorner($img, $xPointLeft, $yPointLeft, $s, $pointColor, false, false, true, false);
                            } else if ($x === 6 && $y === 0) {
                                $this->roundedCorner($img, $xPointLeft, $yPointLeft, $s, $pointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            }

                        } else {
                            //液态
                            if ($x === 2 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, true, false, false, false);
                            } else if ($x === 2 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, false, true, false, false);
                            } else if ($x === 4 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, false, false, true, false);
                            } else if ($x === 4 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                            }
                        }

                    } elseif ($x > $imageSize - 8 && $y < 7) { //右上角定点

                        if ($x === $imageSize - 7 || $y === 0 || $x === $imageSize - 1 || $y === 6) {
                            //液态
                            if ($x === $imageSize - 6 && $y === 0) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $pointColor, true, false, false, false);
                            } else if ($x === $imageSize - 6 && $y === 6) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $pointColor, false, true, false, false);
                            } else if ($x === $imageSize && $y === 6) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $pointColor, false, false, true, false);
                            } else if ($x === $imageSize && $y === 0) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $pointColor, false, false, false, true);
                            }else {//上下
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            }
                        } else {
                            //液态
                            if ($x === $imageSize - 4 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, true, false, false, false);
                            } else if ($x === $imageSize - 4 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, false, true, false, false);
                            } else if ($x === $imageSize - 2 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, false, false, true, false);
                            } else if ($x === $imageSize - 2 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $this->pointSize, $inPointColor, false, false, false, true);
                            }else if($x === $imageSize - 6 || $x === $imageSize){
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                            }
                        }
                    } elseif ($y > count($data) - 9 && $x < 7) { //左下角定点
                        if ($x === 0 || $y === $imageSize - 6 || $x === 6 || $y === $imageSize - 1) {
                            //液态
                            if ($x === 0 && $y === $imageSize - 6) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, true, false, false, false);
                            } else if ($x === 0 && $y === $imageSize) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, true, false, false);
                            } else if ($x === 6 && $y === $imageSize) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, true, false);
                            } else if ($x === 6 && $y === $imageSize - 6) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            }
                        } else {
                            //液态
                            if ($x === 2 && $y === $imageSize - 4) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, true, false, false, false);
                            } else if ($x === 2 && $y === $imageSize - 2) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, true, false, false);
                            } else if ($x === 4 && $y === $imageSize - 2) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, true, false);
                            } else if ($x === 4 && $y === $imageSize - 4) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, false, true);
                            }else if($x < 6 && $y == $imageSize){
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                            }

                        }

                    } else {
                        //上
                        if ($y - 1 < 0) {
                            //靠边的块都属于0
                            $t = 0;
                        } else {
                            $t = $data[$y - 1][$x];
                        }
                        //左上
                        if ($x - 1 < 0 || $y - 1 < 0) {
                            $lt = 0;
                        } else {
                            $lt = $data[$y - 1][$x - 1];
                        }
                        //左
                        if ($x - 1 < 0) {
                            $l = 0;
                        } else {
                            $l = $data[$y][$x - 1];
                        }
                        //左下
                        if ($x - 1 < 0 || $y + 1 > $imageSize - 1) {
                            $lb = 0;
                        } else {
                            $lb = $data[$y + 1][$x - 1];
                        }
                        //下
                        if ($y + 1 > $imageSize - 1) {
                            $b = 0;
                        } else {
                            $b = $data[$y + 1][$x];
                        }
                        //右下
                        if ($x + 1 > $imageSize - 1 || $y + 1 > $imageSize - 1) {
                            $rb = 0;
                        } else {
                            $rb = $data[$y + 1][$x + 1];
                        }
                        //右
                        if ($x + 1 > $imageSize - 1) {
                            $r = 0;
                        } else {
                            $r = $data[$y][$x + 1];
                        }
                        //右上
                        if ($x + 1 > $imageSize - 1 || $y - 1 < 0) {
                            $rt = 0;
                        } else {
                            $rt = $data[$y - 1][$x + 1];
                        }

                        //上+左+下+右=0 全圆
                        if ($t == 0 && $l == 0 && $b == 0 && $r == 0) {
                            //全圆
                            imagefilledellipse($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, $contentColor);
                        } elseif ($t == 0 && $l == 0 && $r == 0) {
                            //上半圆
                            $this->halfRounded($img, $x, $y, $s, $contentColor, true, false, false, false);
                        } elseif ($t == 0 && $l == 0 && $b == 0) {
                            //左半圆
                            $this->halfRounded($img, $x, $y, $s, $contentColor, false, true, false, false);
                        } elseif ($l == 0 && $b == 0 && $r == 0) {
                            //下半圆
                            $this->halfRounded($img, $x, $y, $s, $contentColor, false, false, true, false);
                        } elseif ($t == 0 && $b == 0 && $r == 0) {
                            //右半圆
                            $this->halfRounded($img, $x, $y, $s, $contentColor, false, false, false, true);
                        } elseif ($t == 0 && $l == 0) {
                            //左上角
                            $this->roundedCorner($img, $x, $y, $s, $contentColor, true, false, false, false);
                        } elseif ($l == 0 && $b == 0) {
                            //左下角
                            $this->roundedCorner($img, $x, $y, $s, $contentColor, false, true, false, false);
                        } elseif ($b == 0 && $r == 0) {
                            //右下角
                            $this->roundedCorner($img, $x, $y, $s, $contentColor, false, false, true, false);
                        } elseif ($r == 0 && $t == 0) {
                            //右上角
                            $this->roundedCorner($img, $x, $y, $s, $contentColor, false, false, false, true);
                        } else {
                            //直角
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $contentColor);
                        }
                    }

                } else {
                    if ($x < 7 && $y < 7) {

                    } elseif ($x > $imageSize - 8 && $y < 7) { //右上角定点

                    } elseif ($y > count($data) - 9 && $x < 7) { //左下角定点

                    } else {
                        //液态
                        //为两个黑块之间的直角填充圆度
                        //上
                        if ($y - 1 < 0) {
                            //靠边的块都属于0
                            $t = 0;
                        } else {
                            $t = $data[$y - 1][$x];
                        }
                        //左上
                        if ($x - 1 < 0 || $y - 1 < 0) {
                            $lt = 0;
                        } else {
                            $lt = $data[$y - 1][$x - 1];
                        }
                        //左
                        if ($x - 1 < 0) {
                            $l = 0;
                        } else {
                            $l = $data[$y][$x - 1];
                        }
                        //左下
                        if ($x - 1 < 0 || $y + 1 > $imageSize - 1) {
                            $lb = 0;
                        } else {
                            $lb = $data[$y + 1][$x - 1];
                        }
                        //下
                        if ($y + 1 > $imageSize - 1) {
                            $b = 0;
                        } else {
                            $b = $data[$y + 1][$x];
                        }
                        //右下
                        if ($x + 1 > $imageSize - 1 || $y + 1 > $imageSize - 1) {
                            $rb = 0;
                        } else {
                            $rb = $data[$y + 1][$x + 1];
                        }
                        //右
                        if ($x + 1 > $imageSize - 1) {
                            $r = 0;
                        } else {
                            $r = $data[$y][$x + 1];
                        }
                        //右上
                        if ($x + 1 > $imageSize - 1 || $y - 1 < 0) {
                            $rt = 0;
                        } else {
                            $rt = $data[$y - 1][$x + 1];
                        }

                        if ($t == 1 && $lt == 1 && $l == 1) {
                            //左上角
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $contentColor, true, false, false, false);
                        }

                        if ($l == 1 && $lb == 1 && $b == 1) {
                            //左下角
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $contentColor, false, true, false, false);
                        }
                        if ($b == 1 && $rb == 1 && $r == 1) {
                            //右下角
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $contentColor, false, false, true, false);
                        }
                        if ($r == 1 && $rt == 1 && $t == 1) {
                            //右上角
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $contentColor, false, false, false, true);
                        }
                    }
                }
                $x++;
            }
            $y++;
        }
        $background = ImageCreate($w * $this->pointSize + 2 * $this->options['margin'], $h * $this->pointSize + 2 * $this->options['margin']);
        imagefill($background, 0, 0, $bgColor);
        imagecopymerge($background, $img, $this->options['margin'], $this->options['margin'], 0, 0, $w * $this->pointSize, $h * $this->pointSize, 100);
        imagedestroy($img);

        return $background;
    }
}