<?php
namespace QrCode\traits;

trait Fill
{
    //半角
    public function halfCorner($img, $x, $y, $s, $bgColor, $frontColor, $lt = true, $lb = true, $rb = true, $rt = true)
    {
        //左上半角
        if ($lt) {
            imagefilledarc($img, $x * $s, $y * $s, $s / 2, $s / 2, 0, 90, $frontColor, IMG_ARC_PIE);
            imagefilledarc($img, $x * $s + $s / 4, $y * $s + $s / 4, $s / 2, $s / 2, 180, 270, $bgColor, IMG_ARC_PIE);
        }
        //左下半角
        if ($lb) {
            imagefilledarc($img, $x * $s, ($y + 1) * $s, $s / 2, $s / 2, 270, 360, $frontColor, IMG_ARC_PIE);
            imagefilledarc($img, $x * $s + $s / 4, ($y + 1) * $s - $s / 4, $s / 2, $s / 2, 90, 180, $bgColor, IMG_ARC_PIE);
        }
        //右下半角
        if ($rb) {
            imagefilledarc($img, ($x + 1) * $s, ($y + 1) * $s, $s / 2, $s / 2, 180, 270, $frontColor, IMG_ARC_PIE);
            imagefilledarc($img, ($x + 1) * $s - $s / 4, ($y + 1) * $s - $s / 4, $s / 2, $s / 2, 0, 90, $bgColor, IMG_ARC_PIE);

        }
        //右上半角
        if ($rt) {
            imagefilledarc($img, ($x + 1) * $s, $y * $s, ($s / 2), ($s / 2), 90, 180, $frontColor, IMG_ARC_PIE);
            imagefilledarc($img, ($x + 1) * $s - ($s / 4), $y * $s + ($s / 4), ($s / 2), ($s / 2), 270, 360, $bgColor, IMG_ARC_PIE);
        }

    }

