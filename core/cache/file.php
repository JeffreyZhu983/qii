<?php
/**
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: file.php 2 2012-07-06 08:50:19Z jinhui.zhu $
 *
 * Useage:
 * 在control.php中使用
 * $this->setCache('file', array('path' => 'tmp'));
 * $this->cache->set(id, data, policy);
 * $this->cache->get(id);
 * $this->cache->remove(id);
 */
Qii::requireOnce(dirname(__FILE__) . DS . 'interface.php');
class Cache implements cacheInterface
{
	public $policy = array('path' => 'tmp', 'life_time' => 3600, 'prefix' => 'file');//设置目录、过期时间、文件前缀
	public $exclude = array();
	public function __construct(array $policy = null)
	{
		if(!empty($policy))
		{
			$this->policy = array_merge($this->policy, $policy);
		}
		$this->exclude = array();
		$this->exclude[] = Qii_DIR;
		$QiiPathArray = explode(",", QII_PATH_ARRAY);
		foreach ($QiiPathArray AS $path)
		{
			$this->exclude[] = Qii_DIR .DS. $path;
		}
	}
	public function initialization(array $policy = null)
	{
		if(!empty($policy))
		{
			$this->policy = array_merge($this->policy, $policy);
		}
	}
	/**
	 * 检查是否可以保存到指定的目录
	 *
	 */
	public function checkIsSave()
	{
		if(!is_dir($this->policy['path'])) mkdir($this->policy['path'], 0777);
		if(is_dir($this->policy['path']))
		{
			$this->policy['path'] = realpath($this->policy['path']);
		}
		else 
		{
			Qii::setError(false, 2, array($this->policy['path']));
		}
		//如果在系统目录就不让保存
		if(in_array($this->policy['path'], $this->exclude))
		{
			//Qii::setError(false, 2, array($this->policy['path']));
			throw  new Exception('Access denied');
		}
	}
	/**
	 * 获取文件名称
	 *
	 * @param String $id
	 * @return String
	 */
	public function getFileName($id)
	{
		$fileName = $this->policy['path'] . '/'. $this->policy['prefix'] .'.' . $id . '.'. (time() + $this->policy['life_time']);
		$fileName = $fileName . '.'. md5($fileName);
		return $fileName;
	}
	/**
	 * 检查文件是否存在
	 *
	 * @param Int $id
	 */
	public function scanFile($id)
	{
		$fileArray = glob($this->policy['path'] . '/'. $this->policy['prefix'] .'.' . $id . '.*');
		return $fileArray;
	}
	public function set($id, $data, array $policy = null)//设置
	{
		if(!empty($policy))
		{
			$this->policy = array_merge($this->policy, $policy);
		}
		$this->checkIsSave();
		$fileName = $this->getFileName($id);
		//检查文件是否存在，存在就先删除再保存
		$this->remove($id);
		file_put_contents($fileName, serialize($data), LOCK_EX);
	}
	public function get($id)//获取指定key的缓存
	{
		$this->checkIsSave();
		$fileArray = glob($this->policy['path'] . '/'. $this->policy['prefix'] .'.' . $id . '.*');
		//检查文件是否存在
		if(count($fileArray) == 0)
		{
			return;
		}
		$fileName = $fileArray[0];
		//检查文件是否过期，如果过期就返回空
		$fileInfo = explode(".", $fileName);
		$time = $fileInfo[count($fileInfo) - 2];
		if($this->policy['life_time'] > 0 && $time < time())
		{
			return;
		}
		return unserialize(file_get_contents($fileName));
	}
	public function remove($key)//移除指定key的缓存
	{
		$fileArray = $this->scanFile($key);//检查文件是否存在，存在就先删除再保存
		foreach ($fileArray AS $file)
		{
			unlink($file);
		}
	}
	public function clean()//清除所有缓存
	{
		$this->checkIsSave();
		//禁止清除Qii目录文件
		$handle = opendir($this->policy['path']);
		if($handle)
		{
			while($file = readdir($handle))
			{
				if(is_file($this->policy['path'] . '/' . $file))
				{
					unlink($this->policy['path'] . '/' . $file);
				}
			}
		}
		closedir($handle);
	}
}
?>