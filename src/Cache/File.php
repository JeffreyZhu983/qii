<?php
/**
 * @author Jinhui Zhu<jinhui.zhu@live.cn> 2015-10-26 21:44
 *
 * Useage:
 * 在control.php中使用
 * $this->setCache('file', array('path' => 'tmp'));
 * $this->cache->set(id, data, policy);
 * $this->cache->get(id);
 * $this->cache->remove(id);
 */
class Qii_Cache_File implements Qii_Cache_Intf
{
    const VERSION = '1.2';
    public $policy = array('path' => 'tmp', 'life_time' => 3600, 'prefix' => 'file');//设置目录、过期时间、文件前缀
    public $exclude = array();

    public function __construct(array $policy = null)
    {
        if (!empty($policy)) {
            $this->policy = array_merge($this->policy, $policy);
        }
        $this->exclude = array();
        $this->exclude[] = Qii_DIR;
    }

    /**
     * 初始化 将规则合并
     *
     * @param array|null $policy
     */
    public function initialization(array $policy = null)
    {
        if (!empty($policy)) {
            $this->policy = array_merge($this->policy, $policy);
        }
    }

    /**
     * 检查是否可以保存到指定的目录
     *
     */
    public function checkIsSave()
    {
        if (!is_dir($this->policy['path'])) mkdir($this->policy['path'], 0777);
        if (is_dir($this->policy['path'])) {
            $this->policy['path'] = \Qii_Autoloader_Psr4::realpath($this->policy['path']);
        } else {
            \Qii::setError(false, __LINE__, 1401, $this->policy['path']);
        }
        //如果在系统目录就不让保存
        if (in_array($this->policy['path'], $this->exclude)) {
            throw  new \Qii_Execptions_AccessDenied($this->policy['path']);
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
        $fileName = $this->policy['path'] . '/' . $this->policy['prefix'] . '.' . $id . '.' . (time() + $this->policy['life_time']);
        $fileName = $fileName . '.' . md5($fileName);
        return $fileName;
    }

    /**
     * 检查文件是否存在
     *
     * @param Int $id
     */
    public function scanFile($id)
    {
        $fileArray = glob($this->policy['path'] . '/' . $this->policy['prefix'] . '.' . $id . '.*');
        return $fileArray;
    }

    /**
     * 缓存数据
     *
     * @param $id
     * @param $data
     * @param array|null $policy
     * @return bool|int|void
     * @throws AccessDeniedExecption
     */
    public function set($id, $data, array $policy = null)//设置
    {
        if (!empty($policy)) {
            $this->policy = array_merge($this->policy, $policy);
        }
        $this->checkIsSave();
        $fileName = $this->getFileName($id);
        //检查文件是否存在，存在就先删除再保存
        $this->remove($id);
        return file_put_contents($fileName, serialize($data), LOCK_EX);
    }

    /**
     * 获取指定key的缓存
     *
     * @param $id
     * @return mixed|void
     * @throws AccessDeniedExecption
     */
    public function get($id)
    {
        $this->checkIsSave();
        $fileArray = glob($this->policy['path'] . '/' . $this->policy['prefix'] . '.' . $id . '.*');
        //检查文件是否存在
        if (count($fileArray) == 0) {
            return;
        }
        $fileName = $fileArray[0];
        //检查文件是否过期，如果过期就返回空
        $fileInfo = explode(".", $fileName);
        $time = $fileInfo[count($fileInfo) - 2];
        if ($this->policy['life_time'] > 0 && $time < time()) {
            return;
        }
        return unserialize(file_get_contents($fileName));
    }

    /**
     * 移除指定key的缓存
     *
     * @param $key
     */
    public function remove($key)
    {
        $fileArray = $this->scanFile($key);//检查文件是否存在，存在就先删除再保存
        foreach ($fileArray AS $file) {
            unlink($file);
        }
    }

    /**
     * 清除所有缓存
     *
     * @throws AccessDeniedExecption
     */
    public function clean()
    {
        $this->checkIsSave();
        //禁止清除Qii目录文件
        $handle = opendir($this->policy['path']);
        if ($handle) {
            while ($file = readdir($handle)) {
                if (is_file($this->policy['path'] . '/' . $file)) {
                    unlink($this->policy['path'] . '/' . $file);
                }
            }
        }
        closedir($handle);
    }
}