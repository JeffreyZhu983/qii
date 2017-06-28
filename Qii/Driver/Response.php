<?php
/**
 * 数据库操作Response类，返回response对象{code:0, body:{}}
 * @author Zhu Jinhui<jinhui.zhu@live.cn> 2015-12-31 17:49
 * 用法：
 * $response = array();
 *
 * $response[] = new \Qii\Driver\Response(array(
 *        'code' => \Qii\Driver\Response::DO_SUCCESS,
 *        'body' => array(
 *            'operate' => 'save',
 *            'result' => 10
 *        )
 * ));
 * $response[] = \Qii\Driver\Response::Success('save', 10);
 * $response[] = new Response(array(
 *        'code' => \Qii\Driver\Response::DO_FAIL,
 *        'body' => array(
 *            'operate' => 'save',
 *            'result' => '操作失败'
 *        )
 * ));
 * $response[] = Qii\Driver\Response::Fail('save', '操作失败');
 * $response[] = new Response(array(
 *        'code' => Qii\Driver\Response::DOES_EXISTS,
 *        'body' => array(
 *            'operate' => 'save',
 *            'result' => array(
 *                    'uid' => 1,
 *                    'email' => 'antsnet@163.com'
 *            )
 *        )
 * ));
 * $response[] = \Qii\Driver\Response::Exist('save', array('uid' => 10, 'email' => 'antsnet@163.com'));
 * $response[] = new Response(array(
 *        'code' => \Qii\Driver\Response::DOES_NOT_EXISTS,
 *        'body' => array(
 *            'operate' => 'save',
 *            'result' => array()
 *        )
 * ));
 * $response[] = \Qii\Driver\Response::NotExist('save', 10);
 * $response[] = new Response(array(
 *        'code' => \Qii\Driver\Response::FAIL_FOR_VALIDATE,
 *        'body' => array(
 *            'operate' => 'save',
 *            'result' => array(
 *                'fields' => array(
 *                    array(
 *                        'name' => 'email',
 *                        'msg' => '验证失败/格式不正确'
 *                    )
 *                )
 *            )
 *        )
 * ));
 * $response[] = \Qii\Driver\Response::FailValidate('save', array('fields' => array('name' => 'email', 'msg' => '验证失败/格式不正确')));
 *
 * $response[] = new Response(array(
 *        'code' => \Qii\Driver\Response::FAIL_FOR_SAVE,
 *        'body' => array(
 *            'operate' => 'save',
 *            'result' => '连接错误'
 *        )
 * ));
 * $result[] = \Qii\Driver\Response::FailSave('save', '链接错误');
 * foreach($response AS $res)
 * {
 *    if($res->isError())
 *    {
 *        echo "<pre>". $res->getCode() .'&nbsp;'. print_r($res->getError(), true) ."</pre>";
 *    }
 *    else
 *    {
 *        echo '操作成功' . print_r($res->getResult(), 1);
 *    }
 * }
 */
namespace Qii\Driver;

class Response
{
	const VERSION = '1.2';
	//成功
	const DO_SUCCESS = 0;
	//失败
	const DO_FAIL = 1;
	//数据已经存在
	const DOES_EXIST = 100;
	//数据不存在
	const DOES_NOT_EXIST = 101;
	//验证失败
	const FAIL_FOR_VALIDATE = 102;
	//保存失败
	const FAIL_FOR_SAVE = 103;
	//更新失败
	const FAIL_FOR_UPDATE = 104;
	//删除失败
	const FAIL_FOR_REMOVE = 105;
	//类为定义
	const UNDEFINED_CLASS = 106;
	//方法为定义
	const UNDEFINED_METHOD = 107;
	//是否有错误
	public $isError = false;
	//状态码，对应上边的常量
	public $code;
	//返回的内容
	public $body;

	/**
	 * 实例化response对象
	 */
	public function __construct()
	{
		$this->code = 0;
		$this->body = array();
		return $this;
	}

