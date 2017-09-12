<?php

/**
 * Class global_helper
 *
 * 全局方法，主要使用在模板中
 */
namespace helper;
class globals
{
	public static function rules($rule, $name, $validate, $invalidMessage = array(), $extRules = array())
	{
		$ruleText = isset($validate[$rule]) ? $validate[$rule] : $rule;
		$message = isset($invalidMessage[$name]) && isset($invalidMessage[$name][$rule]) ? $invalidMessage[$name][$rule] : '';
		$ruleValue = isset($extRules[$name]) && isset($extRules[$name][$rule]) ? $extRules[$name][$rule] : '';

		if (in_array($rule, array('length', 'minlength', 'maxlength', 'rangeof', 'sets'))) {
			return '<label class="w160">' . $ruleText . '&nbsp;<input type="text" class="scinput display_left" name="rules[extRules][' . $name . '][' . $rule . ']" id="rules[extRules][' . $name . '][' . $rule . ']" placeholder="' . $ruleText . '规则" value="' . $ruleValue . '"  />&nbsp;<input type="text" class="scinput" name="rules[invalidMessage][' . $name . '][' . $rule . ']" id="rules[invalidMessage][' . $name . '][' . $rule . ']" placeholder="' . $ruleText . '错误提示" value="' . $message . '" /></label>';
		}
		return '<label class="w160">' . $ruleText . '&nbsp;<input type="text" class="scinput" name="rules[invalidMessage][' . $name . '][' . $rule . ']" id="rules[invalidMessage][' . $name . '][' . $rule . ']" placeholder="' . $ruleText . '错误提示" value="' . $message . '" /></label>';
	}

	/**
	 * 判断string是否包含html标签
	 *
	 * @param $string
	 * @return bool
	 */
	public static function hasHtmlTag($string)
	{
		return $string != strip_tags($string);
	}

	/**
	 * 生成带参数的URL
	 *
	 * @param String $url
	 * @param Array $param
	 * @return String
	 */
	public static function createURL($url, $param = array())
	{
		return \Qii::getInstance('Request')->url($url, $param);
	}

	/**
	 * 跳转连接
	 *
	 */
	public static function location($url, $param = array())
	{
		$url = self::createURL($url, $param);
		header('Location: ' . $url);
		die();
	}

	/**
	 * POST数据的时候需要将此参数带上
	 *
	 */
	public static function safeForm()
	{
		return;
	}

	/**
	 * 设置Cookie
	 *
	 */
	public static function cookie($key, $value = null, $expired = 86400)
	{
		if ($value === null) return isset($_COOKIE[$key]) ? $_COOKIE[$key] : '';
		setcookie($key, $value, time() + $expired, '/');
	}

	/**
	 * 获取get值
	 *
	 */
	public static function get($key)
	{
		return \Qii::segment($key);
	}

	/**
	 * 获取当前连接地址
	 *
	 */
	public static function getCURL()
	{
		return \Qii::instance('URI')->getCurrentURL();
	}

	/**
	 * 获取网站路径的URL
	 * @return string
	 */
	public static function pathUrl()
	{
		return \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Request\Http')->getWebHost() . \Qii\Autoloader\Psr4::getInstance()->loadClass('\Qii\Request\Http')->getPath();
	}

	/**
	 * 获取文件的路径
	 * @param String $file
	 * @return String
	 */
	public static function getFile($file)
	{
		return self::pathUrl() . '/' . ltrim($file, '/');
	}

	/**
	 * 将绝对地址补成全路径URL
	 * @param String $url
	 * @return string
	 */

	public static function getFullUrl($url = 'index', $ext = '.html')
	{
		if (is_array($url)) {
			$url = join('/', $url);
		}
		$url = str_replace('//', '/', $url);
		$query = parse_url($url);
		$url = $query['path'];
		$params = array();
		if (isset($query['query'])) {
			parse_str($query['query'], $params);
		}
		if ($url == '/') $url = 'index';
		if ($ext == null) {
			$ext = '';
		}
		$query = count($params) > 0 ? '?' . http_build_query($params) : '';
		//去掉url中末尾的扩展名,避免重复
		$url = preg_replace('/(\.' . $ext . ')$/', '', $url);
		return rtrim(self::pathUrl(), '/') . '/' . ltrim($url, '/') . $ext . (count($params) > 0 ? '?' . http_build_query($params) : '');
	}

	/**
	 * 获取Image全路径
	 * @param String $image
	 * @return string
	 */
	public static function getImage($image)
	{
		$image = explode('.', $image);
		$ext = '.' . array_pop($image);
		return self::getFullUrl('/static/images/' . join('.', $image), $ext);
	}

	/**
	 * 获取Css全路径
	 * @param String $css
	 * @return string
	 */
	public static function getCss($css)
	{
		return self::getFullUrl('/static/css/' . $css, '.css');
	}

	/**
	 * 获取JS全路径
	 * @param String $js
	 * @return string
	 */
	public static function getJS($js)
	{
		return self::getFullUrl('/static/js/' . $js, '.js');
	}

	/**
	 * 通过制定url路径,不局限于static/images目录返回全路径url
	 * @param $image
	 * @return string
	 */
	public static function getImageFullUrl($image)
	{
		return self::getSourceFullUrl($image);
	}

	/**
	 * 通过path直接返回全路径
	 *
	 * @param $url
	 * @return string
	 */
	public static function getSourceFullUrl($url)
	{
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		$url = preg_replace('/' . $ext . '$/', '', $url);
		return self::getFullUrl($url, $ext);
	}
}
