<?php
/**
 * 将键值保存到\Qii\Config\Register::$config中
 * 用于保存系统相关设置，保存项目相关的配置，请注意不出现key相互覆盖的情况，可以考虑加前缀
 *
 * @author Jinhui Zhu
 * @version 1.3
 *
 * Usage:
 *    保存值：
 *        \Qii\Config\Register::set($key, $val);
 *    读取值：
 *        \Qii\Config\Register::get($key, $default);
 *        \Qii\Config\Register::$key($default);
 *
 *
 */
namespace Qii\Config;

use \Qii\Autoloader\Psr4;

class Register
{
	const VERSION = '1.3';
	/**
	 * 存储键值的变量
	 *
	 * @var Array
	 */
	public static $_cache;

    /**
     * 是否将内容写入到文件
     *
     * @var bool $isWrite 是否写入到文件
     */
	public static $isWrite = true;

	/**
	 * 设置键值
	 *
	 * @param String $key
	 * @param String $val
	 * @param Bool overwrite 是否覆盖之前保存的值，如果之前保存了值，要想再保存需要额外设置它为true，否则不让保存
	 */
	public static function set($key, $val, $overwrite = true)
	{
		if (!Register::isValid($key, $overwrite)) \Qii\Application::_e('Overwrite', $key, __LINE__);
		Register::$_cache[$key] = $val;
	}

	/**
	 * 设置键
	 *
	 * @param String $index
	 * @param String $key
	 * @param String $val
	 */
	public static function add($index, $key, $val)
	{
		$added = Register::get(Consts::APP_LOADED_FILE, array());
		$added[$index][$key] = $val;
		Register::$_cache[$index] = $added;
	}

	/**
	 * 移除某一个key
	 * @param String $key
	 */
	public static function remove($key)
	{
		if (!isset(Register::$_cache[$key])) return;
		unset(Register::$_cache[$key]);
	}

	/**
	 * 获取保存的值
	 *
	 * @param String $key
	 * @param String $index
	 * @param Mix $default
	 * @return Mix
	 */
	public static function get($key, $default = null)
	{
		if (!$key) throw new \Qii\Exceptions\Variable(\Qii::i(1003), __LINE__);
		//优先调用存在的get方法
		$method = 'get' . $key;
		if (method_exists('\Qii\Config\Register', $method)) return Register::$method();

		if (isset(Register::$_cache[$key])) {
			return Register::$_cache[$key];
		}
		return $default;
	}

	/**
	 * 通过\Qii\Config\Register::$key($defaultVal)来获取内容
	 *
	 * @param String $method
	 * @param Array $argvs
	 * @return Mix
	 */
	public static function __callStatic($method, $argvs)
	{
		$default = array_shift($argvs);
		return Register::get($method, $default);
	}

	/**
	 * 整理数组，将0.key 最后会整理到 [0][key]中
	 * @param Array $array
	 * @return multitype:
	 */
	public static function feval($array)
	{
		$data = array();
		foreach ($array AS $key => $value) {
			$keys = explode('.', $key);
			if (is_array($value)) {
				$string = "\$data['" . join("']['", $keys) . "']=" . var_export(Register::feval($value), true) . ";";
			} else {
				$string = "\$data['" . join("']['", $keys) . "']='" . $value . "';";
			}
			eval($string);
		}
		return $data;
	}

	/**
	 * 读取ini配置文件
	 *
	 * @param String $fileName
	 * @return Array
	 */
	public static function ini($fileName)
	{
		if (!$fileName) throw new \Qii\Exceptions\Variable(\Qii::i(1408), __LINE__);
		$ini = parse_ini_file($fileName, true);
		if (!$ini) throw new \Qii\Exceptions\InvalidFormat($fileName, __LINE__);
		$config = array();
		foreach ($ini AS $namespace => $properties) {
			$properties = Register::feval($properties);
			$name = $namespace;
			$namespaces = array();
			if (stristr($namespace, ':')) {
				$namespaces = explode(':', $namespace);
				$name = array_shift($namespaces);
			}
			$name = trim($name);
			if (count($namespaces) > 0) {
				foreach ($namespaces AS $space) {
					//如果space以“.”开头，与key的方式放在当前key下边如[dev:.space]，那么生成后的数据就是这样的[dev][space]否则是[space+dev]
					if (substr($space, 0, 1) == '.') {
						$space = substr($space, 1);
						if (isset($config[$space])) $config[$name][$space] = array_merge($config[$space], $properties);;
						continue;
					}
                    if(stristr($namespace, '.')) {
                        if (isset($config[$space])) $config[$name] = $config[$space];
                    }else{
                        $config[$name] = $config[$name] ?? [];
                        if (isset($config[$space])) $config[$name] = array_merge($config[$space], $config[$name], $properties);
                    }
				}
			}else{
                $config[$name] = $properties;
            }
		}
		return $config;
	}

	/**
	 * 返回cache的名称
	 * @param String $iniFile
	 * @return String
	 */
	public static function getCacheName($iniFile)
	{
		$cacheName = basename($iniFile);
		$environs = Register::get(Consts::APP_ENVIRONS, array());
		if (isset($environs[$cacheName])) {
			$environ = $environs[$cacheName];
			$cacheName = $environ . '.' . $cacheName;
		}
		return $cacheName;
	}

