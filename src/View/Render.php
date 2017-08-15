<?php
namespace Qii\View;

class Render
{
	protected $_view;
	public function __construct(&$view, \Qii\View\Resource $resource)
	{
		$this->_view = $view;
	}

	/**
	 * renderJS
	 * @param \widget\resource $resource 资源
	 */
	public function renderLinkJs(\Qii\View\Resource $resource)
	{
		$blockJs = array();
		if (isset(\Qii\View\Dependence::$dependence['blockLinkJs'])) {
			$resource->addBlockLinkJs(\Qii\View\Dependence::$dependence['blockLinkJs']);
		}
		if ($resource->blockLinkJs) {
			if (is_array($resource->blockLinkJs)) {
				foreach ($resource->blockLinkJs AS $js) {
					if ($js == '') continue;
					$blockJs[] = '<script src="' . \Qii\Request\Url::getSourceFullUrl($js) . '"></script>';
				}
			} else {
				$blockJs[] = '<script src="' . \Qii\Request\Url::getSourceFullUrl($resource->blockLinkJs) . '"></script>';
			}
		}
		$this->_view->assign('blockLinkJs', "\n" . join("\n", $blockJs) . "\n");
	}

	/**
	 * renderCss
	 * @param \widget\resource $resource 资源
	 */
	public function renderLinkCss(\Qii\View\Resource $resource)
	{
		$blockCss = array();
		if (isset(\Qii\View\Dependence::$dependence['blockLinkCss'])) {
			$resource->addBlockLinkCss(\Qii\View\Dependence::$dependence['blockLinkCss']);
		}
		if ($resource->blockLinkCss) {
			if (is_array($resource->blockLinkCss)) {
				foreach ($resource->blockLinkCss AS $css) {
					if ($css == '') continue;
					$blockCss[] = '<link href="' . \Qii\Request\Url::getSourceFullUrl($css) . '" rel="stylesheet">';
				}
			} else {
				$blockCss[] = '<link href="' . \Qii\Request\Url::getSourceFullUrl($resource->blockLinkCss) . '" rel="stylesheet">';
			}
		}
		$this->_view->assign('blockLinkCss', "\n" . join("\n", $blockCss) . "\n");
	}

	/**
	 * renderJS
	 * @param \widget\resource $resource 资源
	 */
	public function renderJs(\Qii\View\Resource $resource)
	{
		$blockJs = '';
		if (isset(\Qii\View\Dependence::$dependence['blockJs'])) {
			$resource->addBlockJs(\Qii\View\Dependence::$dependence['blockJs']);
		}
		if ($resource->blockJs) {
			if (is_array($resource->blockJs)) {
				foreach ($resource->blockJs AS $js) {
					if ($js == '') continue;
					$blockJs .= $this->_view->fetch($js);
				}
			} else {
				$blockJs .= $this->_view->fetch($resource->blockJs);
			}
		}
		$blockJs .= join('\n', \Qii\View\Dependence::$blockJS);
		$this->_view->assign('blockJs', "\n" . $blockJs . "\n");
	}

	/**
	 * renderCss
	 * @param \widget\resource $resource 资源
	 */
	public function renderCss(\Qii\View\Resource $resource)
	{
		$blockCss = '';
		if (isset(\Qii\View\Dependence::$dependence['blockCss'])) {
			$resource->addBlockCss(\Qii\View\Dependence::$dependence['blockCss']);
		}
		if ($resource->blockCss) {
			if (is_array($resource->blockCss)) {
				foreach ($resource->blockCss AS $css) {
					if ($css == '') continue;
					$blockCss .= $this->_view->fetch($css);
				}
			} else {
				$blockCss .= $this->_view->fetch($resource->blockCss);
			}
		}
		$blockCss .= join('\n', \Qii\View\Dependence::$blockCss);
		$this->_view->assign('blockCss', "\n" . $blockCss . "\n");
	}

	/**
	 * 通过页面定义的资源去显示内容
	 *
	 * @param string $tpl 模板名
	 */
	public function render(\Qii\View\Resource $resource)
	{
		$this->_view->assign('pageTitle', $resource->title);
		$this->_view->assign('bodyClass', $resource->bodyClass);
		$html = array();
		$html[] = '<div class="' . $resource->wrapperClass . '">';
		$dependence = array();
		//todo 针对dependence当方法在后边的情况还没处理完
		$executed = array();
		foreach ($resource->resource AS $index => $res) {
			$callFunction = $res;
			$className = array_shift($res);
			$method = array_shift($res);

			//如果是存在依赖，先跳过，等依赖的执行了再执行
			if (isset($resource->dependence[$className . ':' . $method]) && !isset($executed[$resource->dependence[$className . ':' . $method]])) {
				$dependence[$resource->dependence[$className . ':' . $method]][] = array($index, $callFunction);
				$html[$index] = '';
				continue;
			}
			$executed[$className . ':' . $method] = true;
			$class = \Qii\Autoloader\Psr4::loadClass($className);
			$class->controller = $this;
			$class->actionId = $this->_action;
			$class->input = $resource->input;
			//如果是返回了false，就直接退出
			$result = call_user_func_array(array($class, $method), $res);
			if ($result === false) {
				return false;
			}
			$html[$index] = $result;
			//如果是依赖的方法已经执行，此处再执行原有的方法
			if (isset($dependence[$className . ':' . $method])) {
				foreach ($dependence[$className . ':' . $method] AS $key => $val) {
					$i = $val[0];
					$subRes = $val[1];
					$className = array_shift($subRes);
					$method = array_shift($subRes);

					$class = \Qii\Autoloader\Psr4::loadClass($className);
					$class->controller = $this;
					$class->actionId = $this->_action;
					$class->input = $resource->input;
					$executed[$className . ':' . $method] = true;
					$result = call_user_func_array(array($class, $method), $subRes);
					//如果是返回了false，就直接退出
					if ($result === false) {
						return false;
					}
					$html[$i] = $result;
					unset($dependence[$key]);
				}
			}
		}
		$html[] = '</div>';
		$html[] = join('\n', \Qii\View\Dependence::$blockHTML);;

		$this->_view->assign('bodyBlock', join("\n", $html));
	}
}