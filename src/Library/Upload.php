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
	public $mine = array (
	  'image/gif' => 'gif',
	  'image/jpeg' => 'jpg',
	  'image/bmp' => 'bmp',
	  'image/png' => 'png',
	  'image/tiff' => 'tiff',
	  'image/x-pict' => 'pict',
	  'image/x-photoshop' => 'psd',
	  'application/x-shockwave-flash' => 'swf',
	  'application/x-javascript' => 'js',
	  'application/pdf' => 'pdf',
	  'application/postscript' => 'ps',
	  'application/x-msmetafile' => 'wmf',
	  'application/x-httpd-php' => 'php',
	  'application/x-httpd-asp' => 'asp',
	  'application/x-httpd-aspx' => 'aspx',
	  'text/css' => 'css',
	  'text/ini' => 'ini',
	  'text/html' => 'html',
	  'text/plain' => 'txt',
	  'text/xml' => 'xml',
	  'text/wml' => 'wml',
	  'image/vnd.wap.wbmp' => 'wbmp',
	  'audio/midi' => 'mid',
	  'audio/wav' => 'wav',
	  'audio/mpeg' => 'mp3',
	  'audio/wma' => 'wma',
	  'video/x-msvideo' => 'avi',
	  'video/mpeg' => 'mpeg',
	  'video/quicktime' => 'mov',
	  'video/rm' => 'rm',
	  'video/rmvb' => 'rmvb',
	  'application/x-lha' => 'lzh',
	  'application/x-compress' => 'z',
	  'application/x-gtar' => 'gtar',
	  'application/x-gzip' => 'gzip',
	  'application/x-tar' => 'tar',
	  'application/bzip2' => 'bz2',
	  'application/zip' => 'zip',
	  'application/7z' => '7z',
	  'application/x-arj' => 'arj',
	  'application/x-rar-compressed' => 'rar',
	  'application/mac-binhex40' => 'hqx',
	  'application/x-stuffit' => 'sit',
	  'application/x-macbinary' => 'bin',
	  'text/x-uuencode' => 'uue',
	  'application/x-latex' => 'latex',
	  'application/x-tcl' => 'tcl',
	  'application/pgp' => 'pgp',
	  'application/x-msdownload' => 'exe',
	  'application/msword' => 'docx',
	  'application/rtf' => 'rtf',
	  'application/vnd.ms-excel' => 'xlsx',
	  'application/vnd.ms-powerpoint' => 'ppt',
	  'application/x-msaccess' => 'mdb',
	  'application/x-mswrite' => 'wri',
	  'application/octet-stream' => 'dll',
	  'application/x-bittorrent' => 'torrent',
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
		if(!is_array($configure) || empty($configure))
		{
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
		if(isset($_SERVER['HTTP_CONTENT_DISPOSITION'])
			&& preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',
			$_SERVER['HTTP_CONTENT_DISPOSITION'],$info)
		)
		{
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
		if(!is_array($configure) || empty($configure))
		{
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
		if(!isset($files['binary'])) $files['binary'] = false;
		if ($files['error'] != UPLOAD_ERR_OK) {
			$data['code'] = $files['error'];
			$data['src'] = '';
			$data['size'] = 0;
			$data['msg'] = $this->errorMessage[$files['error']];
			$this->setError($index, 100008);
			return $data;
		}
		$extension = pathinfo($files['name'], PATHINFO_EXTENSION);
		if(!$extension){
			if(isset($this->mine[$files['type']]))
			{
				$extension = $this->mine[$files['type']];
			}
		}
		if(!in_array($extension, $this->allowed))
		{
			$data['code'] = 1407;
			$data['src'] = '';
			$data['size'] = 0;
			$data['msg'] = 'Not Allowed';
			return $data;
		}

		$extension = '.'. $extension;

		$fileName = rand(10000, 90000) . uniqid();
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
		if($files['binary'])
		{
			$result = rename($files['tmp_name'], $fillPath . '/' . $configure['fileName']);
		}
		else
		{
			$result= move_uploaded_file($files['tmp_name'], $fillPath . '/' . $configure['fileName']);
		}
		if ($result) {
			$data['code'] = 0;
			$data['src'] = $fillPath . '/' . $configure['fileName'];
			$data['file_type'] = $files['type'];
			$data['file_hash'] = md5_file($data['src']);
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
		if($image[2] == 3)
		{
			$tn = imagecreate($myW, $myH);//创建小图
		}
		else
		{
			$tn = imagecreatetruecolor($myW, $myH);
		}
		
		imagecopy($tn, $im, 0, 0, $myX, $myY, $myW, $myH);
		if($image[2] == 3)
		{
			imagepng($tn, $smallImg, 9);
		}else{
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
		if($image[2] == 3)
		{
			$tn = imagecrate($width, $height);//创建小图
		}
		else
		{
			$tn = imagecreatetruecolor($width, $height);
		}
		
		imagecopy($tn, $im, 0, 0, 0, 0, $width, $height);
		if($image[2] == 3)
		{
			imagepng($tn, $smallImg, 9);
		}else{
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

?>