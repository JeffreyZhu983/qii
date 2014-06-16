<?php
Qii::requireOnce(Qii_DIR . DS . 'core'. DS .'Session.php');
Qii::requireOnce(Qii_DIR . DS . 'core'. DS .'Security.php');
class test_controller extends Controller 
{
	public $_private = array();
	public function __construct()
	{
		$this->Qii('model');
	}
	public function index()
	{
		$this->instance('Model');
		$row = $this->model->getRow('SELECT * FROM emlog_link');
		print_r($row);
		//print_r(glob('/*/{*.jpg,*.gif,*.php}', GLOB_BRACE));
		Security::setExpiredTime(100);
		$sid =  Security::getSecurity();
		print_r($sid);
		Qii::setSecurity($sid);
		var_dump(Security::validateSecurity('1392711528.24m6n9yGb9n62e'));
		Session::init();
		session_start();
		foreach($sid AS $key => $value)
		{
			$_SESSION[$key] = $value;
		}
		print_r($_SESSION);
		$this->dispatch('test', 'test');
	}
	public function test()
	{
		$object = new Arrays();
		$object->setPrivate('s[]', array('111'));
		$object->setPrivate('s[]', array('222'));
		$object->setPrivate('s[]', array('333'));
		print_r ($object->getPrivate('s[0]'));
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
			$array = str_replace('[\'\']', '[]', '$privates[\'' . join("']['", $private) . '\']=\''. print_r($value, true) . '\';');
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
		preg_match_all("/(.*?)\[(.*?)\]/", $key, $match);
		$name = $match[1][0];
		$keys = $match[2];
		if($name == '')
		{
			return isset($this->_private[$key]) ? $this->_private[$key] : null;
		}
		if(!isset($this->_private[$name]))
		{
			return null;
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