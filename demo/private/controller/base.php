<?php
namespace controller;

class base extends \Qii\Base\Controller
{
    public $enableDB = true;
    public $enableView = true;
    public function indexAction()
    {
        parent::__construct();
    }

	/**
	 * 初始化view后执行方法
	 */
	protected function initView()
	{
		$this->view->assign('pathes', _include('../private/configure/path.config.php'));
	}
	/**
	 * 当data中带code的时候,自动添加msg
	 *
	 * @param $data
	 * @return string
	 */
	public function jsonEncode($data)
	{
		if (empty($data)) return '{}';
		if (isset($data['code']) && $data['code'] > 0 && !isset($data['msg'])) $data['msg'] = _i($data['code']);
		return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 输出JSON数据后直接退出
	 * @param array $data 数据
	 * @param bool $exit 是否输出数据以后退出
	 */
	public function echoJson($data, $exit = true)
	{
		ob_clean();
		echo $this->jsonEncode($data);
		if ($exit) exit();
	}
}