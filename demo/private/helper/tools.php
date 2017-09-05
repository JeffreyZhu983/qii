<?php
namespace helper;
class tools
{
	/**
	 * 将数组生成树结构
	 * @param Array $items
	 * @param String $id
	 * @param String $pid
	 * @param String $son
	 * @return Array
	 */
	public static function tree($items, $id = 'cid', $pid = 'pid', $son = 'children')
	{
		$tree = array(); //格式化的树
		$tmpMap = array();  //临时扁平数据
		foreach ($items as $item) {
			$tmpMap[$item[$id]] = $item;
		}
		foreach ($items as $item) {
			if ($item[$id] != $item[$pid] && isset($tmpMap[$item[$pid]])) {
				$tmpMap[$item[$pid]][$son][$item[$id]] = &$tmpMap[$item[$id]];
			} else {
				$tree[$item[$id]] = &$tmpMap[$item[$id]];
			}
		}
		return $tree;
	}

	/**
	 * 格式化文件大小
	 */
	public static function formatSize($bytes, $unit = "", $decimals = 2, $showUnit = true)
	{
		$units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
		$bytes = (int)$bytes;
		$value = 0;
		if ($bytes > 0) {
			if (!array_key_exists($unit, $units)) {
				$pow = floor(log($bytes) / log(1024));
				$unit = array_search($pow, $units);
			}
			$value = (intval($bytes) / pow(1024, floor($units[$unit])));
		}
		if (!is_numeric($decimals) || $decimals < 0) {
			$decimals = 2;
		}
		if ($unit == 'KB' && $value < 0.1) {
			$decimals = 0;
		}
		if ($showUnit) {
			return sprintf('%.' . $decimals . 'f' . $unit, $value);
		} else {
			return sprintf('%.' . $decimals . 'f ', $value);
		}
	}

	/**
	 * 获取用户访问目录路径
	 * @param string $path 目录
	 */
	public static function getVisitPath($path)
	{
		$usePath = explode('/', $path);
		$dirPathes = array();
		$lastDir = $usePath[0];
		foreach ($usePath AS $key => $path) {
			if (!$path) continue;
			$array = array();
			$array['name'] = $path;
			if ($key > 0) {
				$lastDir = $lastDir . '/' . $path;
			}
			$array['path'] = $lastDir;
			$array['url'] = _link('/dirs?path=' . urlencode($lastDir));
			$dirPathes[] = $array;
		}
		return $dirPathes;
	}

	public static function fileType($fullPath)
	{
		if (is_dir($fullPath)) return 'folder';
		return pathinfo($fullPath, PATHINFO_EXTENSION);
	}

	/**
	 * 是否是图片文件
	 * @param string $fullPath 文件路径
	 * @return bool
	 */
	public static function isImage($fullPath)
	{
		$fileType = self::fileType($fullPath);
		if (in_array($fileType, array('gif', 'jpg', 'jpeg', 'webp', 'png', 'bmp'))) {
			return true;
		}
		return false;
	}

	/**
	 * 获取目录中文件及目录
	 * @param string $path 目录
	 * @return array
	 */
	public static function getFolders($path, $filter = '*')
	{
		$dir = dir($path);
		$filetype = _include('../private/configure/filetype.config.php');
		$files = array();
		while (($file = $dir->read()) !== false) {
			if ($file == '.' || $file == '..') continue;
			$fullPath = str_replace('//', '/', $path . '/' . $file);
			$isDir = is_dir($fullPath);
			if ($filter != '*' && !$isDir && !preg_match('/' . $filter . '$/', $file)) continue;
			$array = array();
			$type = self::fileType($fullPath);
			$array['name'] = $file;
			if (!in_array($type, $filetype)) {
				$array['icon'] = _link('filetype/unknow.png');
			} else {
				$array['icon'] = _link('filetype/' . $type . '.gif');
			}
			$array['type'] = is_dir($fullPath) ? 'folder' : 'file';
			$array['url'] = $array['type'] == 'folder' ? '/dirs?path=' . urlencode($fullPath) : '/dirs/file?file=' . urlencode($fullPath);
			$array['remove'] = _link('/dirs/remove?file=' . $fullPath . '&isAjax=1');
			$array['path'] = _link($fullPath);
			$size = $array['type'] == 'folder' ? '' : filesize($fullPath);
			$array['size'] = $array['type'] == 'folder' ? '' : \helper\tools::formatSize($size, 'KB');
			$array['isImage'] = self::isImage($fullPath);
			//如果文件超过200k就不让直接查看，让下载后查看
			if ($size > 1024 * 200) {
				$array['url'] = '/dirs/down?file=' . urlencode($fullPath);
			}
			$array['url'] = _link($array['url']);
			$array['extension'] = $type;
			$array['createAt'] = filectime($fullPath);
			$array['updateAt'] = fileatime($fullPath);
			$files[] = $array;

		}
		$dir->close();
		return $files;
	}

	/**
	 * 不在指定目录的文件不让删除
	 *
	 * @param $file
	 * @return bool
	 */
	public static function allowRemove($file)
	{
		$allowFolder = array('tmp', 'tmp/compile');
		$file = ltrim($file, './');

		foreach ($allowFolder AS $allow) {
			if (stristr($file, $allow)) return true;
		}
		return false;
	}

	/**
	 * 删除指定文件夹或文件
	 * @param $dir
	 * @return bool
	 */
	public static function removeFile($dir)
	{
		//只让删除指定文件夹的文件,其他文件夹中的不让删除
		if (!self::allowRemove($dir)) {
			return false;
		}

		if (is_file($dir)) return unlink($dir);
		$dh = opendir($dir);

		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				$fullpath = $dir . "/" . $file;
				if (!is_dir($fullpath)) {
					unlink($fullpath);
				} else {
					self::removeFile($fullpath);
				}
			}
		}

		closedir($dh);
		//删除当前文件夹：
		if (rmdir($dir)) {
			return true;
		} else {
			return false;
		}
	}
}