<?php
namespace Qii\Library;
/**
 * 验证类
 *
 * 如果验证规则为空则则返回true
 * @Author: Jinhui Zhu
 * @Date:   2014-12-31 16:05:49
 * Use:
 *
 *      $data = array(
 *              "username"=>'jinhui.zhu@live.cn',
 *              "qq"=>'119328118',
 *              "nickname"=>'Jinhui.Zhu',
 *              "id"=>'24',
 *      );
 *      $rules = array(
 *              'id'=>array(
 *                      'required'=>true,
 *                      'number'=>true,
 *              ),
 *              'username'=>array(
 *                      'required'=>true,
 *                      'email'=>true,
 *              ),
 *              'qq'=>array(
 *                      'required'=>true,
 *                      'qq'=>true,
 *              ),
 *              'nickname'=>array(
 *                      'required'=>true,
 *              ),
 *                'gender' => array(
 *                        'sets' => '1,2,3,4'
 *                )
 *      );
 *      $msg = array(
 *              'username'=>array(
 *                      'required'=>'用户名必须填写',
 *                      'email'=> '用户名格式不正确',
 *              ),
 *              'qq'=>array(
 *                      'required'=> 'QQ好必须填写',
 *                      'qq'=>'QQ号格式不正确',
 *              ),...
 *      );
 * $validate = new \Qii\Library\Validate();
 * $validate->verify($data, $rules, $msg);
 */
class Validate
{
	const VERSION = '1.2';
	// 验证规则
	public $ruleNames = array(
		'required' => '必填',
		'email' => '邮箱',
		'idcode' => '身份证',
		'number' => '数字',
		'http' => '网址',
		'qq' => 'qq',
		'postcode' => '邮编',
		'ip' => 'ip地址',
		'phone' => '电话',
		'telephone' => '座机',
		'mobile' => '手机',
		'en' => '英文字母',
		'cn' => '中文',
		'account' => '账户',
		'date' => '日期',
		'datetime' => '日期',
		'safe' => '安全字符',
		'password' => '密码',
		'maxlength' => '最大长度',
		'minlength' => '最小长度',
		'length' => '固定长度',
		'rangeof' => '范围',
		'string' => '字符',
		'sets' => '枚举',
		'setsArray' => '数组',
	);
	//出错保存数据用
	protected $invalidFields = array();

	/**
	 * 出错时候保存数据用
	 * @param string $field 字段
	 * @param string $rule 规则名
	 * @param string $value
	 */
	protected function setInvalidFields($field, $rule, $value, $msg = '')
	{
		$this->invalidFields['field'] = $field;
		$this->invalidFields['rule'] = $rule;
		$this->invalidFields['value'] = $value;
		if ($msg) $this->invalidFields['msg'] = $msg;
	}

	/**
	 * 将错误消息保存到invalidFields的msg字段
	 */
	protected function appendMessage($msg)
	{
		$this->invalidFields['msg'] = $msg;
	}

	/**
	 * 获取验证失败的消息
	 */
	public function getErrors()
	{
		return $this->invalidFields;
	}

	/**
	 * 验证函数
	 *
	 * @param array $data [用户要验证的数据]
	 * @param array $rules [验证规则]
	 * @param array $validateErrMsg [错误信息提示]
	 * @return bool [成功返回true, 失败返回错误信息]
	 */
	public function verify($data, $rules, $validateErrMsg = array())
	{
		if (empty ($data))
			return false;
		if (empty ($rules))
			return true;
		//将data转换成数组
		$data = (array)$data;
		//以验证规则作为依据
		foreach ($rules AS $key => $val) {
			$value = isset($data[$key]) ? $data[$key] : null;
			if (is_array($val)) {
				$required = true;
				foreach ($val AS $validType => $v) {
					if ($validType == 'required') {
						$required = $v;
						if ($required && !$this->required($value)) {
							$this->setInvalidFields($key, $validType, $value);
							if (!isset($validateErrMsg[$key][$validType])) {
								$this->appendMessage(\Qii::i(5003, $key));
								return \Qii::i(5003, $key);
							}
							$this->appendMessage($validateErrMsg[$key][$validType]);
							return $validateErrMsg[$key][$validType];
						}
						continue;
					}
					if (!$required && !$this->required($value)) {
						continue;
					}
					if (!isset($this->ruleNames[$validType])) {
						$this->setInvalidFields($key, $validType, $value, \Qii::i(5005, $validType));
						return \Qii::i(5005, $validType);
					}
					if ($v == true) {
						if (!$this->$validType ($value, $v)) {
							$this->setInvalidFields($key, $validType, $value);
							if (!isset($validateErrMsg[$key][$validType])) {
								$this->appendMessage(\Qii::i(5004, $key));
								return \Qii::i(5004, $key);
							}
							$this->appendMessage($validateErrMsg[$key][$validType]);
							return $validateErrMsg[$key][$validType];
						}
					}
				}
			}

		}
		return true;
	}

	/**
	 * 获取规则
	 *
	 * @param String $str
	 * @return Bool
	 */
	public function getRuleNames()
	{
		return $this->ruleNames;
	}

	/**
	 * 设置属性规则
	 * @param array $arr 额外增加的规则
	 */
	public function setRuleNames(array $arr)
	{
		$this->ruleNames = array_merge($this->ruleNames, $arr);
	}

	/**
	 * 验证是否为空
	 *
	 * @param String $str
	 * @return Bool
	 */
	public function required($str)
	{
		if (is_array($str)) return !empty($str);
		return trim($str) != "";
	}

