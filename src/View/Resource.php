<?php
/**
 * 资源
 * 
 */
namespace Qii\View;

class Resource
{
	//页面标题
	public $title = '';
	//meta 标签
	public $meta = array();
	public $htmlBlock = array();
	public $htmlBlockJS = array();
	public $htmlBlockCss = array();

	//块js，里边可能包含js代码及js链接，是完整的文件
	public $blockJs = '';
	//js路径，渲染的时候加上标签
	public $blockLinkJs = '';
	//css块，css代码及css文件路径，是完整的css文件
	public $blockCss = '';
	//css路径，渲染的时候加上标签
	public $blockLinkCss = '';
	//资源处理队列依赖的模块，如果有依赖就等依赖执行完以后再执行
	public $dependence = array();
	//资源处理队列
	public $resource = array();
	public function __construct(array $resource)
	{
		if(empty($resource)) _e(100003, __LINE__);
		$this->resource($resource);
		return $this;
	}

	/**
	 * 添加blocks
	 */
	public function addBlocks($blocks)
	{
		if (!$blocks) return;
		if (is_array($blocks)) {
			$this->addBlocks($blocks);
		}
		$this->blocks .= $blocks;
	}

	/**
	 * 添加JS
	 */
	public function addblockLinkJs($js)
	{
		if (!$js) return;
		if (empty($this->blockLinkJs)) $this->blockLinkJs = array();
		if ($this->blockLinkJs && !is_array($this->blockLinkJs)) {
			$this->blockLinkJs = array($this->blockLinkJs);
		}
		if (is_array($js)) {
			$this->blockLinkJs = array_merge($this->blockLinkJs, $js);
		} else {
			$this->blockLinkJs[] = $js;
		}
		$this->blockLinkJs = $this->array_unique($this->blockLinkJs);
		return $this->blockLinkJs;
	}

	/**
	 * 添加js block
	 */
	public function addBlockJs($js)
	{
		if (!$js) return;
		if (empty($this->blockJs)) $this->blockJs = array();
		if ($this->blockJs && !is_array($this->blockJs)) {
			$this->blockJs = array($this->blockJs);
		}
		if (is_array($js)) {
			$this->blockJs = array_merge($this->blockJs, $js);
		} else {
			$this->blockJs[] = $js;
		}
		$this->blockJs = $this->array_unique($this->blockJs);
		return $this->blockJs;
	}

	/**
	 * 添加css
	 */
	public function addBlockLinkCss($css)
	{
		if (!$css) return;
		if (empty($this->blockLinkCss)) $this->blockLinkCss = array();
		if ($this->blockLinkCss && !is_array($this->blockLinkCss)) {
			$this->blockLinkCss = array($this->blockLinkCss);
		}
		if (is_array($css)) {
			$this->blockLinkCss = array_merge($this->blockLinkCss, $css);
		} else {
			$this->blockLinkCss[] = $css;
		}
		$this->blockLinkCss = $this->array_unique($this->blockLinkCss);
		return $this->blockLinkCss;
	}

	/**
	 * 添加css block
	 */
	public function addBlockCss($css)
	{
		if (!$css) return;
		if (empty($this->blockCss)) $this->blockCss = array();
		if ($this->blockCss && !is_array($this->blockCss)) {
			$this->blockCss = array($this->blockCss);
		}
		if (is_array($css)) {
			$this->blockCss = array_merge($this->blockCss, $css);
		} else {
			$this->blockCss[] = $css;
		}
		$this->blockCss = array_unique($this->blockCss);
		return $this->blockCss;
	}
	/**
	 * 避免重复
	 * @array array $array
	 */
	public function array_unique($array)
	{
		if (is_array($array)) return array_unique($array);
		return $array;
	}

	/**
	 * 格式化资源数据
	 * @return stdClass
	 */
	public function resource(array $resource)
	{
		if (!isset($resource['title'])) _e(100001);
		if (!isset($resource['resource']) || !is_array($resource['resource'])) _e(100002);
		$this->title = $resource['title'];
		$this->blockJs = isset($resource['blockJs']) ? $this->array_unique($resource['blockJs']) : '';
		$this->blockCss = isset($resource['blockCss']) ? $this->array_unique($resource['blockCss']) : '';
		$this->blockLinkJs = isset($resource['blockLinkJs']) ? $this->array_unique($resource['blockLinkJs']) : '';
		$this->blockLinkCss = isset($resource['blockLinkCss']) ? $this->array_unique($resource['blockLinkCss']) : '';
		$this->dependence = isset($resource['dependence']) ? $resource['dependence'] : array();
		$this->resource = isset($resource['resource']) ? $resource['resource'] : array();
		return $this;
	}
}