    //半圆
    public function halfRounded($img, $x, $y, $s, $color, $t = true, $l = true, $b = true, $r = true)
    {
        //上半圆
        if ($t) {
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 180, 270, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 270, 360, $color, IMG_ARC_PIE);
            imagefilledrectangle($img, $x * $s, ($y * $s) + ($s / 2), ($x + 1) * $s, ($y + 1) * $s, $color);
        }
        //左半圆
        if ($l) {
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 90, 180, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 180, 270, $color, IMG_ARC_PIE);
            imagefilledrectangle($img, ($x * $s) + ($s / 2), ($y * $s), ($x + 1) * $s, ($y + 1) * $s, $color);
        }

        //下半圆
        if ($b) {
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 0, 90, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 90, 180, $color, IMG_ARC_PIE);
            imagefilledrectangle($img, $x * $s, $y * $s, ($x + 1) * $s, ($y * $s) + ($s / 2), $color);
        }
        //右半圆
        if ($r) {
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 270, 360, $color, IMG_ARC_PIE);
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 0, 90, $color, IMG_ARC_PIE);
            imagefilledrectangle($img, $x * $s, $y * $s, ($x * $s) + ($s / 2), ($y + 1) * $s, $color);

        }
    }

    //圆角
    public function roundedCorner($img, $x, $y, $s, $color, $lt = true, $lb = true, $rb = true, $rt = true)
    {
        if ($lt) {
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 180, 270, $color, IMG_ARC_PIE);
            $values = array(($x + 1) * $s, ($y * $s), ($x + 1) * $s, ($y + 1) * $s, ($x * $s), ($y + 1) * $s,);
            $values1 = array(($x * $s), ($y + 1) * $s, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), ($x * $s), ($y * $s) + ($s / 2),);
            $values2 = array(($x * $s) + ($s / 2), ($y * $s) + ($s / 2), ($x * $s) + ($s / 2), ($y * $s), ($x + 1) * $s, ($y * $s),);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
        if ($lb) {
            imagefilledarc($img, ($x * $s) + ($s / 2), ($y * $s) + ($s / 2), $s, $s, 90, 180, $color, IMG_ARC_PIE);
            $values = array($x * $s, $y * $s, $x * $s + $s, $y * $s, $x * $s + $s, ($y + 1) * $s,);
            $values1 = array($x * $s, $y * $s, $x * $s, $y * $s + ($s / 2), $x * $s + $s, $y * $s + ($s / 2),);
            $values2 = array($x * $s + ($s / 2), $y * $s + ($s / 2), $x * $s + ($s / 2), ($y + 1) * $s, $x * $s + $s, ($y + 1) * $s,);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
        if ($rb) {
            imagefilledarc($img, ($x * $s) + ($s / 2), $y * $s + ($s / 2), $s, $s, 360, 90, $color, IMG_ARC_PIE);
            $values = array($x * $s, ($y + 1) * $s, $x * $s, $y * $s, ($x + 1) * $s, $y * $s,);
            $values1 = array($x * $s, ($y + 1) * $s, $x * $s + ($s / 2), $y * $s + ($s / 2), $x * $s + ($s / 2), ($y + 1) * $s,);
            $values2 = array($x * $s + ($s / 2), $y * $s + ($s / 2), ($x + 1) * $s, $y * $s, ($x + 1) * $s, $y * $s + ($s / 2),);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
        if ($rt) {
            imagefilledarc($img, ($x * $s) + ($s / 2), $y * $s + ($s / 2), $s, $s, 270, 360, $color, IMG_ARC_PIE);
            $values = array($x * $s, $y * $s, $x * $s, $y * $s + $s, ($x + 1) * $s, $y * $s + $s,);
            $values1 = array($x * $s, $y * $s, $x * $s + ($s / 2), $y * $s, $x * $s + ($s / 2), $y * $s + ($s / 2),);
            $values2 = array($x * $s + ($s / 2), $y * $s + ($s / 2), ($x + 1) * $s, $y * $s + ($s / 2), ($x + 1) * $s, $y * $s + $s,);
            imagefilledpolygon($img, $values, 3, $color);
            imagefilledpolygon($img, $values1, 3, $color);
            imagefilledpolygon($img, $values2, 3, $color);
        }
    }

    /**
     * 缩放图片
     * @param type $im
     * @param type $maxwidth
     * @param type $maxheight
     * @return type
     */
    public function resizeImage($im, $maxwidth, $maxheight)
    {
        $picWidth = imagesx($im);
        $picHeight = imagesy($im);

        $newim = imagecreatetruecolor($maxwidth, $maxheight);
        ImageCopyResampled($newim, $im, 0, 0, 0, 0, $maxwidth, $maxheight, $picWidth, $picHeight);
        imagedestroy($im);

        return $newim;

    }

    //增加背景
    public function imageAddBG($im, $bgpath)
    {

        //计算宽和高
        $w = imagesx($im);
        $h = imagesy($im);

        //加载logo
        $ext = substr($bgpath, strrpos($bgpath, '.'));
        if (empty($ext)) {
            return false;
        }
        switch (strtolower($ext)) {
            case '.jpg':
                $srcIm = @imagecreatefromjpeg($bgpath);
                break;
            case '.gif':
                $srcIm = @imagecreatefromgif($bgpath);
                break;
            case '.png':
                $srcIm = @imagecreatefrompng($bgpath);
                break;

        }

        $bgw = imagesx($srcIm);
        $bgh = imagesy($srcIm);
        imagecopymerge($srcIm, $im, ($bgw / 2) - ($w / 2), ($bgh / 2) - ($h / 2), 0, 0, $w, $h, 100);
        imagedestroy($im);
        return $srcIm;
    }

    //图片增加logo
    public function imageAddLogo($im, $logo)
    {

        //计算宽和高
        $w = imagesx($im);
        $h = imagesy($im);

        //加载logo
        $ext = pathinfo($logo, PATHINFO_EXTENSION);

        if (empty($ext)) {
            return false;
        }
        switch (strtolower($ext)) {
            case 'jpg':
                $srcIm = @imagecreatefromjpeg($logo);
                break;
            case 'gif':
                $srcIm = @imagecreatefromgif($logo);
                break;
            case 'png':
                $srcIm = @imagecreatefrompng($logo);
                break;

        }
        $srcIm = $this->resizeImage($srcIm, min(36, $w / 5), min(36, $h / 5));
        $srcWidth = imagesx($srcIm);
        $srcHeight = imagesy($srcIm);


        //logo边框1 小
        $bor1 = ImageCreate($srcWidth + 2, $srcHeight + 2);
        ImageColorAllocate($bor1, 237, 234, 237);//背景色
        $bor1Width = imagesx($bor1);
        $bor1Height = imagesy($bor1);

        //logo边框2 中
        $bor2 = ImageCreate($bor1Width + 8, $bor1Height + 8);
        ImageColorAllocate($bor2, 255, 255, 255);//背景色
        $bor2_w = imagesx($bor2);
        $bor2_h = imagesy($bor2);

        //logo边框3 大
        $bor3 = ImageCreate($bor2_w + 2, $bor2_h + 2);
        ImageColorAllocate($bor3, 215, 215, 215);//背景色
        $bor3Width = imagesx($bor3);
        $bor3Height = imagesy($bor3);

        //圆角处理
        $rounder = new \QrCode\RoundedCorner('', 5);

        //二维码与logo边框3合并
        $bor3 = $rounder->round_it($bor3);
        imagecopymerge($im, $bor3, ($w / 2) - ($bor3Width / 2), ($h / 2) - ($bor3Height / 2), 0, 0, $bor3Width, $bor3Height, 100);
        imagedestroy($bor3);

        //二维码与logo边框2合并
        $bor2 = $rounder->round_it($bor2);
        imagecopymerge($im, $bor2, ($w / 2) - ($bor2_w / 2), ($h / 2) - ($bor2_h / 2), 0, 0, $bor2_w, $bor2_h, 100);
        imagedestroy($bor2);

        //二维码与logo边框1合并
        $bor1 = $rounder->round_it($bor1);
        imagecopymerge($im, $bor1, ($w / 2) - ($bor1Width / 2), ($h / 2) - ($bor1Height / 2), 0, 0, $bor1Width, $bor1Height, 100);
        imagedestroy($bor1);

        //二维码与logo合并
        $srcIm = $rounder->round_it($srcIm);
        imagecopymerge($im, $srcIm, ($w / 2) - ($srcWidth / 2), ($h / 2) - ($srcHeight / 2), 0, 0, $srcWidth, $srcHeight, 100);
        imagedestroy($srcIm);
        return $im;
    }

    /**
     * 16进制颜色转换为RGB色值
     * @method hex2rgb
     */
    public function hex2rgb($hexColor)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {

            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {

            $color = str_replace('#', '', $hexColor);
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }

        return $rgb;
    }
}