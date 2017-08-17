<?php
namespace controller;

class dirs extends base
{
	public $defaultPath = './';

	public function __construct()
	{
		parent::__construct();
		$this->_view->assign('defaultPath', $this->defaultPath);
	}

	public function indexAction()
	{
		$path = $this->_request->get('path', './');
		if (!is_dir($path)) {
			$this->showErrorPage('指定文件不存在');
			return;
		}
		$files = \helper\tools::getFolders($path);
		$usePath = explode('/', $path);
		$visitPathes = \helper\tools::getVisitPath($path);
		$this->_view->assign('visitPathes', $visitPathes);
		$currentPath = array_pop($usePath);
		$this->_view->assign('currentPath', $currentPath);
		$this->_view->assign('usePath', join('/', $usePath));
		$this->_view->assign('files', $files);
		$this->_view->display('manage/folder/dir.html');
	}

	/**
	 * 查看指定文件内容，仅限于php、css、js、cpp、.h、java、python类型文件
	 */
	public function fileAction()
	{
		$file = $this->_request->get('file');
		if (!is_file($file)) {
			$this->showErrorPage($file . '指定文件不存在');
			return;
		}
		//如果是php、css、js、cpp、.h、java、python文件就直接显示内容，否则下载
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		if (!in_array($extension, array('php', 'css', 'js', 'html', 'cpp', 'h', 'py', 'java', 'ini'))) {
			$download = new \Library\download();
			$download->download($file);
			return;
		}
		$visitPathes = \helper\tools::getVisitPath(pathinfo($file, PATHINFO_DIRNAME));
		$this->_view->assign('visitPathes', $visitPathes);
		$this->_view->assign('file', pathinfo($file, PATHINFO_BASENAME));
		$usePath = pathinfo($file, PATHINFO_DIRNAME);
		$this->_view->assign('usePath', $usePath);
		$content = file_get_contents($file);
		$encode = mb_detect_encoding($content, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
		if ($encode != 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encode);
		}
		$this->_view->assign('code', $content);
		$this->_view->display('manage/folder/file.html');
	}

	public function removeAction()
	{
		$file = $this->_request->get('file');
		$isAjax = $this->_request->get('isAjax', 0);
		if (!\helper\tools::allowRemove($file)) {
			$isAjax == 1 ? $this->echoJson(array('code' => 1, 'msg' => '此目录或文件不允许删除')) : $this->showTipsPage('此目录或文件不允许删除');
			return;
		}
		$result = \helper\tools::removeFile($file);
		if ($result) {
			$isAjax == 1 ? $this->echoJson(array('code' => 0, 'msg' => '删除成功')) : $this->showTipsPage('删除成功');
			return;
		}
		$isAjax == 1 ? $this->echoJson(array('code' => 1, 'msg' => '删除失败')) : $this->showTipsPage('删除失败');
	}

	/**
	 * 下载指定文件
	 */
	public function downAction()
	{
		$file = $this->_request->get('file');
		if (!is_file($file)) {
			$this->showErrorPage($file . '指定文件不存在');
			return;
		}
		$download = new \Library\download();
		$download->download($file);
	}
}