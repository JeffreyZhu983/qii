<?php
namespace \Qii\Autoloader;

/**
 * Psr4 规范
 *
 */
class Psr4
{
    /**
     * 将查找过的文件放入缓存
     */
    protected static $cachedFiles = array();
    /**
     * 是否使用namespace
     */
    protected $useNamespace = array();
    /**
     * 添加namespace前缀对应的目录，只要是以这个前缀开头的文件都在指定目录中去查找
     * 前缀可以对应多个目录，找的时候会去遍历数组
     * @var array
     */
    protected $prefixes = array();

    /**
     * 当前class的初始化
     */
    private static $_instance = null;

    /**
     * @var APP_LOAD_PREFIX 保存类到以APP_LOAD_PREFIX开头的key中
     */
    const APP_LOAD_PREFIX = '__qii_psr4_instance';
    /**
     * @var $_loadedClass 保存加载过的类
     */
    protected static $_loadedClass = array();

    /**
     * @var $_realpath 将转换后的路径存放到此变量中
     */
    protected static $_realpath = array();

    /**
     * 最后一次没有加载到文件的错误路径
     * @var array $lastErrorLoadedFile
     */
    protected static $lastErrorLoadedFile = array();

    /**
     * 注册自动加载类
     *
     */
    private function __construct()
    {
    }

