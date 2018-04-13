<?php

namespace Qii\Library;

/**
 * Library upload_library
 * @author Jinhui Zhu <jinhui.zhu@live.cn>
 * @version 1.2
 *
 * 使用方法：
 * $libUpload = new \Qii\Library\Upload();
 * $libUpload->setAllowed(array('mp3', 'mp4'));
 * $uploaded_files = $libUpload->upload('file', array('path' => 'data/tmp',
 *                        'maxSize' => 1024*1024,
 *                        'maxFolder' => 100，
 *                        'prefix' => '',
 * ));
 *
 */
class Upload
{
    const VERSION = '1.2';
    public $dir;
    public $name;
    public $allowed = array('jpg', 'gif', 'png');
    public $error;
    protected $errorMessage = array(
        1 => '文件超出大小限制',
        2 => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
        3 => '文件只有部分被上传',
        4 => '没有文件被上传',
        6 => '找不到临时文件夹',
        7 => '文件写入失败',
        8 => '上传被其它扩展中断'
    );
    public $mine = array(
        '*' => 'application/octet-stream',
        '323' => 'text/h323',
        'acx' => 'application/internet-property-stream',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'asf' => 'video/x-ms-asf',
        'asr' => 'video/x-ms-asf',
        'asx' => 'video/x-ms-asf',
        'au' => 'audio/basic',
        'avi' => 'video/x-msvideo',
        'axs' => 'application/olescript',
        'bas' => 'text/plain',
        'bcpio' => 'application/x-bcpio',
        'bin' => 'application/octet-stream',
        'bmp' => 'image/bmp',
        'c' => 'text/plain',
        'cat' => 'application/vnd.ms-pkiseccat',
        'cdf' => 'application/x-cdf',
        'cer' => 'application/x-x509-ca-cert',
        'class' => 'application/octet-stream',
        'clp' => 'application/x-msclip',
        'cmx' => 'image/x-cmx',
        'cod' => 'image/cis-cod',
        'cpio' => 'application/x-cpio',
        'crd' => 'application/x-mscardfile',
        'crl' => 'application/pkix-crl',
        'crt' => 'application/x-x509-ca-cert',
        'csh' => 'application/x-csh',
        'css' => 'text/css',
        'dcr' => 'application/x-director',
        'der' => 'application/x-x509-ca-cert',
        'dir' => 'application/x-director',
        'dll' => 'application/x-msdownload',
        'dms' => 'application/octet-stream',
        'doc' => 'application/msword',
        'dot' => 'application/msword',
        'dvi' => 'application/x-dvi',
        'dxr' => 'application/x-director',
        'eps' => 'application/postscript',
        'etx' => 'text/x-setext',
        'evy' => 'application/envoy',
        'exe' => 'application/octet-stream',
        'fif' => 'application/fractals',
        'flr' => 'x-world/x-vrml',
        'gif' => 'image/gif',
        'gtar' => 'application/x-gtar',
        'gz' => 'application/x-gzip',
        'h' => 'text/plain',
        'hdf' => 'application/x-hdf',
        'hlp' => 'application/winhlp',
        'hqx' => 'application/mac-binhex40',
        'hta' => 'application/hta',
        'htc' => 'text/x-component',
        'htm' => 'text/html',
        'html' => 'text/html',
        'htt' => 'text/webviewhtml',
        'ico' => 'image/x-icon',
        'ief' => 'image/ief',
        'iii' => 'application/x-iphone',
        'ins' => 'application/x-internet-signup',
        'isp' => 'application/x-internet-signup',
        'jfif' => 'image/pipeg',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'js' => 'application/x-javascript',
        'latex' => 'application/x-latex',
        'lha' => 'application/octet-stream',
        'lsf' => 'video/x-la-asf',
        'lsx' => 'video/x-la-asf',
        'lzh' => 'application/octet-stream',
        'm13' => 'application/x-msmediaview',
        'm14' => 'application/x-msmediaview',
        'm3u' => 'audio/x-mpegurl',
        'man' => 'application/x-troff-man',
        'mdb' => 'application/x-msaccess',
        'me' => 'application/x-troff-me',
        'mht' => 'message/rfc822',
        'mhtml' => 'message/rfc822',
        'mid' => 'audio/mid',
        'mny' => 'application/x-msmoney',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp2' => 'video/mpeg',
        'mp3' => 'audio/mpeg',
        'mpa' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpp' => 'application/vnd.ms-project',
        'mpv2' => 'video/mpeg',
        'ms' => 'application/x-troff-ms',
        'mvb' => 'application/x-msmediaview',
        'nws' => 'message/rfc822',
        'oda' => 'application/oda',
        'p10' => 'application/pkcs10',
        'p12' => 'application/x-pkcs12',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7c' => 'application/x-pkcs7-mime',
        'p7m' => 'application/x-pkcs7-mime',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'p7s' => 'application/x-pkcs7-signature',
        'pbm' => 'image/x-portable-bitmap',
        'pdf' => 'application/pdf',
        'pfx' => 'application/x-pkcs12',
        'pgm' => 'image/x-portable-graymap',
        'pko' => 'application/ynd.ms-pkipko',
        'pma' => 'application/x-perfmon',
        'pmc' => 'application/x-perfmon',
        'pml' => 'application/x-perfmon',
        'pmr' => 'application/x-perfmon',
        'pmw' => 'application/x-perfmon',
        'pnm' => 'image/x-portable-anymap',
        'pot,' => 'application/vnd.ms-powerpoint',
        'ppm' => 'image/x-portable-pixmap',
        'pps' => 'application/vnd.ms-powerpoint',
        'ppt' => 'application/vnd.ms-powerpoint',
        'prf' => 'application/pics-rules',
        'ps' => 'application/postscript',
        'pub' => 'application/x-mspublisher',
        'qt' => 'video/quicktime',
        'ra' => 'audio/x-pn-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'ras' => 'image/x-cmu-raster',
        'rgb' => 'image/x-rgb',
        'rmi' => 'audio/mid http://www.dreamdu.com',
        'roff' => 'application/x-troff',
        'rtf' => 'application/rtf',
        'rtx' => 'text/richtext',
        'scd' => 'application/x-msschedule',
        'sct' => 'text/scriptlet',
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'sit' => 'application/x-stuffit',
        'snd' => 'audio/basic',
        'spc' => 'application/x-pkcs7-certificates',
        'spl' => 'application/futuresplash',
        'src' => 'application/x-wais-source',
        'sst' => 'application/vnd.ms-pkicertstore',
        'stl' => 'application/vnd.ms-pkistl',
        'stm' => 'text/html',
        'svg' => 'image/svg+xml',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'swf' => 'application/x-shockwave-flash',
        't' => 'application/x-troff',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tgz' => 'application/x-compressed',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'tr' => 'application/x-troff',
        'trm' => 'application/x-msterminal',
        'tsv' => 'text/tab-separated-values',
        'txt' => 'text/plain',
        'uls' => 'text/iuls',
        'ustar' => 'application/x-ustar',
        'vcf' => 'text/x-vcard',
        'vrml' => 'x-world/x-vrml',
        'wav' => 'audio/x-wav',
        'wcm' => 'application/vnd.ms-works',
        'wdb' => 'application/vnd.ms-works',
        'wks' => 'application/vnd.ms-works',
        'wmf' => 'application/x-msmetafile',
        'wps' => 'application/vnd.ms-works',
        'wri' => 'application/x-mswrite',
        'wrl' => 'x-world/x-vrml',
        'wrz' => 'x-world/x-vrml',
        'xaf' => 'x-world/x-vrml',
        'xbm' => 'image/x-xbitmap',
        'xla' => 'application/vnd.ms-excel',
        'xlc' => 'application/vnd.ms-excel',
        'xlm' => 'application/vnd.ms-excel',
        'xls' => 'application/vnd.ms-excel',
        'xlt' => 'application/vnd.ms-excel',
        'xlw' => 'application/vnd.ms-excel',
        'xof' => 'x-world/x-vrml',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'z' => 'application/x-compress',
        'zip' => 'application/zip',
    );
    
