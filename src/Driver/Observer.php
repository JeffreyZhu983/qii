<?php
/**
 * 观察者
 * @author Jinhui Zhu
 *
 * 用法：
 * class User
 * {
 *        public $observer;
 *        public function __construct()
 *        {
 *            $this->observer = new \Qii_Driver_Observer($this);
 *    }
 *        public function signup($email, $password)
 *        {
 *            //todo
 *            //执行notify通知观察者
 *            $this->observer->notify($email, $password);
 *        }
 * }
 * class emailObserver implements \SplObserver
 * {
 *        public function update(SplSubject $subject)
 *        {
 *            //todo
 *            $email = func_get_arg(1);
 *            $password = func_get_arg(2);
 *            echo '发送邮件到'. $email . ', 你的密码是'. $password . '请妥善保管';
 *        }
 * }
 * $user = new User();
 * $user->observer->attach($emailObserver);
 * $user->signup('email@test.com', '123456');
 */
namespace Qii\Driver;

use SplSubject;
use SplObjectStorage;
use SplObserver;

class Observer implements SplSubject
{
	private $observers = NULL;
	//上下文
	public $context;

	/**
	 * Observer constructor.
	 * @param $context 调用此方法的类
	 */
	public function __construct($context)
	{
		if (!isset($context) || !$context || !is_object($context)) throw new \Exception(\Qii::i(1003), __LINE__);
		
		$this->context = $context;
		$this->observers = new \SplObjectStorage();
	}

	/**
	 * 添加观察者
	 * @param SplObserver $observer
	 */
	public function attach(\SplObserver $observer)
	{
		$this->observers->attach($observer);
	}

	/**
	 * 移除观察者
	 * @param SplObserver $observer
	 */
	public function detach(\SplObserver $observer)
	{
		$this->observers->detach($observer);
	}

	/**
	 * 发送通知 调用此方法需要传递一个参数
	 */
	public function notify()
	{
		$result = array();
		$args = func_get_args();
		array_unshift($args, $this);
		foreach ($this->observers as $observer) {
			$result[] = call_user_func_array(array($observer, 'update'), $args);
		}
		return $result;
	}
}