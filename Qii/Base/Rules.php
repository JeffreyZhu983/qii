<?php
namespace Qii\Base;
class Rules 
{
    /**
     * @var array $rules 验证规则
     */
    private $rules = array();
    /**
     * @var array $message 验证消息
     */
    private $message = array();
    /**
     * @var array $data 待验证的数据
     */
    private $data = array();
    /**
     * 需要验证的字段
     */
    private $forceValidKey = array();
    /**
     * 选择其中一个去验证
     */
    private $optionValidKey = array();
    /**
     * 字段名称
     */
    private $files = array();

    public function __construct()
    {
        $this->constants();
        $this->clean();
    }

    public function getDefaultValues()
    {
        $data = array();
        foreach($this->fields() AS $key => $val)
        {
            $data[$val] = '';
        }
        return $data;
    }

    /**
     * 返回数据表中的字段名
     *
     * @return array
     */
    public function fields()
    {
        return $this->fields;
    }
    /**
     * 添加字段
     * @param string | array $fields 字段名
     */
    public function addFields($fields)
    {
        if(empty($fields)) return;
        if(is_array($fields))
        {
            return array_map(function ($n) {
                return $this->addFields($n);
            }, $fields);
        }
        $this->fields[] = $fields;
    }
    /**
     * 定义规则
     */
    public function constants()
    {
        return array();
    }

    /**
     * 获取所有字段中的数据
     *
     * @return array
     */
    public function getValues()
    {
        return $this->data;
    }

    /**
     * 获取制定字段数据
     *
     * @param string $field 字段名
     * @return mixed|null
     */
    public function getValue($field)
    {
        if(isset($this->data[$field])) return $this->data[$field];
        return null;
    }
    /**
     * 清空数据
     */
    public function clean()
    {
        $this->data = array();
        $this->fields = array();
        $this->forceValidKey = array();
    }
    /**
     * 获取指定字段的规则配置
     * @param string $field
     * @return array
     */
    public function get($field)
    {
        $data = array();
        if(isset($this->rules[$field]))
        {
            $data['rules'] = $this->rules[$field];
            $data['message'] = isset($this->message[$field]) ? $this->message[$field] : '未设置';
        }
        return $data;
    }
    /**
     * 自动获取rules中定义的规则
     * @param string $method 方法名
     */
    public function autoForceValidKeyForMethod($method)
    {
        if(!$method) throw new Exception(__METHOD__ . ' parameter error.', 1);
        if(method_exists($this, $method))
        {
            $this->$method();
        }
        if(!$method) throw new Exception(__METHOD__ . ' undefined.', 1);
    }
    /**
     * 添加规则
     */
    public function addRules($field, $key, $isValid, $message)
    {
        if(!$field || !$key || $isValid === null || $isValid === '') return;
        if(!$this->isAllow($key)) return;
        $this->rules[$field][$key] = $isValid;
        $this->message[$field][$key] = $message;
    }
    /**
     * 移除规则
     * @param string $fields 字段名
     */
    public function removeRules($fields)
    {
        if(!$fields) return;
        if(is_array($fields))
        {
            return array_map(function ($n) {
                return $this->removeRules($n);
            }, $fields);
        }
        if(isset($this->rules[$fields])) 
        {
            unset($this->rules[$fields]);
            unset($this->message[$fields]);
        }
    }
    /**
     * 添加必须验证用的字段
     * @param string $key 字段名
     */
    public function addForceValidKey($key)
    {
        if(!$key) return;
        if(is_array($key))
        {
            foreach ($key as $k => $value) 
            {
                $this->addForceValidKey($value);
            }
        }
        else
        {
            if(!in_array($key, $this->forceValidKey))
            {
                $this->forceValidKey[] = $key;
            }
        }
    }

    public function removeForceValidKey($key)
    {
        foreach ($this->forceValidKey as $key => $value) 
        {
            if($value == $key)
            {
                unset($this->forceValidKey);
            }
        }
    }
    /**
     * 添加必须其中某一个字段，选择不为空的字段去验证
     * 
     * @param string $key 字段名
     */
    public function addOptionValidKey($key)
    {
        if(!$key) return;
        if(is_array($key))
        {
            foreach ($key as $k => $value) 
            {
                $this->addOptionValidKey($value);
            }
        }
        else
        {
            if(!in_array($key, $this->optionValidKey))
            {
                $this->optionValidKey[] = $key;
            }
        }
    }
    /**
     * 给数据添加属性
     * @param string $field 字段
     * @param mix $val 值
     */
    public function addValue($field, $val)
    {
        if(in_array($field, $this->fields())){
            $this->data[$field] = $val;
        }
    }
    /**
     * 添加数据
     * @param array $data 数据
     */
    public function addValues($data)
    {
        foreach ($data AS $field => $value){
            $this->addValue($field, $value);
        }
    }
    /**
     * 验证数据，验证将返回数据及验证结果
     * @return bool
     */
    public function verify()
    {
        $data = array();
        $data['data'] = $this->data;
        $data['code'] = 0;
        $data['valid'] = true;
        $data['msg'] = '';
        if(empty($this->forceValidKey))
        {
            return $data;
        }
        $valid = \_loadClass('\Qii\Library\Validate');
        //将optionValidKey中不为空的字段添加到必须验证的字段中去
        //如果选择验证的都没数据就提示参数错误
        $options = array();
        foreach($this->optionValidKey AS $key)
        {
            if($this->data[$key] && $this->data[$key] != '')
            {
                $options[] = $key;
                $this->addForceValidKey($key);
            }
        }
        if(count($this->optionValidKey) > 0 && count($options) == 0)
        {
                $data['valid'] = false;
                $data['code'] = 20000;
                $data['errorInfo'][] = join(',', $this->optionValidKey) . ' 字段必须填写一个';
                $data['msg'] = _i($data['code']);
                return $data;
        }
        foreach($this->forceValidKey AS $key)
        {
            $rule = $this->get($key);
            if(!$rule)
            {
                $rule['rules'] = array('required' => true);
                $rule['message'] = array('required' => $key .'不能为空');
            }
            $result = $valid->verify(
                array($key => isset($this->data[$key]) ? $this->data[$key] : ''),
                array($key => $rule['rules']),
                array($key => $rule['message'])
            );
            if($result !== true){
                $data['valid'] = false;
                $data['code'] = 20000;
                $data['errorInfo'][$key] = $result;
            }
        }
        if($data['code'] > 0)
        {
            $data['msg'] = _i($data['code']);
        }
        return $data;
    }
    /**
     * 验证数据，验证将返回数据及验证结果
     * @return bool
     */
     /*
    public function verify()
    {
        $data = array();
        $data['data'] = array();
        $valid = _loadClass('Qii_Library_Validate');
        foreach($this->data AS $key => $val)
        {
            $rule = $this->get($key);
            $data['data'][$key] = $val;
            if(empty($rule))
            {
                continue;
            }
            $result = $valid->verify(array($key => $val), array($key => $rule['rules']), array($key => $rule['message']));
            if($result !== true){
                $data['valid'][$key] = $result;
            }
        }
        return $data;
    }*/
    /**
     * 是否在允许的规则内
     * @param string $key 规则名称
     * @return bool
     */
    public function isAllow($key)
    {
        $allow = array(
            'required', 'email', 'idcode', 'http',
            'qq', 'postcode', 'ip', 'phone', 'telephone',
            'mobile', 'en', 'cn', 'account', 'number', 'date',
            'safe', 'password', 'maxlength', 'minlength', 'length',
            'rangeof', 'string', 'sets', 'setsArray'
        );
        return in_array($key, $allow);
    }
}