	/**
	 * 覆盖/添加ini文件的key对应的值
	 * @param String $iniFile ini文件名
	 * @param String $key 需覆盖的key
	 * @param String $val key对应的值
	 */
	public static function rewriteConfig($iniFile, $key, $val)
	{
		$config = Register::getIniConfigure($iniFile);
		$cacheName = Register::getCacheName($iniFile);
		$config[$key] = $val;
		Register::set($cacheName, $config);
	}
	/**
	 * 删除ini配置文件中对应的key
	 * @param string $iniFile ini配置我呢见
	 * @param string $key 陪删除的key
	 */
	public static function removeAppConfigure($iniFile, $key)
	{
		$config = Register::getIniConfigure($iniFile);
		$cacheName = Register::getCacheName($iniFile);
		unset($config[$key]);
		Register::set($cacheName, $config);
	}

	/**
	 * 合并ini文件生成的数组
	 * @param String $iniFile ini文件名
	 * @param Array $array
	 */
	public static function mergeAppConfigure($iniFile, $array)
	{
		if (!is_array($array)) return;
		$config = Register::getIniConfigure($iniFile);

		$environs = Register::get(Consts::APP_ENVIRONS, array());

		$cacheName = basename($iniFile);
		if (isset($environs[$cacheName])) {
			$environ = $environs[$cacheName];
			$cacheName = $environ . '.' . $cacheName;
		}
		$config = array_merge($config, $array);
		Register::set($cacheName, $config);
	}

	/**
	 * 获取配置ini文件
	 * @param String $iniFile
	 * @param String $environ
	 * @return boolean
	 */
	public static function setConfig($iniFile, $environ = 'product')
	{
		$cacheName = basename($iniFile);
		$environs = Register::get(Consts::APP_ENVIRONS, array());
		$environs[$cacheName] = $environ;
		Register::set(Consts::APP_ENVIRONS, $environs);

		$cacheName = $environ . '.' . $cacheName;
		if (!is_file($iniFile)) return false;
		$cacheFile = Psr4::getInstance()->getFileByPrefix(Register::get(Consts::APP_CACHE_PATH) . DS . $cacheName . '.php');
		if (self::$isWrite && Register::get(Consts::APP_CACHE_PATH)) {
			if (is_file($cacheFile)) {
				if (filemtime($cacheFile) == filemtime($iniFile)) {
					$common = include($cacheFile);
					Register::set($cacheName, $common);
					return $common;
				}
			}
		}
		$array = Register::ini($iniFile);
		if (!$array) return false;
		$common = $array['common'];
		if (isset($array[$environ])) {
			$environConfig = $array[$environ];
			$common = self::array_merge_recursive_distinct($common, $environConfig);
		}
		//如果文件不可写，touch就有问题，就不写缓存文件
		if (self::$isWrite && is_writeable($iniFile)) {
			file_put_contents($cacheFile, "<?php \n return " . var_export($common, true) . "\n?>", LOCK_EX);
			touch($iniFile);
		}
		Register::set($cacheName, $common);
		return $common;
	}

    /**
     * 递归的合并两个数组 array_merge_recursive会把字段转化成数组
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function array_merge_recursive_distinct ( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = self::array_merge_recursive_distinct ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }
	/**
	 * 设置网站的配置文件
	 *
	 * @param String $iniFile 配置我呢见
	 * @param string $environ 环境变量
	 * @return Object self
	 */
	public static function setAppConfigure($iniFile, $environ = 'product')
	{
		return Register::setConfig($iniFile, $environ);
	}

	/**
	 * 获取配置ini文件相关信息
	 * @param String $fileName 文件名
	 * @return Ambigous <Mix, multitype:>
	 */
	public static function getIniConfigure($fileName)
	{
		$cacheName = basename($fileName);
		$environs = Register::get(Consts::APP_ENVIRONS, array());
		if (isset($environs[$cacheName])) {
			$cacheName = $environs[$cacheName] . '.' . $cacheName;
		}
		return Register::get($cacheName);
	}

	/**
	 * 获取网站的配置信息
	 *
	 * @return Array
	 */
	public static function getAppConfigure($iniFile = Consts::APP_INI, $key = NULL)
	{
		$appConfigure = Register::getIniConfigure($iniFile);
		if ($key == null) return $appConfigure;
		return isset($appConfigure[$key]) ? $appConfigure[$key] : NULL;
	}

	/**
	 * 验证是否之前已经保存过这个属性，如果保存过，不覆盖属性对应的值就不保存
	 *
	 * @param String $key
	 * @param Bool $overwrite
	 * @return Bool
	 */
	public static function isValid($key, $overwrite = false)
	{
		if ($overwrite) return true;
		if (isset(Register::$_cache[$key])) return false;
		return true;
	}

	/**
	 * 获取当前系统环境
	 * @return Ambigous <Mix, multitype:>
	 */
	public static function getAppEnviron()
	{
		return isset(Register::$_cache[Consts::APP_ENVIRON]) ?
                    Register::$_cache[Consts::APP_ENVIRON]
                    : Consts::APP_DEFAULT_ENVIRON;
	}

	public function __call($method, $argvs)
	{
	}
}