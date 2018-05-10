<?php
/**
 * 圆角
 */
namespace QrCode;

class RoundedCorner
{
    private $_r;
    private $_g;
    private $_b;
    private $_image_path;
    private $_radius;

    function __construct($image_path, $radius, $r = 255, $g = 0, $b = 0)
    {
        $this->_image_path = $image_path;
        $this->_radius = $radius;
        $this->_r = (int)$r;
        $this->_g = (int)$g;
        $this->_b = (int)$b;
    }

    private function _get_lt_rounder_corner()
    {
        $radius = $this->_radius;
        //$radius=20;
        $img = imagecreatetruecolor($radius, $radius);

        $bgcolor = imagecolorallocate($img, $this->_r, $this->_g, $this->_b);
        $fgcolor = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $bgcolor);

        imagefilledarc($img, $radius, $radius, $radius * 2, $radius * 2, 180, 270, $fgcolor, IMG_ARC_PIE);
        //imagefilledarc在指定的 image 上画一椭圆弧且填充。

        imagecolortransparent($img, $fgcolor);
        //imagecolortransparent() 将 image 图像中的透明色设定为 color。
        //image 是 imagecreatetruecolor() 返回的图像标识符，
        //color 是 imagecolorallocate() 返回的颜色标识符。

        //imagejpeg($img);
        return $img;
    }


    public function round_it($srcImage, $lt = true, $lb = true, $rb = true, $rt = true)
    {
        // load the source image
        if ($srcImage === false) {
            die('对不起，图片加载失败');
        }
        $imageWidth = imagesx($srcImage);
        $imageHeight = imagesy($srcImage);

        // create a new image, with src_width, src_height, and fill it with transparent color
        $image = imagecreatetruecolor($imageWidth, $imageHeight);
        $transColor = imagecolorallocate($image, $this->_r, $this->_g, $this->_b);
        imagefill($image, 0, 0, $transColor);

        // then overwirte the source image to the new created image
        imagecopymerge($image, $srcImage, 0, 0, 0, 0, $imageWidth, $imageHeight, 100);

        // then just copy all the rounded corner images to the 4 corners
        $radius = $this->_radius;
        // lt
        $ltCorner = $this->_get_lt_rounder_corner();

        if ($lt) {
            imagecopymerge($image, $ltCorner, 0, 0, 0, 0, $radius, $radius, 100);
        }

        // lb
        $lb_corner = imagerotate($ltCorner, 90, $transColor);
        //imagerotate将 srcIm 图像用给定的 angle 角度旋转。bgd_color 指定了旋转后没有覆盖到的部分的颜色。
        //旋转的中心是图像的中心，旋转后的图像会按比例缩小以适合目标图像的大小——边缘不会被剪去。

        if ($lb) {
            imagecopymerge($image, $lb_corner, 0, $imageHeight - $radius, 0, 0, $radius, $radius, 100);
        }

        // rb
        $rb_corner = imagerotate($ltCorner, 180, $transColor);
        if ($rb) {
            imagecopymerge($image, $rb_corner, $imageWidth - $radius, $imageHeight - $radius, 0, 0, $radius, $radius, 100);
        }

        // rt
        $rtCorner = imagerotate($ltCorner, 270, $transColor);
        if ($rt) {
            imagecopymerge($image, $rtCorner, $imageWidth - $radius, 0, 0, 0, $radius, $radius, 100);
        }

        // set the transparency
        imagecolortransparent($image, $transColor);

        return $image;

    }
}