    public function __construct()
    {
    
    }
    
    
    /**
     * 设置允许上传的文件类型
     * @param array $array 允许上传的文件类型
     */
    public function setAllowed(array $array)
    {
        $this->allowed = $array;
    }
    
    /**
     * setError
     *
     * @param String $key
     * @param Mix $value
     */
    public function setError($key, $value)
    {
        $this->error[$key] = $value;
    }
    
    /**
     * 获取错误信息
     *
     * @param String $key
     * @return Mix
     */
    public function getError($key)
    {
        if (!empty($key)) {
            return $this->error[$key];
        }
        return $this->error;
    }
    
    /**
     * 上传过程中是否有错误
     */
    public function isError()
    {
        return count($this->error) > 0 ? true : false;
    }
    
    /**
     * 是否为二进制上传
     */
    public function isBinaryUpload()
    {
        return isset($_SERVER['HTTP_CONTENT_DISPOSITION'])
            && preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',
                $_SERVER['HTTP_CONTENT_DISPOSITION']);
    }
    
    /**
     * html5上传
     */
    public function uploadBinary($configure)
    {
        $data = array();
        if (!is_array($configure) || empty($configure)) {
            $data['code'] = 1406;
            $data['src'] = '';
            $data['msg'] = 'Configure file is empty';
            return $data;
        }
        //如果文件路径不存在就自动创建
        if (!is_dir($configure['path'])) {
            mkdir($configure['path'], 0777, true);
        }
        if (!isset($configure['prefix'])) $configure['prefix'] = '';
        
        $tmpFile = sys_get_temp_dir() . DS . time() . rand(10000, 1000000);
        if (isset($_SERVER['HTTP_CONTENT_DISPOSITION'])
            && preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',
                $_SERVER['HTTP_CONTENT_DISPOSITION'], $info)
        ) {
            file_put_contents($tmpFile, file_get_contents("php://input"));
            $name = urldecode($info[2]);
        }
        
        $file = array(
            'binary' => true,
            'name' => $name,
            'type' => '',
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => filesize($tmpFile)
        );
        return $this->deal($file, $configure);
    }
    
    /**
     * 上传文件
     * @param $files
     * @param array $configure
     * @return array
     */
    protected function deal($files, $configure = array(), $index = 0)
    {
        $data = array();
        if (!is_array($configure) || empty($configure)) {
            $data['code'] = 1406;
            $data['src'] = '';
            $data['msg'] = 'Configure file is empty';
            return $data;
        }
        if (is_array($files['name'])) {
            foreach ($files['name'] as $key => $value) {
                $file = array(
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );
                $data['files'][] = $this->deal($file, $configure, $key);
            }
            return $data;
        }
        if (!isset($files['binary'])) $files['binary'] = false;
        if ($files['error'] != UPLOAD_ERR_OK) {
            $data['code'] = $files['error'];
            $data['src'] = '';
            $data['size'] = 0;
            $data['msg'] = $this->errorMessage[$files['error']];
            $this->setError($index, 100008);
            return $data;
        }
        $extension = pathinfo($files['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        if (!in_array($files['type'], $this->mine) || !isset($this->mine[$extension])) {
            $data['code'] = 1407;
            $data['src'] = '';
            $data['size'] = 0;
            $data['extension'] = $extension;
            $data['msg'] = 'Not Allowed';
            return $data;
        }
        //如果设置允许所有文件上传就不检测
        if ($this->allowed[0] != '*' && !in_array($extension, $this->allowed)) {
            $data['code'] = 1407;
            $data['src'] = '';
            $data['size'] = 0;
            $data['extension'] = $extension;
            $data['msg'] = 'Not Allowed';
            return $data;
        }
        
        $extension = '.' . $extension;
        //如果是设置了保持文件名称的话，就不自动转换文件名
        if (isset($configure['keepFileName'])) {
            $fileName = pathinfo($files['name'], PATHINFO_FILENAME);
        } else {
            $fileName = rand(10000, 90000) . uniqid();
        }
        $configure['fileName'] = $configure['prefix'] . $fileName . $extension;
        $subDir = $this->distribution($fileName, $configure['maxFolder']);
        
        $fillPath = $configure['path'];
        if (!empty($subDir)) {
            $fillPath = $fillPath . '/' . $subDir;
        }
        if (!is_dir($fillPath)) {
            if (!mkdir($fillPath, 0777, true)) {
                $data['code'] = 100007;
                $data['src'] = 0;
                $data['msg'] = 'Access Denied';
                $this->setError($index, 100007);
                return $data;
            }
        }
        $realPath = rtrim($fillPath, '/') . '/' . toGBK($configure['fileName']);
        if ($files['binary']) {
            $result = rename($files['tmp_name'], $realPath);
        } else {
            $result = move_uploaded_file($files['tmp_name'], $realPath);
        }
        if ($result) {
            $data['code'] = 0;
            $data['src'] = toUTF8($realPath);
            $data['name'] = toUTF8($files['name']);
            $data['file_type'] = $files['type'];
            $data['file_hash'] = md5_file($realPath);
            $data['size'] = $files['size'];
        } else {
            $data['src'] = '';
            $data['code'] = 100005;
            $data['msg'] = 100005;
            $data['size'] = 0;
            $this->setError($index, 100005);
        }
        return $data;
    }
    
    /**
     * 上传文件
     *
     * @param String $filed 上传文件域名称
     * @param Array $configure array('path' => 'data/tmp',
     *                        'maxSize' => 1024*1024,
     *                        'maxFolder' => 100);
     */
    public function upload($filed = 'upload', $configure)
    {
        $data = array();
        if ($_FILES[$filed]) {
            if (!is_dir($configure['path'])) {
                mkdir($configure['path'], 0777, true);
            }
            if (!isset($configure['prefix'])) $configure['prefix'] = '';
            return $this->deal($_FILES[$filed], $configure);
        }
        return $data;
    }
    
    /**
     * 自动重命名文件，如果没有指定目录就重命名，否则移动文件
     *
     * @param Mix $oldName 原始文件名
     * @param Mix $newName 新文件名
     * @param Bool $keepExtension 是否保持当前文件名
     * @return Array|String
     */
    public function rename($oldName, $newName, $keepExtension = false)
    {
        $path = array();
        if (is_array($oldName)) {
            foreach ($oldName AS $key => $val) {
                $path[] = autoRename($val, $newName[$key]);
            }
        } else {
            $oldNameArray = pathinfo($oldName);
            $newNameArray = pathinfo($newName);
            
            $dirName = $oldNameArray['dirname'];
            //如果第二个文件夹没有包含路径就直接重新命名，否则移动到新的路径下
            if ($newNameArray['dirname'] == substr($newName, 0, strlen($newNameArray['dirname']))) {
                $dirName = $newNameArray['dirname'];
            }
            $fileName = $oldNameArray['filename'];
            if ($newNameArray['filename']) {
                $fileName = $newNameArray['filename'];
            }
            $extension = '.' . $oldNameArray['extension'];
            if (!isset($newNameArray['extension']) && !$keepExtension) {
                $extension = '';
            }
            $newPath = $dirName . '/' . $fileName . $extension;
            rename($oldName, $newPath);
            return $newPath;
        }
        return $path;
    }
    
    /**
     * 过滤images
     *
     * @param Array $images
     * @return Array
     */
    public function filterImages($images)
    {
        $data = array();
        if (!is_array($images)) {
            return $data;
        }
        foreach ($images AS $image) {
            if (!empty($image)) $data[] = $image;
        }
        return $data;
    }
    
    /**
     * 根据配置文件批量缩放图片
     * @param $images 图片路径
     * @param $configure [{width: 100, height: 100}, ...]
     * @return array
     */
    public function autoResize($images, $configure)
    {
        $data = array();
        if (is_array($images)) {
            foreach ($images AS $image) {
                $data[] = $this->autoResize($image, $configure);
            }
        } else {
            $thumbs = array();
            foreach ($configure['size'] AS $key => $value) {
                $pathInfo = pathinfo($images);
                $thumbs[$key] = $small = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $key . '.' . $pathInfo['extension'];
                $this->imgCutScale($images, $small, $configure['size'][$key]['width'], $configure['size'][$key]['height']);
                $this->imgResizeSamll($small, $small, $configure['size'][$key]['width']);
            }
            return $thumbs;
        }
        return $data;
    }
    
    /**
     * 计算存放的文件夹
     *
     * @param String $fileName 文件名
     * @param Int $maxFolder 文件夹数量
     * @return String
     */
    public function distribution($fileName, $maxFolder = 100)
    {
        if (empty($maxFolder) || $maxFolder == 1) {
            return '';
        }
        $folder = ord($fileName) % $maxFolder;
        if ($folder == 0) {
            return $maxFolder;
        }
        return $folder;
    }
    
    /**
     * 图片缩放,等比缩放
     *
     * @param String $bigImg 原图
     * @param String $smallImg 缩放以后的图片
     * @param Int $width 宽度
     * @return Bool
     */
    public function imgResizeSmall($bigImg, $smallImg, $width = 392)
    {
        // 图片路径
        if (!file_exists($bigImg)) {
            $this->setError('img_resize_samll', $bigImg . "文件不存在");
            return false;
        } else {
            ini_set("memory_limit", "128M");
            $filename = $bigImg;
            // 获取原图片的尺寸
            list($widthOrig, $heightOrig) = getimagesize($filename);
            //根据比例，计算新图片的尺寸
            $height = ($width / $widthOrig) * $heightOrig;
            //新建一个真彩色图像
            $destImage = imagecreate($width, $height);
            //从 JPEG 文件或 URL 新建一图像
            $imageInfo = getimagesize($bigImg);//获取大图信息
            switch ($imageInfo[2]) {//判断图像类型
                case 1:
                    $image = imagecreatefromgif($bigImg);
                    break;
                case 2:
                    $image = imagecreatefromjpeg($bigImg);
                    break;
                case 3:
                    $image = imagecreatefrompng($bigImg);
                    $color = imagecolorallocate($image, 255, 255, 255);
                    imagecolortransparent($image, $color);
                    imagefill($image, 0, 0, $color);
                    break;
                default:
                    $image = imagecreatefromjpeg($filename);
                    break;
            }
            //重采样拷贝部分图像并调整大小
            imagecopyresampled($destImage, $image, 0, 0, 0, 0, $width, $height, $widthOrig, $heightOrig);
            // 将图片保存到服务器
            imagejpeg($destImage, $smallImg, 100);
            //销毁图片，释放内存
            imagedestroy($destImage);
            return true;
        }
    }
    
    /**
     * 缩放并按给定长宽比裁切图片
     * @param string $bigImg 原图路径
     * @param string $smallImg 缩放以后的文件路径
     * @param int $width 缩放宽度
     * @param int $height 缩放高度
     */
    public function imgCutScale($bigImg, $smallImg = 'test.jpg', $width = 90, $height = 130)
    {
        if (!file_exists($bigImg)) {
            $this->setError('img_cut_scale', $bigImg . "文件不存在");
            return;
        }
        ini_set("memory_limit", "128M");
        //大图文件地址，缩略宽，缩略高，小图地址
        $image = getimagesize($bigImg);//获取大图信息
        switch ($image[2]) {//判断图像类型
            case 1:
                $im = imagecreatefromgif($bigImg);
                break;
            case 2:
                $im = imagecreatefromjpeg($bigImg);
                break;
            case 3:
                $im = imagecreatefrompng($bigImg);
                $color = imagecolorallocate($im, 255, 255, 255);
                imagecolortransparent($im, $color);
                imagefill($im, 0, 0, $color);
                break;
        }
        
        $src_W = imagesx($im);//获取大图宽
        $src_H = imagesy($im);//获取大图高
        
        //计算比例
        //检查图片高度和宽度
        $srcScale = sprintf("%.2f", ($src_W / $src_H));//原图比例
        $destScale = sprintf("%.2f", ($width / $height));//缩略图比例
        
        //echo "<p>原始比例:".$srcScale."；目标比例".$destScale."</p>";
        if ($srcScale > $destScale) {
            //说明高度不够,就以高度为准
            $myH = $src_H;
            $myW = intval($src_H * ($width / $height));
            //获取开始位置
            $myY = 0;
            $myX = intval(($src_W - $myW) / 2);
        } elseif ($srcScale < $destScale) {
            //宽度不够就以宽度为准
            $myW = $src_W;
            $myH = intval($src_W * ($height / $width));
            $myX = 0;
            $myY = intval(($src_H - $myH) / 2);
        } else {
            if ($src_W > $src_H) {
                //echo "<p>case 1:</p>";
                $myH = $src_H;
                $myW = intval($src_H * ($width / $height));
                //获取开始位置
                $myY = 0;
                $myX = intval(($src_W - $myW) / 2);
            }
            if ($src_W < $src_H) {
                //echo "case 2";
                $myW = $src_W;
                $myH = intval($src_W * ($height / $width));
                $myX = 0;
                $myY = intval(($src_H - $myH) / 2);
            }
        }
        if ($src_W == $src_H) {
            $myW = intval($src_H * ($width / $height));
            $myH = $src_H;
            
            $myX = intval(($src_W - $myW) / 2);
            $myY = 0;
        }
        //echo "<p>SW:" . $src_W ."W:" .$myW . "</p><p>X".$myX."</p><p>SH".$src_H.";H:" . $myH ."<p>Y".$myY."</p>";
        //从中间截取图片
        if ($image[2] == 3) {
            $tn = imagecreate($myW, $myH);//创建小图
        } else {
            $tn = imagecreatetruecolor($myW, $myH);
        }
        
        imagecopy($tn, $im, 0, 0, $myX, $myY, $myW, $myH);
        if ($image[2] == 3) {
            imagepng($tn, $smallImg, 9);
        } else {
            imagejpeg($tn, $smallImg, 100);//输出图像
        }
        
        imagedestroy($im);
    }
    
    /**
     *
     * 剪切圖片到指定大小
     * @param String $bigImg 原始圖片
     * @param Int $width 寬
     * @param Int $height高
     * @param String $smallImg 縮放後保存的圖片
     */
    public function imgCutSmall($bigImg, $smallImg, $width, $height)
    {
        if (!file_exists($bigImg)) {
            return;
        }
        ini_set("memory_limit", "128M");
        //大图文件地址，缩略宽，缩略高，小图地址
        $imgage = getimagesize($bigImg);//获取大图信息
        switch ($imgage[2]) {//判断图像类型
            case 1:
                $im = imagecreatefromgif($bigImg);
                break;
            case 2:
                $im = imagecreatefromjpeg($bigImg);
                break;
            case 3:
                $im = imagecreatefrompng($bigImg);
                break;
        }
        $src_W = imagesx($im);//获取大图宽
        $src_H = imagesy($im);//获取大图高
        if ($image[2] == 3) {
            $tn = imagecrate($width, $height);//创建小图
        } else {
            $tn = imagecreatetruecolor($width, $height);
        }
        
        imagecopy($tn, $im, 0, 0, 0, 0, $width, $height);
        if ($image[2] == 3) {
            imagepng($tn, $smallImg, 9);
        } else {
            imagejpeg($tn, $smallImg, 100);//输出图像
        }
        imagedestroy($im);
    }
    
    /**
     * 按比例缩放图片并限制图片的最大宽度或高度
     * @param string $bigImg 原图地址
     * @param string $smallImg 缩略图地址
     * @param int $maxValue 最大宽高
     */
    public function imgResizeMaxSize($bigImg, $smallImg, $maxValue = 392)
    {
        // 图片路径
        if (!file_exists($bigImg)) {
            $this->setError('img_resize_samll', $bigImg . "文件不存在");
            return false;
        } else {
            ini_set("memory_limit", "128M");
            $filename = $bigImg;
            // 获取原图片的尺寸
            list($widthOrig, $heightOrig) = getimagesize($filename);
            $width = $widthOrig;
            $height = $heightOrig;
            //根据比例，计算新图片的尺寸
            if ($widthOrig > $heightOrig) {
                if ($widthOrig > $maxValue) {
                    $width = $maxValue;
                    $height = ($maxValue / $widthOrig) * $heightOrig;
                }
            } else {
                if ($heightOrig > $maxValue) {
                    $height = $maxValue;
                    $width = ($maxValue / $heightOrig) * $widthOrig;
                }
            }
            //$height = ($width / $widthOrig) * $heightOrig;
            
            //新建一个真彩色图像
            $destImage = imagecreate($width, $height);
            //从 JPEG 文件或 URL 新建一图像
            $imageInfo = getimagesize($bigImg);//获取大图信息
            switch ($imageInfo[2]) {//判断图像类型
                case 1:
                    $image = imagecreatefromgif($bigImg);
                    break;
                case 2:
                    $image = imagecreatefromjpeg($bigImg);
                    break;
                case 3:
                    $image = imagecreatefrompng($bigImg);
                    $color = imagecolorallocate($image, 255, 255, 255);
                    imagecolortransparent($image, $color);
                    imagefill($image, 0, 0, $color);
                    break;
                default:
                    $image = imagecreatefromjpeg($filename);
                    break;
            }
            //重采样拷贝部分图像并调整大小
            imagecopyresampled($destImage, $image, 0, 0, 0, 0, $width, $height, $widthOrig, $heightOrig);
            // 将图片保存到服务器
            imagejpeg($destImage, $smallImg, 100);
            //销毁图片，释放内存
            imagedestroy($destImage);
            return true;
        }
    }
}