<?php

namespace QrCode;

class Liquid
{
    use Traits\Fill;
    public $defaults = array(
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
                            if ($x === 0 && $y === 0) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, true, false, false, false);
                            } else if ($x === 0 && $y === 6) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, true, false, false);
                            } else if ($x === 6 && $y === 6) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, true, false);
                            } else if ($x === 6 && $y === 0) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            }

                        } else {
                            //液态
                            if ($x === 2 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, true, false, false, false);
                            } else if ($x === 2 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, true, false, false);
                            } else if ($x === 4 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, true, false);
                            } else if ($x === 4 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                            }
                        }

                    } elseif ($x > $imageSize - 8 && $y < 7) { //右上角定点

                        if ($x === $imageSize - 7 || $y === 0 || $x === $imageSize - 1 || $y === 6) {
                            //液态
                            if ($x === $imageSize - 7 && $y === 0) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, true, false, false, false);
                            } else if ($x === $imageSize - 7 && $y === 6) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, true, false, false);
                            } else if ($x === $imageSize - 1 && $y === 6) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, true, false);
                            } else if ($x === $imageSize - 1 && $y === 0) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            }
                        } else {
                            //液态
                            if ($x === $imageSize - 5 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, true, false, false, false);
                            } else if ($x === $imageSize - 5 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, true, false, false);
                            } else if ($x === $imageSize - 3 && $y === 4) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, true, false);
                            } else if ($x === $imageSize - 3 && $y === 2) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $inPointColor);
                            }
                        }

                    } elseif ($y > count($data) - 9 && $x < 7) { //左下角定点
                        if ($x === 0 || $y === $imageSize - 7 || $x === 6 || $y === $imageSize - 1) {
                            //液态
                            if ($x === 0 && $y === $imageSize - 7) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, true, false, false, false);
                            } else if ($x === 0 && $y === $imageSize - 1) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, true, false, false);
                            } else if ($x === 6 && $y === $imageSize - 1) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, true, false);
                            } else if ($x === 6 && $y === $imageSize - 7) {
                                $this->roundedCorner($img, $x, $y, $s, $pointColor, false, false, false, true);
                            } else {
                                imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $pointColor);
                            }


                        } else {
                            //液态
                            if ($x === 2 && $y === $imageSize - 5) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, true, false, false, false);
                            } else if ($x === 2 && $y === $imageSize - 3) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, true, false, false);
                            } else if ($x === 4 && $y === $imageSize - 3) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, true, false);
                            } else if ($x === 4 && $y === $imageSize - 5) {
                                $this->roundedCorner($img, $x, $y, $s, $inPointColor, false, false, false, true);
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
                            imagefilledellipse($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, $frontColor);
                        } elseif ($t == 0 && $l == 0 && $r == 0) {
                            //上半圆
                            $this->halfRounded($img, $x, $y, $s, $frontColor, true, false, false, false);
                        } elseif ($t == 0 && $l == 0 && $b == 0) {
                            //左半圆
                            $this->halfRounded($img, $x, $y, $s, $frontColor, false, true, false, false);
                        } elseif ($l == 0 && $b == 0 && $r == 0) {
                            //下半圆
                            $this->halfRounded($img, $x, $y, $s, $frontColor, false, false, true, false);
                        } elseif ($t == 0 && $b == 0 && $r == 0) {
                            //右半圆
                            $this->halfRounded($img, $x, $y, $s, $frontColor, false, false, false, true);
                        } elseif ($t == 0 && $l == 0) {
                            //左上角
                            $this->roundedCorner($img, $x, $y, $s, $frontColor, true, false, false, false);
                        } elseif ($l == 0 && $b == 0) {
                            //左下角
                            $this->roundedCorner($img, $x, $y, $s, $frontColor, false, true, false, false);
                        } elseif ($b == 0 && $r == 0) {
                            //右下角
                            $this->roundedCorner($img, $x, $y, $s, $frontColor, false, false, true, false);
                        } elseif ($r == 0 && $t == 0) {
                            //右上角
                            $this->roundedCorner($img, $x, $y, $s, $frontColor, false, false, false, true);
                        } else {
                            //直角
                            imagefilledrectangle($img, $x * $this->pointSize, $y * $this->pointSize, ($x + 1) * $this->pointSize, ($y + 1) * $this->pointSize, $frontColor);

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
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $frontColor, true, false, false, false);
                        }

                        if ($l == 1 && $lb == 1 && $b == 1) {
                            //左下角
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $frontColor, false, true, false, false);
                        }
                        if ($b == 1 && $rb == 1 && $r == 1) {
                            //右下角
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $frontColor, false, false, true, false);
                        }
                        if ($r == 1 && $rt == 1 && $t == 1) {
                            //右上角
                            $this->halfCorner($img, $x, $y, $s, $bgColor, $frontColor, false, false, false, true);
                        }
                    }

                }
                $x++;
            }
            $y++;
        }
        return $img;
    }
}