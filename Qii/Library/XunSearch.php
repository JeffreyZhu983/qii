<?php
namespace Qii\Library;
/**
 * 迅搜插件
 * @link http://www.xunsearch.com/
 */
_require(__DIR__ . '/Third/XS.php');

class XunSearch
{
	public $xsRoot, $project;
	public $charset = 'utf-8';
	private $_xs, $_scws;
	public function __construct($iniFile)
	{
		$this->_xs = new XS($iniFile);
		$this->_xs->setDefaultCharset($this->charset);
		return $this;
	}
	/**
	 * Quickly add a new document (without checking key conflicts)
	 * @param mixed $data XSDocument object or data array to be added
	 */
	public function add($data)
	{
		$this->update($data, true);
	}

	/**
	 * @param mixed $data XSDocument object or data array to be updated
	 * @param boolean $add whether to add directly, default to false
	 */
	public function update($data, $add = false)
	{
		if ($data instanceof XSDocument) {
			$this->_xs->index->update($data, $add);
		} else {
			$doc = new XSDocument($data);
			$this->_xs->index->update($doc, $add);
		}
	}

	/**
	 * @return XSTokenizerScws get scws tokenizer
	 */
	public function getScws()
	{
		if ($this->_scws === null) {
			$this->_scws = new XSTokenizerScws;
		}
		return $this->_scws;
	}

	public function __call($name, $parameters)
	{
		// check methods of xs
		if ($this->_xs !== null && method_exists($this->_xs, $name)) {
			return call_user_func_array(array($this->_xs, $name), $parameters);
		}
		// check methods of index object
		if ($this->_xs !== null && method_exists('XSIndex', $name)) {
			$ret = call_user_func_array(array($this->_xs->index, $name), $parameters);
			if ($ret === $this->_xs->index) {
				return $this;
			}
			return $ret;
		}
		// check methods of search object
		if ($this->_xs !== null && method_exists('XSSearch', $name)) {
			$ret = call_user_func_array(array($this->_xs->search, $name), $parameters);
			if ($ret === $this->_xs->search) {
				return $this;
			}
			return $ret;
		}
        throw new \Qii\Exceptions\CallUndefinedClass(\Qii::i('1105', $name), __LINE__);
	}
}