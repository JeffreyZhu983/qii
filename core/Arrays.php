<?php
/**
 * 实现PHP中数组功能
 * 
 * 用法：
 * 
 * $array = new Arrays();
 * 
 * $data = array();
 * $data['common']['one'] = '1';
 * $data['common']['two'] = '1';
 * $data['common']['three'] = '1';
 * 
 * $array->getValueFromArray($data, '[common][three]');
 * 
 * $array->setPrivate('string', 'string');
 * $array->setPrivate('array[val][]', array(1, 2));
 * $array->setPrivate('array[val][]', array(3, 4));
 * 
 * $array->getPrivate('string');
 * 
 * $array->getPrivate('array[val]');
 * 
 * $array->getPrivate('array[val][0]');
 * 
 * $array->getPrivate('array[val][1]');
 * 
 * 
 */
if(class_exists('Arrays'))
{
	return;
}
class Arrays
{
	public $version = '1.1.0';
	public $_private = array();
	public function __construct()
	{
		
	}
	public function __toString()
	{
		return 'v1.0.1';
	}
	/**
	 * 直接从数组中获取指定key的值
	 *
	 * @param Array $data
	 * @param String $key
	 * @return Mix
	 */
	public function getValueFromArray($data, $key)
	{
		if(preg_match('/^\s*$/', $key))
		{
			return $data;
		}
		preg_match_all("/(.*?)\[(.*?)\]/", $key, $match);
		
		$name = $match[1][0];
		$keys = $match[2];
		
		if($name == '')
		{
			return isset($data[$key]) ? $data[$key] : '';
		}
		if(!isset($data[$name]))
		{
			return '';
		}
		$value = $data[$name];
		foreach ($keys AS $key)
		{
			if($key == '')
			{
				$value = $value;
			}
			else 
			{
				$value = $value[$key];
			}
		}
		return $value;
	}
	/**
	 * 实现PHP数组赋值
	 *
	 * @param String $key
	 * @param Mix $value
	 * @return Array
	 */
	public function setPrivate($key, $value)
	{
		preg_match_all("/(.*?)\[(.*?)\]/", $key, $match);
		$name = $match[1][0];
		$keys = $match[2];
		if($name == '')
		{
			$name = $key;
		}
		if(empty($keys))
		{
			$this->_private[$key] = $value;
			return $this->_private;
		}
		$private = array();
		$private = array_merge($private, $keys);
		if(is_array($value) || is_object($value))
		{
			$array = str_replace('[\'\']', '[]', '$privates[\'' . join("']['", $private) . '\']=$value;');
		}
		else 
		{
			$array = str_replace('[\'\']', '[]', '$privates[\'' . join("']['", $private) . '\']=\''. $value . '\';');
		}
		eval($array);
		if(isset($this->_private[$name]))
		{
			if(!is_array($this->_private[$name]))
			{
				unset($this->_private[$name]);
				$this->_private[$name] = $privates;
			}
			else
			{
				$this->_private[$name] = array_merge_recursive($this->_private[$name], $privates);
			}
		}
		else 
		{
			$this->_private[$name] = $privates;
		}
		return $this->_private;
	}
	/**
	 * 获取通过setPrivate key对应的值
	 *
	 * @param String $key
	 * @return Mix
	 */
	public function getPrivate($key)
	{
		if(preg_match('/^\s*$/', $key))
		{
			return $this->_private;
		}
		preg_match_all("/(.*?)\[(.*?)\]/", $key, $match);
		$name = $match[1][0];
		$keys = $match[2];
		if($name == '')
		{
			return isset($this->_private[$key]) ? $this->_private[$key] : '';
		}
		if(!isset($this->_private[$name]))
		{
			return '';
		}
		$value = $this->_private[$name];
		foreach ($keys AS $key)
		{
			if($key == '')
			{
				$value = $value;
			}
			else 
			{
				$value = $value[$key];
			}
		}
		return $value;
	}
}
?>