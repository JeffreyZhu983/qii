<?php
namespace Qii\Library;

set_time_limit (0);
//ignore_user_abort(true);
class Download
{
	const VERSION = 1.0;
	//下载速率
	public $speed = 1000;
	public function __construct()
	{
		
	}
	/**
	 * 以字符串的形式下载文件
	 *
	 * @param String $fileName 保存的文件名
	 * @param String $string 下载的内容
	 */
	public function downloadByString($fileName, $string)
	{
		header('Cache-Control: max-age=2592000');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		header("Content-type: application/octet-stream");
		header("Accept-Length: ".  sizeof($string));
		$fileName = toGBK(urlencode($fileName));
		header("Content-Disposition: attachment; filename=". $fileName);
		echo $string;
	}

	/**
	 * 断点续传
	 *
	 * @param string $filePath 文件路径
	 * @param string $fileName 下载后的文件名
	 * @param string $mime 文件类型，如果是空的话就直接下载
	 * @return bool
	 */
	public function downResume($filePath, $fileName = '', $mime = '')
	{
		if (!file_exists($filePath)) {
			return false;
		}
		if ($fileName == '') {
			$fileName = basename($filePath);
		}
		$size = filesize($filePath);
		$size2 = $size - 1;
		$range = 0;
		if (isset($_SERVER['HTTP_RANGE']))
		{
			header('HTTP /1.1 206 Partial Content');
			$range = str_replace('=', '-', $_SERVER['HTTP_RANGE']);
			$range = explode('-', $range);
			$range = trim($range[1]);
			header('Content-Length:' . $size);
			header('Content-Range: bytes ' . $range . '-' . $size2 . '/' . $size);
		}
		else
		{
			header('Content-Length:' . $size);
			header('Content-Range: bytes 0-' . $size2 . '/' . $size);
		}
		header('Accenpt-Ranges: bytes');
		if ($mime != '' && $mime != 'unlink')
		{
			header("Content-type: {$mime}");
		}
		else
		{
			header("Content-type: application/octet-stream");
		}
		header("Cache-control: public");
		header("Pragma: public");
		$fileName = toGBK(urlencode($fileName));
		header('Content-Dispositon:attachment; filename=' . $fileName);
		$fp = fopen($filePath, 'rb+');
		fseek($fp, $range);
		while (!feof($fp))
		{
			set_time_limit(0);
			print(fread($fp, 1024));
			flush();
			ob_flush();
		}
		fclose($fp);
	}
	/**
	 * 指定文件路径下载指定文件
	 *
	 * @param String $filePath 文件路径
	 * @param String $fileName 文件名
	 * @param String $mime 文件类型
	 * @param String $view 下载/打开文件
	 */
	public function download($filePath, $fileName = '', $mime = '', $view = 'download')
	{
		//设定不限制时间
		ignore_user_abort(false);
		set_time_limit(0);
		//转换文件路为GBK，避免无法访问中文文件
		$filePath = toGBK($filePath);
		if(!file_exists($filePath))
		{
			die('File '. $filePath .' does not exist.');
		}
		if($fileName == '')
		{
			$fileName = basename($filePath);
		}
		$file = fopen($filePath, "r"); // 打开文件
		// 输入文件标签
		header('Cache-Control: max-age=2592000');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		if($mime != '' && $mime != 'unlink')
		{
			header("Content-type: {$mime}");
		}
		else
		{
			header("Content-type: application/octet-stream");
		}
		header('Content-Encoding: none');
		header('Content-Transfer-Encoding: binary');
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".  filesize($filePath));
		$fileName = toGBK(urlencode($fileName));
		if($view == 'download')
		{
			header("Content-Disposition: attachment; filename=". $fileName);
		}
		else
		{
			header('Content-Disposition: inline;filename="'.$fileName.'"');
		}
		//输出固定长度的文件避免文件过大导致无法下载
		$chunk = 16384;
		$sleep = $this->speed ? floor(( $chunk / ($this->speed*1024))*1000000) : 0;
		do
		{
			$buf = fread($file, $chunk);
			echo $buf;
			ob_flush();
			flush();
			usleep($sleep);
			if(strlen($buf) == 0)
			{
				break;
			}
		}while(true);
		fclose($file);
	}
}