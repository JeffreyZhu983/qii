<?php
namespace Qii\Library;

ignore_user_abort(true);
class Download
{
	const VERSION = 1.0;
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
		$fileName = iconv("UTF-8", "GB2312//TRANSLIT", $fileName);
		header("Content-Disposition: attachment; filename=". $fileName);
		echo $string;
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
		if(file_exists($filePath))
		{
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
			$fileName = iconv("UTF-8", "GB2312//TRANSLIT", $fileName);
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
			$speed = 1000;
			//#$speed = 0;
			$sleep = $speed ? floor(( $chunk / ($speed*1024))*1000000) : 0;
			do
			{
				$buf = fread($file, $chunk);
				$sent += strlen($buf);
				echo $buf;
				ob_flush();
				flush();
				usleep($sleep);
				if(strlen($buf) ==0)
				{
					break;
				}
			}while(true);
			fclose($file);
			exit();
		}
	}
}