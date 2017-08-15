<?php
namespace Qii\Autoloader;

class Import
{
    const VERSION = '1.3';
    private static $loadedFiles = array();
    private static $includeFiles = array();

    /**
     * require文件
     *
     * @param string $file 需要require的文件
     * @return array|bool|void
     */
    public static function requires($file)
    {
        if (is_array($file)) {
            return array_map(function ($n) {
                return self::requires($n);
            }, $file);
        }
        $file = str_replace(DS . DS , DS, str_replace(array('\\', '/'), DS, $file));
        if (self::getFileLoaded($file)) return true;
        if (file_exists($file)) {
            self::setFileLoaded($file);
            require $file;
            return true;
        }
        return false;
    }

    /**
     * 包含文件
     * @param string $file 文件路径
     * @return mix
     */
    public static function includes($file)
    {
        if (is_array($file)) {
            return array_map(function ($n) {
                return self::includes($n);
            }, $file);
        }
        $file = str_replace(array('\\', '/'), DS, $file);
        if (self::getIncludeFiles($file) !== null) self::getIncludeFiles($file);
        if (file_exists($file)) {
            $configure = include($file);
            self::setIncludeFiles($file, $configure);
            return $configure;
        }else{
            //print_r(debug_backtrace());
            throw new \Qii\Exceptions\FileNotFound(\Qii::i(1405, $file), __LINE__);
        }
        return false;
    }

    /**
     * 根据类名载入文件
     *
     * @param string $className 类名
     * @return string
     */
    public static function requireByClass($className)
    {
        return \Qii\Autoloader\Psr4::getInstance()->loadFileByClass($className);
    }

    /**
     * 载入文件夹中所有文件
     *
     * @param string $dir 目录
     * @throws Qii\Exceptions\FileNotFound
     */
    public static function requireByDir($dir)
    {
        if (!is_dir($dir)) throw new \Qii\Exceptions\FileNotFound(\Qii::i(1405, $dir), __LINE__);
        $files = self::findFiles($dir, array('php'));
        if (isset($files['php'])) self::requires($files['php']);
    }

    /**
     * 设置文件到加载列表
     *
     * @param string $file 文件
     */
    public static function setFileLoaded($file)
    {
        self::$loadedFiles[$file] = true;
    }

    /**
     * 获取指定文件是否已经加载
     * @param string $file 文件路径
     * @return null
     */
    public static function getFileLoaded($file)
    {
        if (isset(self::$loadedFiles[$file])) return self::$loadedFiles[$file];
        return false;
    }

    /**
     * 设置include的文件到已经加载列表
     *
     * @param string $file 文件路径
     */
    public static function setIncludeFiles($file, $config)
    {
        self::$includeFiles[$file] = $config;
    }

    /**
     * 获取指定文件是否已经加载
     *
     * @param string $file 文件路径
     * @return bool
     */
    public static function getIncludeFiles($file)
    {
        if (isset(self::$includeFiles[$file])) {
            return self::$includeFiles[$file];
        }
        return false;
    }

    /**
     * 获取已经require及include的文件列表
     *
     * @return array
     */
    public static function getLoadedFile()
    {
        return array('include' => self::$includeFiles, 'requires' => self::$loadedFiles);
    }

    /**
     * 遍历目录中的路径
     * @param string $directory 目录
     * @param array $directories 目录下所有的路径
     */
    public static function globRecursive($directory, &$directories = array())
    {
        foreach (glob($directory, GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
            $directories[] = $folder;
            self::globRecursive("{$folder}/*", $directories);
        }
    }

    /**
     * 返回指定目录中的文件
     * @param string $directory 目录
     * @param array $extensions 需要过滤的后缀名
     * @return array
     */
    public static function findFiles($directory, $extensions = array())
    {
        self::globRecursive($directory, $directories);
        $files = array();
        foreach ($directories as $directory) {
            if (count($extensions) == 0) {
                foreach (glob("{$directory}/*.*") as $file) {
                    $files[] = $file;
                }
            } else {
                foreach ($extensions as $extension) {
                    foreach (glob("{$directory}/*.{$extension}") as $file) {
                        $files[$extension][] = $file;
                    }
                }
            }
        }
        return $files;
    }
}