    /**
     * 单例模式
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 注册自动加载类
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadFileByClass'));
        return $this;
    }

    /**
     * Setting is use namespace for class
     *
     * @param $prefix 以prefix前缀开头的使用namespace
     * @param bool $useNamespace
     * @return object $this
     */
    public function setUseNamespace($prefix, $useNamespace = true)
    {
        $this->useNamespace[$prefix] = $useNamespace;
        return $this;
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string $baseDir A base directory for class files in the
     * namespace.
     * @param bool $prepend If true, prepend the base directory to the stack
     * instead of appending it; this causes it to be searched first rather
     * than last.
     * @return void
     */
    public function addNamespace($prefix, $baseDir, $prepend = false)
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $baseDir = rtrim($baseDir, '/') . DIRECTORY_SEPARATOR;
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        // initialize the namespace prefix array
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }
        if (in_array($baseDir, $this->prefixes[$prefix])) {
            return $this;
        }
        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $baseDir);
        } else {
            array_push($this->prefixes[$prefix], $baseDir);
        }
        return $this;
    }

    /**
     * 移除某一个namespace下的指定路径
     * @param string $prefix 前缀
     * @param string $baseDir 路径
     * @return array
     */
    public function removeNameSpace($prefix, $baseDir)
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $baseDir = rtrim($baseDir, '/') . DIRECTORY_SEPARATOR;
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        // initialize the namespace prefix array
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }
        foreach ($this->prefixes[$prefix] AS $key => $dir) {
            if ($dir == $baseDir) unset($this->prefixes[$prefix][$key]);
        }
        return $this->prefixes;
    }

    /**
     * 返回namespace路径
     */
    public function getNamespace($prefix)
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';
        if (isset($this->prefixes[$prefix])) return $this->prefixes[$prefix];
        return '';
    }

    /**
     * 通过文件名返回路径
     * @param string $fileName 文件名
     * @return string
     */
    public function getFileByPrefix($fileName)
    {
        $fileName = str_replace(array('/', '\\'), DS, $fileName);
        $prefixes = explode(DS, $fileName, 2);
        $dirs = isset($this->prefixes['workspace\\']) ? $this->prefixes['workspace\\'] : array();
        if (count($prefixes) == 2) {
            if (isset($this->prefixes[$prefixes[0]])) $dirs = $this->prefixes[$prefixes[0]];
        }
        foreach ($dirs as $baseDir) {
            if (is_file($baseDir . DS . $fileName)) {
                return $baseDir . DS . $fileName;
            }
        }
        return $fileName;
    }

    /**
     * 获取指定文件夹路径
     * @param string $folder 路径
     * @return string 路径
     */
    public function getFolderByPrefix($folder)
    {
        $fileName = str_replace(array('/', '\\'), DS, $folder);
        $prefixes = explode(DS, $fileName, 2);
        $dirs = isset($this->prefixes['workspace\\']) ? $this->prefixes['workspace\\'] : array();
        if (count($prefixes) == 2) {
            if (isset($this->prefixes[$prefixes[0]])) $dirs = $this->prefixes[$prefixes[0]];
        }
        foreach ($dirs as $baseDir) {
            return $baseDir . DS . $folder;
        }
        return $folder;
    }

    /**
     * 通过类名加载文件
     * @param string $class 类名
     * @return string 文件路径
     */
    public function loadFileByClass($class)
    {
        // the current namespace prefix
        //replace "_" to "\" use common method to load class
        $class = str_replace("_", "\\", $class);
        $prefix = $class;
        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($prefix, '\\')) {
            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // the rest is the relative class name
            $relativeClass = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            // remove the trailing namespace separator for the next iteration
            // of strrpos()
            $prefix = rtrim($prefix, '\\');
        };
        //如果没有找到就在workspace中去找对应的文件 额外添加的方法
        $mappedFile = $this->loadMappedFile('workspace\\', $class);
        if ($mappedFile) {
            return $mappedFile;
        }
        $notLoaded = isset(self::$lastErrorLoadedFile[$class]) ? self::$lastErrorLoadedFile[$class] : self::getClassName($class);
        throw new \Qii\Exceptions\FileNotFound(\Qii::i(1405, $notLoaded), __LINE__);
    }

    /**
     * loadClass返回真正的类名
     *
     * @param string $class 类名
     */
    public function getClassName($class)
    {
        //如果是不使用namespace，就将namespace转换成下划线的形式
        $useNamespace = false;
        //将_隔开的统一替换成namespace方式,如果不使用namespace就再替换回来
        $class = str_replace('_', '\\', $class);
        if (stristr($class, '\\')) {
            $prefix = explode('\\', ltrim($class, '\\'))[0];
            $useNamespace = isset($this->useNamespace[$prefix]) && $this->useNamespace[$prefix] ? true : false;
        }
        if (!$useNamespace) {
            $class = str_replace('\\', '_', $class);
        }
        return $class;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     * @return mixed The mapped file name on success, or boolean false on
     * failure.
     */
    public function loadClass($class)
    {
        $args = func_get_args();
        //去掉第一个斜杠
        $class = array_shift($args);
        $class = preg_replace("/^\\\\/", "", $class);
        $class = $this->getClassName($class);
        array_unshift($args, $class);

        if (class_exists($class, false)) {
            return call_user_func_array(array($this, 'instance'), $args);
        }
        if ($this->loadFileByClass($class)) {
            return call_user_func_array(array($this, 'instance'), $args);
        }
        throw new \Qii\Exceptions\ClassNotFound(\Qii::i(1103, $class), __LINE__);
    }

    /**
     * 调用静态的方法
     * @param string $class 类名
     * @param string $method 方法名
     * @return mixed
     */
    public static function loadStatic($class, $method)
    {
        $args = func_get_args();
        $class = \Qii\Autoloader\Psr4::getInstance()->getClassName(array_shift($args));
        $method = array_shift($args);
        return call_user_func_array(array($class, $method), $args);
    }

    /**
     * 获取文件的绝对路径
     * @param string $path
     * @param bool $exists 是否使用realpath
     * @return string  真实路径
     */
    public static function realpath($path)
    {
        if (isset(self::$_realpath[$path])) return self::$_realpath[$path];
        $drive = '';
        if (OS === 'WIN') {
            $path = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $path);
            if (preg_match('/(phar\:\\\\|[a-zA-Z]\:)(.*)/', $path, $matches)) {
                list(, $drive, $path) = $matches;
            } else {
                $cwd = getcwd();
                $drive = substr($cwd, 0, 2);
                if (substr($path, 0, 1) != DIRECTORY_SEPARATOR) {
                    $path = substr($cwd, 3) . DIRECTORY_SEPARATOR . $path;
                }
            }
        } elseif (substr($path, 0, 1) != DIRECTORY_SEPARATOR) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        $stack = array();
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($parts as $dir) {
            if (strlen($dir) && $dir !== '.') {
                if ($dir == '..') {
                    array_pop($stack);
                } else {
                    array_push($stack, $dir);
                }
            }
        }
        $realPath = str_replace(DIRECTORY_SEPARATOR, '/', $drive . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack));
        self::$_realpath[$path] = $realPath;
        return $realPath;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     * @param string $relativeClass The relative class name.
     * @return mixed Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected function loadMappedFile($prefix, $relativeClass)
    {
        if (isset(self::$cachedFiles[$prefix . '_' . $relativeClass])) return self::$cachedFiles[$prefix . '_' . $relativeClass];
        // are there any base directories for this namespace prefix?
        if (isset($this->prefixes[$prefix]) === false) {
            //if there any base directories , add self to prefix
            $this->addNamespace($prefix, $prefix);
            //return false;
        }
        $prefix = trim($prefix, '\\') . '\\';
        $file = '';
        // look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $baseDir) {
            $file = str_replace("/", DS, $baseDir
                . str_replace('\\', DS, $relativeClass)
                . '.php');
            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                self::$cachedFiles[$prefix . '_' . $relativeClass] = $file;
                return $file;
            }
        }
        self::$lastErrorLoadedFile[$relativeClass] = $file;
        // never found it
        return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile($file)
    {
        return \Qii\Autoloader\Import::requires($file);
    }

    /**
     * instance class
     * @param string $class
     * @return object
     */
    public function instance()
    {
        $args = func_get_args();
        $class = array_shift($args);
        $className = $this->getClassName($class);
        if (isset(self::$_loadedClass[$className])) return self::$_loadedClass[$className];
        if (!class_exists($className, false)) {
            throw new \Qii\Exceptions\CallUndefinedClass(\Qii\Application::i('1105', $className), __LINE__);
        }
        $loader = new \ReflectionClass($className);
        //try {
        self::$_loadedClass[$className] = $instance = $loader->newInstanceArgs($args);
        //如果有_initialize方法就自动调用_initialize方法，并将参数传递给_initialize方法
        if ($loader->hasMethod('_initialize')) {
            call_user_func_array(array($instance, '_initialize'), $args);
        }
        return $instance;
        /*} catch (Exception $e) {
            throw new \Exception($e->getMessage(), __LINE__);
        }*/
    }
}