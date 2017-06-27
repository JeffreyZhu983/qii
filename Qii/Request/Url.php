<?php
namespace Qii\Request;

/**
 * 返回URL处理方法
 */
class Url
{
    /**
     * self instance
     */
    private static $_instance;
    public $request;

    public function __construct($rewriteRule = 'Normal')
    {
        $allow = array('Normal', 'Middle', 'Short');
        if (!in_array($rewriteRule, $allow)) {
            throw new \Qii\Exceptions\Unsupport("链接模式错误，链接格式只能为 '<u><font color=\"green\">" . join("', '", $allow) . "</font></u>'，当前模式为 '<font color=\"red\">" . $rewriteRule . "</font>'", __LINE__);
        }
        $className = 'Qii\Request\Url\\' . $rewriteRule;
        $this->request = \Qii\Autoloader\Psr4::getInstance()->loadClass($className, $rewriteRule);
        return $this;
    }

    public static function getInstance()
    {
        $args = func_get_args();
        $arg = array_shift($args);
        if (self::$_instance == null) {
            self::$_instance = new self($arg);
        }
        return self::$_instance;
    }


    /**
     * 获取当前连接地址
     *
     */
    public static function getCurrentURL()
    {
        $rewriteRule = \Qii::getInstance()->appConfigure(\Qii\Consts\Config::APP_SITE_METHOD);
        return \Qii\Request\Url::getInstance($rewriteRule)->request->getCurrentURL();
    }

    /**
     * 获取网站路径的URL
     * @return string
     */
    public static function pathUrl()
    {
        $rewriteRule = \Qii::getInstance()->appConfigure(\Qii\Consts\Config::APP_SITE_METHOD);
        return \Qii\Request\Url::getInstance($rewriteRule)->request->getWebHost() . \Qii::getInstance()->request->url->getPath();
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
        $url = preg_replace('/' . $ext . '$/', '', $url);
        return rtrim(self::pathUrl(), '/') . '/' . ltrim($url, '/') . $ext . (count($params) > 0 ? '?' . http_build_query($params) : '');
    }

    /**
     * 返回url的全路径
     * @param string $url url格式为路径如：publish/edit.json?isAjax=1&ad_uuid=1912-1212-1212-1112-123
     */
    public static function getAbsluteUrl($url)
    {
        return self::getFullUrl($url, '');
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
    /**
     * 获取来源地址
     */
    public static function getRefererURL()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }
    /**
     * 来源地址是否是当前网站
     * @param string $currentURL 需要验证的网址，不传默认当前网址
     * @return bool
     */
    public static function refererFromCurrentSite($currentURL = null)
    {
		$referer = self::getRefererURL();
        if(!$currentURL) $currentURL = self::getCurrentURL();
		if(parse_url($referer, PHP_URL_HOST) != parse_url($currentURL, PHP_URL_HOST)){
			return false;
		}
        return true;
    }
    /**
     * 先看本方法中有没有静态方法可调用
     */
    public function __call($method, $args)
    {
        if(method_exists($this, $method))
        {
            return call_user_func_array(array(self, $method), $args);
        }
        return call_user_func_array(array($this->request, $method), $args);
    }

}