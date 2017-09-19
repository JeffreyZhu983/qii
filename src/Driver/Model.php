<?php
/**
 * 数据库分发器
 * @author Jinhui Zhu <jinhui.zhu@live.cn>2016-01-19 18:31
 * 使用方法:
 * 1.
 * namespace Model;
 *
 * use \Qii\Driver\Model;
 * class comments extends Model
 * {
 *        public function __construct()
 *        {
 *            parent::__construct();
 *            $this->setRules(new \Qii\Driver\Rules(\Qii\Autoloader\Import::includes('configure/table/adcycle.comments.config.php')));
 *        }
 * }
 * 2.
 * $test = _M(new \Qii\Driver\Rules(\Qii\Autoloader\Import::includes('configure/table/test.config.php')));
 * $fields = array('id' => 1, 'name' => 'test');
 * $test->save($fields);
 */
namespace Qii\Driver;

class Model
{
    const VERSION = '1.2';
    /**
     * @var $_allow 允许使用的数据库驱动类新
     */
    protected $_allow = array('pdo', 'mysql', 'mysqli');
    /**
     * @var $db 数据库实例
     */
    public $db = null;
    /**
     * 数据库配置文件
     */
    protected $_dbInfo;
    /**
     * 数据库驱动
     */
    protected $_driver = 'pdo';
    /**
     * @var $_load 加载类
     */
    public $_load;

    /**
     * @var $_language 语言包
     */
    public $_language;
    /**
     * @var array $rules 数据表规则
     */
    private $rules = null;
    /**
     * @var \Qii_Driver_Easy $model
     */
    private $model = array();
    /**
     * @var Qii_Request_Abstract $_request 请求类
     */
    protected $_request;
    /**
     * @var $_helper helper类
     */
    protected $_helper;

    public function __construct()
    {
        $this->_load = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Autoloader\Loader');
        $this->_language = \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Language\Loader');
        $this->_request = \Qii\Autoloader\Psr4::getInstance()->loadClass('Qii\Request\Http');
        $this->_helper = \Qii\Autoloader\Psr4::getInstance()->loadClass('Qii\Autoloader\Helper');
        $this->_dbInfo = \Qii\Config\Register::getAppConfigure(\Qii\Config\Register::get(\Qii\Config\Consts::APP_DB));
        if (isset($this->_dbInfo['driver'])) {
            $this->_driver = $this->_dbInfo['driver'];
        }
        if (!in_array($this->_driver, $this->_allow)) {
            $this->_driver = array_shift($this->_allow);
        }
        \Qii\Autoloader\Import::requires(array(
            Qii_DIR . DS . 'Qii' . DS . 'Driver' . DS . 'Base.php',
            Qii_DIR . DS . 'Qii' . DS . 'Driver' . DS . 'ConnBase.php',
            Qii_DIR . DS . 'Qii' . DS . 'Driver' . DS . 'ConnIntf.php',
            Qii_DIR . DS . 'Qii' . DS . 'Driver' . DS . ucWords($this->_driver) . DS . 'Connection.php',
            Qii_DIR . DS . 'Qii' . DS . 'Driver' . DS . ucWords($this->_driver) . DS . 'Driver.php',
        ));
        $this->db = \Qii\Autoloader\Psr4::getInstance()->loadClass(
            '\Qii\Driver\\' . ucWords($this->_driver) . '\Driver',
            \Qii\Autoloader\Psr4::getInstance()->loadClass(
                '\Qii\Driver\\' . ucWords($this->_driver) . '\Connection'
            )
        );
        $this->db->_debugSQL = isset($this->_dbInfo['debug']) ? $this->_dbInfo['debug'] : false;
        return $this;
    }

    /**
     * 设置属性
     *
     * @param string $name 属性名
     * @param mix $val 值
     */
    public function __set($name, $val)
    {
        $this->db->$name = $val;
    }

    /**
     * 将属性转到DB类中去
     */
    public function __get($attr)
    {
        if ($this->db) {
            return $this->db->$attr;
        }
        return null;
    }

    /**
     * 获取当前使用的数据库
     */
    public function getCurrentDB()
    {
        return $this->db->currentDB;
    }

    /**
     * 设置规则
     * @param array $rules
     */
    public function setRules(\Qii\Driver\Rules $rules)
    {
        if (empty($rules)) throw new \Exception(\Qii::i('Please set rules first'), __LINE__);
        $this->rules = $rules;
        return $this;
    }

    /**
     * 生成数据库结构
     */
    public function tableStruct()
    {
        $this->checkRulesInstance();
        $struct = array_flip($this->rules->getFields());
        foreach ($struct AS $key => $val) {
            $struct[$key] = '';
        }
        return $struct;
    }

    /**
     * 检查是否已经设置规则
     */
    final public function checkRulesInstance()
    {
        if ($this->rules == null) throw new \Exception(\Qii::i('Please set rules first'), __LINE__);
    }

    /**
     * 获取当前初始化的model
     * @return \Qii_Driver_Easy
     */
    final public function getInstance()
    {
        $this->checkRulesInstance();
        $tableName = $this->rules->getTableName();
        if (!isset($this->model[$tableName])) $this->model[$tableName] = _DBDriver($this->rules);
        return $this->model[$tableName];
    }

    /**
     * 设置主键
     * @param array $privateKey 设置主键
     * @return Object
     */
    final public function setPrivateKey($privateKey = array())
    {
        $this->getInstance()->setPrivateKey($privateKey);
        return $this;
    }

    /**
     * 检查数据是否存在
     * @param array $fields 数据
     * @return \Qii\Driver\Response
     */
    final public function _exist($fields, $privateKey = array())
    {
        return $this->getInstance()->setPrivateKey($privateKey)->setFieldsVal($fields)->_exist();
    }

    /**
     * 保存数据
     * @param array $fields 数据
     * @return \Qii\Driver\Response
     */
    final public function _save($fields, $privateKey = array())
    {
        return $this->getInstance()->setPrivateKey($privateKey)->setFieldsVal($fields)->_save();
    }

    /**
     * 更新数据
     * @param array $fields 数据
     * @return \Qii\Driver\Response
     */
    final public function _update($fields, $privateKey = array())
    {
        return $this->getInstance()->setPrivateKey($privateKey)->setFieldsVal($fields)->_update();
    }

    /**
     * 删除数据
     * @param array $fields 数据
     * @return \Qii_Response
     */
    final public function _remove($fields, $privateKey = array())
    {
        return $this->getInstance()->setPrivateKey($privateKey)->setFieldsVal($fields)->_remove();
    }

    /**
     * 方法不存在的时候调用$this->db下的方法
     * @param string $method 方法名
     * @param mix $args 参数
     */
    public function __call($method, $args)
    {
        if ($this->db) return call_user_func_array(array($this->db, $method), $args);
    }
}