	/**
	 * 验证邮件格式
	 */
	public function email($str)
	{
		return preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/", $str);
	}

	/**
	 * 验证身份证
	 *
	 * @param String $str
	 * @return Bool
	 */
	public function idcode($str)
	{
		return preg_match("/^\d{14}(\d{1}|\d{4}|(\d{3}[xX]))$/", $str);
	}

	/**
	 * 验证http地址
	 *
	 * @param String $str
	 * @return Bool
	 */
	public function http($str)
	{
		return preg_match("/[a-zA-Z]+:\/\/[^\s]*/", $str);
	}

	/**
	 * 匹配QQ号(QQ号从10000开始)
	 *
	 * @param String $str
	 * @return Bool
	 */
	public function qq($str)
	{
		return preg_match("/^[1-9][0-9]{4,}$/", $str);
	}

	/**
	 * 匹配中国邮政编码
	 *
	 * @param String $str
	 * @return bool
	 */
	public function postcode($str)
	{
		return preg_match("/^[1-9]\d{5}$/", $str);
	}

	/**
	 * 匹配ip地址
	 *
	 * @param string $str
	 * @return bool
	 */
	public function ip($str)
	{
		return preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $str);
	}

	/**
	 * 电话号码
	 *
	 * @param string $str
	 * @return bool
	 */
	public function phone($str)
	{
		return $this->telephone($str) || $this->mobile($str);
	}

	/**
	 * 匹配电话格式
	 *
	 * @param string $str
	 * @return bool
	 */
	public function telephone($str)
	{
		return preg_match("/^\d{3}-\d{8}$|^\d{4}-\d{7}$/", $str);
	}

	/**
	 * 匹配手机格式
	 *
	 * @param string $str
	 * @return bool
	 */
	public function mobile($str)
	{
		return preg_match("/^(13[0-9]|15[0-9]|18[0-9])\d{8}$/", $str);
	}

	/**
	 * 匹配26个英文字母
	 *
	 * @param string $str
	 * @return bool
	 */
	public function en($str)
	{
		return preg_match("/^[A-Za-z]+$/", $str);
	}

	/**
	 * 匹配只有中文
	 *
	 * @param string $str
	 * @return array
	 */
	public function cn($str)
	{
		return preg_match("/^[\x80-\xff]+$/", $str);
	}

	/**
	 * 验证账户(字母开头，由字母数字下划线组成，4-20字节)
	 *
	 * @param string $str
	 * @return bool
	 */
	public function account($str)
	{
		return preg_match("/^[a-zA-Z][a-zA-Z0-9_]{3,19}$/", $str);
	}

	/**
	 * 验证数字
	 *
	 * @param int $str
	 * @return bool
	 */
	public function number($str)
	{
		return preg_match("/^[0-9]+$/", $str);
	}

	/**
	 * 验证日期
	 *
	 * @param string $str
	 * @return bool
	 */
	public function date($str)
	{
		return preg_match("/^[\d]{4}\-[\d]{2}\-[\d]{2}/", $str);
	}
	/**
	 * 验证日期
	 * Y-m-d H:i:s
	 * @param string $str
	 * @return bool
	 */
	public function datetime($str)
	{
		return date('Y-m-d H:i:s', strtotime($str)) == $str;
	}

	/**
	 * 验证字符串中，不允许包含怪字符
	 *
	 * @param string $str
	 * @return bool
	 */
	public function safe($str)
	{
		return preg_match('/^[\x7f-\xffA-Za-z0-9_]+$/', $str);
	}

	/**
	 * 验证密码
	 *
	 * @param string $str
	 * @return bool
	 */
	public function password($str)
	{
		return preg_match('/^(?![A-Z]+$)(?![a-z]+$)(?!\d+$)(?![\W_]+$)\S+$/', $str);
	}

	/**
	 * 最大长度
	 *
	 * @param string $str
	 * @param int $len
	 * @return bool
	 */
	public function maxlength($str, $len)
	{
		return strlen($str) <= $len;
	}

	/**
	 * 最小长度
	 *
	 * @param string $str
	 * @param int $len
	 * @return bool
	 */
	public function minlength($str, $len)
	{
		return strlen($str) >= $len;
	}

	/**
	 * 字符串固定长度
	 *
	 * @param string $str
	 * @param int $len
	 * @return bool
	 */
	public function length($str, $len)
	{
		return strlen($str) == $len;
	}

	/**
	 * 字符长度范围
	 *
	 * @param string $str
	 * @param string $rangeof 1,10
	 * @return bool
	 */
	public function rangeof($str, $rangeof)
	{
		$range = explode(',', str_replace(' ', '', $rangeof));
		if (count($range) != 2 || intval($range[0]) > intval($range[1])) return false;
		return $this->minlength($str, intval($range[0])) && $this->maxlength($str, intval($range[1]));
	}

	/**
	 * 验证数据是否是字符串
	 *
	 * @param string $str
	 * @return bool
	 */
	public function string($str)
	{
		return is_string($str);
	}

	/**
	 * 返回数据是否在列表中
	 *
	 * @param string $str
	 * @param string $set
	 * @return bool
	 */
	public function sets($str, $set)
	{
		$sets = preg_replace("/(\"|\')/", "", explode(",", $set));
		return in_array($str, $sets);
	}

	/**
	 * 返回数据是否在列表中
	 *
	 * @param string $str
	 * @param string $set
	 * @return bool
	 */
	public function setsArray($str, $sets)
	{
		return in_array($str, $sets);
	}
}