	/**
	 * 设置response的信息
	 */
	public function set(array $data)
	{
		if (!isset($data['code']) || !isset($data['body'])
			|| !isset($data['body']['operate']) || !isset($data['body']['result'])
		) {
			throw new \Exception('Response muse have code , body , body[operate] and body[result] element');
		}
		foreach ($data AS $key => $val) {
			$this->$key = $val;
		}
		$this->isError = $this->isError();
		return $this;
	}

	/**
	 * 成功
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function Success($operate, $result)
	{
		return self::Instance(self::DO_SUCCESS, $operate, $result);
	}

	/**
	 * 失败
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function Fail($operate, $result)
	{
		return self::Instance(self::DO_FAIL, $operate, $result);
	}

	/**
	 * 记录已存在
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function Exist($operate, $result)
	{
		return self::Instance(self::DOES_EXIST, $operate, $result);
	}

	/**
	 * 记录不存在
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function NotExist($operate, $result)
	{
		return self::Instance(self::DOES_NOT_EXIST, $operate, $result);
	}

	/**
	 * 验证失败
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function FailValidate($operate, $result)
	{
		return self::Instance(self::FAIL_FOR_VALIDATE, $operate, $result);
	}

	/**
	 * 保存失败
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function FailSave($operate, $result)
	{
		return self::Instance(self::FAIL_FOR_SAVE, $operate, $result);
	}

	/**
	 * 更新失败
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function FailUpdate($operate, $result)
	{
		return self::Instance(self::FAIL_FOR_UPDATE, $operate, $result);
	}

	/**
	 * 删除失败
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function FailRemove($operate, $result)
	{
		return self::Instance(self::FAIL_FOR_REMOVE, $operate, $result);
	}

	/**
	 * 方法未定义
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function UndefinedMethod($operate, $result)
	{
		return self::Instance(self::UNDEFINED_METHOD, $operate, $result);
	}

	/**
	 * 类失败
	 * @param string $operate 操作类型
	 * @param mix $result 结果
	 * @return Qii\Driver\Response
	 */
	public static function UndefinedClass($operate, $result)
	{
		return self::Instance(self::UNDEFINED_CLASS, $operate, $result);
	}

	/**
	 * 直接初始化Qii\Driver\Response 对象
	 */
	public static function Instance($code, $operate, $result)
	{
		$data = array('code' => $code, 'body' => array('operate' => $operate, 'result' => $result));
		return (new Qii\Driver\Response())->set($data);
	}

	/**
	 * 获取操作类型
	 */
	public function getOperate()
	{
		if (isset($this->body['operate'])) return $this->body['operate'];
		throw new \Exception('Call undefined operate');
	}

	/**
	 * 返回body中的result,默认返回_result字段
	 * @param string $key 返回字段
	 * @return mix
	 */
	public function getResult($key = '_result')
	{
		if ($key) {
			return isset($this->body['result']) && isset($this->body['result'][$key]) ? $this->body['result'][$key] : null;
		}
		return $this->body['result'];
	}

	/**
	 * 返回错误信息
	 */
	public function getMessage()
	{
		$message = array(
			0 => '成功',
			1 => '失败',
			100 => '数据已经存在',
			101 => '数据不存在',
			102 => '验证失败',
			103 => '保存失败',
			104 => '更新失败',
			105 => '删除失败',
			106 => '类未定义',
			107 => '方法未定义'
		);
		$code = $this->getCode();
		if (isset($message[$code])) return $message[$code] . $this->getResult();
		return $this->getResult('message');
	}

	/**
	 * 返回错误信息，如果无错误即返回false
	 * @return array
	 */
	public function getErrors()
	{
		$code = $this->code;
		if ($code == self::DO_SUCCESS) return false;
		return $this->getResult();
	}

	/**
	 * 是否包含错误信息
	 */
	public function isError()
	{
		$code = $this->code;
		return $code == self::DO_SUCCESS ? false : true;
	}

	/**
	 * 当调用get...的时候使用
	 */
	public function __call($method, $args)
	{
		if (substr($method, 0, 3) == 'get') {
			$propertty = strtolower(substr($method, 3));
			if (property_exists($this, $propertty)) return $this->$propertty;
		}
		throw new \Qii\Exceptions\MethodNotFound(\Qii::i(1101, $method), __LINE__);
	}
}