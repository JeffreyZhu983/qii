<?php
namespace Qii\Library;

class Image
{
	public $config = ['width' => 100, 'height' => 100];
	public $error = [];

	public function __construct($config)
	{
		if(is_array($config)){
			$this->config = array_merge($this->config, $config);
		}
	}

	public function setError($key, $val)
	{
		$this->error[$key] = $val;
	}


	/**
	 * 根据配置文件批量缩放图片
	 * @param $images 图片路径
	 * @param $config [{width: 100, height: 100}, ...]
	 * @return array
	 */
	public function autoResize($images, $config)
	{
		$config = array_merge($this->config, $config);
		$data = array();
		if (is_array($images)) {
			foreach ($images AS $image) {
				$data[] = $this->autoResize($image, $config);
			}
		} else {
			$thumbs = array();
			foreach ($config['size'] AS $key => $value) {
				$pathInfo = pathinfo($images);
				$thumbs[$key] = $small = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $key . '.' . $pathInfo['extension'];
				$this->cutScale($images, $small, $config['size'][$key]['width'], $config['size'][$key]['height']);
				$this->resizeSamll($small, $small, $config['size'][$key]['width']);
			}
			return $thumbs;
		}
		return $data;
	}

	/**
	 * 图片缩放,等比缩放
	 *
	 * @param String $bigImg 原图
	 * @param String $smallImg 缩放以后的图片
	 * @param Int $width 宽度
	 * @return Bool
	 */
	public function resizeSmall($bigImg, $smallImg, $width = 392)
	{
			// 图片路径
		if (!file_exists($bigImg)) {
			$this->setError(__METHOD__, $bigImg . "文件不存在");
			return false;
		} else {
			ini_set("memory_limit", "128M");
			$fileName = $bigImg;
			// 获取原图片的尺寸
			list($widthOrig, $heightOrig) = getimagesize($fileName);
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
					$image = imagecreatefromjpeg($fileName);
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
	public function cutScale($bigImg, $smallImg = 'test.jpg', $width = 90, $height = 130)
	{	
		if (!file_exists($bigImg)) {
			$this->setError(__METHOD__, $bigImg . "文件不存在");
			return false;
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

		$srcW = imagesx($im);//获取大图宽
		$srcH = imagesy($im);//获取大图高

		//计算比例
		//检查图片高度和宽度
		$srcScale = sprintf("%.2f", ($srcW / $srcH));//原图比例
		$destScale = sprintf("%.2f", ($width / $height));//缩略图比例

		//echo "<p>原始比例:".$srcScale."；目标比例".$destScale."</p>";
		if ($srcScale > $destScale) {
			//说明高度不够,就以高度为准
			$myH = $srcH;
			$myW = intval($srcH * ($width / $height));
			//获取开始位置
			$myY = 0;
			$myX = intval(($srcW - $myW) / 2);
		} elseif ($srcScale < $destScale) {
			//宽度不够就以宽度为准
			$myW = $srcW;
			$myH = intval($srcW * ($height / $width));
			$myX = 0;
			$myY = intval(($srcH - $myH) / 2);
		} else {
			if ($srcW > $srcH) {
				//echo "<p>case 1:</p>";
				$myH = $srcH;
				$myW = intval($srcH * ($width / $height));
				//获取开始位置
				$myY = 0;
				$myX = intval(($srcW - $myW) / 2);
			}
			if ($srcW < $srcH) {
				//echo "case 2";
				$myW = $srcW;
				$myH = intval($srcW * ($height / $width));
				$myX = 0;
				$myY = intval(($srcH - $myH) / 2);
			}
		}
		if ($srcW == $srcH) {
			$myW = intval($srcH * ($width / $height));
			$myH = $srcH;

			$myX = intval(($srcW - $myW) / 2);
			$myY = 0;
		}
		//echo "<p>SW:" . $srcW ."W:" .$myW . "</p><p>X".$myX."</p><p>SH".$srcH.";H:" . $myH ."<p>Y".$myY."</p>";
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
	public function cutSmall($bigImg, $smallImg, $width, $height)
	{
		if (!file_exists($bigImg)) {
			$this->setError(__METHOD__, $bigImg . "文件不存在");
			return false;
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
		$srcW = imagesx($im);//获取大图宽
		$srcH = imagesy($im);//获取大图高
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
	public function resizeMaxSize($bigImg, $smallImg, $maxValue = 392)
	{
		// 图片路径
		if (!file_exists($bigImg)) {
			$this->setError(__METHOD__, $bigImg . "文件不存在");
			return false;
		}
		ini_set("memory_limit", "128M");
		$fileName = $bigImg;
		// 获取原图片的尺寸
		list($widthOrig, $heightOrig) = getimagesize($fileName);
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
				$image = imagecreatefromjpeg($fileName);
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