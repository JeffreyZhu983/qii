<?php
/**
 * 图片裁切，将大图，切成小图
 * 用法：
 * $pixes = new Pixes();
 * $pixes->maxWidth = 200;
 * $pixes->maxHeight = 100;
 * $pixes->cutPixes($fileName, $dir);
 */
namespace Qii\Library;

class Pixes
{
    /**
     * @var int $maxWidth 图片的最大宽度
     */
    public $maxWidth = 800;
    /**
     * @var int $maxHeight 图片的最大高度
     */
    public $maxHeight = 600;
    
    
    public function __construct()
    {
        return $this;
    }
    
    /**
     * 获取文件的相关信息
     * @param string $src 图片链接
     * @return array
     * @throws Exception
     */
    public function getImageSize($src)
    {
        $size = @getimagesize($src);
        if(!$size)
        {
            throw new \Exception('未知图片', __LINE__);
        }
        return ['width' => $size[0], 'height' => $size[1], 'type' => $size[2], 'mime' => $size['mime'], 'bits' => $size['bits']];
    }
    
    /**
     * 计算图片需要切成的块数及相应的坐标
     *
     * @param string $src 图片地址
     * @return array
     */
    public function getPixes($src)
    {
        $imageInfo = $this->getImageSize($src);
        //横向
        $horizontal = [];
        //纵向
        $vertical = [];
        
        $horizontalCount = ceil($imageInfo['width'] / $this->maxWidth);
        $verticalCount = ceil($imageInfo['height'] / $this->maxHeight);
        
        for($i = 0; $i < $horizontalCount; $i++)
        {
            $x = $i * $this->maxWidth;
            $width = ($x + $this->maxWidth > $imageInfo['width'] ? $imageInfo['width'] - $x : $this->maxWidth) - 1;
            $horizontal[] = [$x, $width];
        }
        for($i = 0; $i < $verticalCount; $i++)
        {
            $y = $i * $this->maxHeight;
            $height = ($y + $this->maxHeight > $imageInfo['height'] ? $imageInfo['height'] - $y : $this->maxHeight) - 1;
            $vertical[] = [$y, $height];
        }
        
        $pixes = ['info' => $imageInfo, 'horizontal' => $horizontal, 'vertical' => $vertical];
        $pix = [];
        
        for($i = 0 ; $i < $verticalCount; $i++)
        {
            for($j = 0; $j < $horizontalCount; $j++)
            {
                //x, y, width, height
                $pix[$i .'-'. $j] = [$horizontal[$j][0], $vertical[$i][0], $horizontal[$j][1], $vertical[$i][1]];
            }
        }
        
        $pixes['horizontalCount'] = (int) $horizontalCount;
        $pixes['verticalCount'] = (int) $verticalCount;
        $pixes['pixes'] = $pix;
        return $pixes;
    }
    
    /**
     * 裁切图片
     * @param string $src 图片地址
     * @param string $dir 保存裁切后的图片路径
     * @return array
     * @throws Exception
     */
    public function cutPixes($src, $dir = '')
    {
        $pixes = $this->getPixes($src);
        switch ($pixes['info']['type']) {//判断图像类型
            case 1:
                $image = imagecreatefromgif($src);
                break;
            case 2:
                $image = imagecreatefromjpeg($src);
                break;
            case 3:
                $image = imagecreatefrompng($src);
                $color = imagecolorallocate($image, 255, 255, 255);
                imagecolortransparent($image, $color);
                imagefill($image, 0, 0, $color);
                break;
            default:
                $image = imagecreatefromjpeg($src);
                break;
        }
        foreach($pixes['pixes'] as $name => $pix)
        {
            $name = ($dir ? $dir .'/': '') . $name;
            $pixes['info']['ext'] = 'jpg';
            $small = imagecreatetruecolor($pix[2], $pix[3]);
            switch ($pixes['info']['type'])
            {
                case 3:
                    $pixes['info']['ext'] = 'png';
                    imagecopy($small, $image, 0, 0, $pix[0], $pix[1], $pix[2], $pix[3]);
                    imagejpeg($small, $name . '.png', 100);
                    break;
                default:
                    imagecopy($small, $image, 0, 0, $pix[0], $pix[1], $pix[2], $pix[3]);
                    imagejpeg($small, $name . '.jpg', 100);
                    break;
            }
            imagedestroy($small);
        }
        $log = ($dir ? $dir .'/': '') . 'cut-info.php';
        if(!file_put_contents($log, "<?php\nreturn ". var_export($pixes, true) . ";\n"))
        {
            throw new \Exception($log . '写入失败，请确保目录的权限是否设置正确');
        }
        return $pixes;
    }
}