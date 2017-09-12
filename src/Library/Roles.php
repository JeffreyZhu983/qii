<?php
namespace Qii\Library;

/**
 * 权限认证
 * @author Jinhui Zhu <jinhui.zhu@live.cn> 2016-01-15 11:23
 * 用法：
 * $roles = array(
 *    'user' => array('add', 'update', 'remove', 'view')
 * );
 * $user = array(
 *     'user' => array('add')
 * );
 * $rolesCls = new \Qii\Library\Roles();
 * $rolesCls->setRoles($roles);
 * $rolesCls->setUserRoles($user);
 *
 * $bool = $rolesCls->verify('user.update');//user下update权限
 * $bool = $rolesCls->verify('user');//user下所有权限
 * $bool = $rolesCls->verify('user.*');//user下所有权限
 * var_dump($bool);
 * $array = $rolesCls->batchVeryfy(array('user.add', 'user.update', 'user.remove'));
 * print_r($array);
 */
class Roles
{
	/**
	 * 系统权限表
	 * @var array $roles
	 */
	private $roles = array();
	/**
	 * 用户权限表
	 * @var array $roles
	 */
	private $userRoles = array();

	/**
	 * 初始化，可以将系统权限和用户权限都带上
	 */
	public function __construct(array $roles = array(), array $userRoles = array())
	{
		$this->roles = $roles;
		$this->userRoles = $userRoles;
	}

	/**
	 * 设置系统权限
	 * @param array $roles
	 */
	public function setRoles(array $roles)
	{
		$this->roles = $roles;
	}

	/**
	 * 获取系统权限
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * 设置用户权限
	 * @param array $userRoles
	 */
	public function setUserRoles(array $userRoles)
	{
		$this->userRoles = $userRoles;
	}

	/**
	 * 获取用户权限
	 */
	public function getUserRoles()
	{
		return $this->userRoles;
	}

	/**
	 * 验证用户是否有权限执行相关操作
	 * @param string $operation
	 * @return bool
	 */
	public function verify($operation = 'user.add')
	{
		list($controller, $action) = array_pad(explode('.', $operation), 2, '*');
		if ($controller == '*') return true;

		$this->roles[$controller] = isset($this->roles[$controller]) ? $this->roles[$controller] : array();
		//如果权限列表里边没有对应的权限就返回true
		if (!in_array($action, $this->roles[$controller])) {
			return true;
		}
		//如果操作类型为*也返回true
		if ($action == '*') {
			return true;
		}
		$this->userRoles[$controller] = isset($this->userRoles[$controller]) ? $this->userRoles[$controller] : array();
		//如果用户权限列表中找不到对应权限就返回false
		if (!in_array($action, $this->userRoles[$controller])) {
			return false;
		}
		return true;
	}

	/**
	 * 批量验证用户权限
	 * @param array $operations
	 * @return array
	 */
	public function batchVeryfy(array $operations = array())
	{
		$result = array();
		foreach ($operations AS $val) {
			$result[$val] = $this->verify($val);
		}
		return $result;
	}
}