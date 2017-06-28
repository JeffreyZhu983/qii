<?php
namespace Qii\Request;

use Qii\Base\Request;

final class Http extends Request
{

    /**
     * __construct
     *
     * @param string $request_uri
     * @param string $base_uri
     */
    public function __construct($request_uri = null, $base_uri = null)
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->method = $_SERVER['REQUEST_METHOD'];
        } else {
            if (!strncasecmp(PHP_SAPI, 'cli', 3)) {
                $this->method = 'Cli';
            } else {
                $this->method = 'Unknown';
            }
        }
        if (empty($request_uri)) {
            do {
                // #ifdef PHP_WIN32
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    /* check this first so IIS will catch */
                    if ($request_uri = $this->getServer('HTTP_X_REWRITE_URL')) {
                        break;
                    }

                    /* IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem) */
                    if ($rewrited = (boolean)$this->getServer('IIS_WasUrlRewritten')) {
                        $unencode = $this->getServer('UNENCODED_URL');
                        if ($unencode && is_string($unencode)) {
                            $request_uri = $unencode;
                        }
                        break;
                    }
                    // #endif
                }
                if ($request_uri = $this->getServer('PATH_INFO')) {
                    break;
                }

                if ($request_uri = $this->getServer('REQUEST_URI')) {
                    /* Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path */
                    if (strstr($request_uri, 'http') == $request_uri) {
                        $url_info = parse_url($request_uri);
                        if ($url_info && isset($url_info['path'])) {
                            $request_uri = $url_info['path'];
                        }
                    } else {
                        if ($pos = strstr($request_uri, '?')) {
                            $request_uri = substr($request_uri, 0, $pos - 1);
                        }
                    }
                    break;
                }

                if ($request_uri = $this->getServer('ORIG_PATH_INFO')) {
                    /* intended do nothing */
                    /*
                    if ($query = $this->getServer('QUERY_STRING')) {
                    }
                    */
                    break;
                }

            } while (0);
        }
        if ($request_uri && is_string($request_uri)) {
            $request_uri = str_replace('//', '/', $request_uri);
            $this->uri = $request_uri;

            // request_set_base_uri
            $this->_setbaseUri($base_uri, $request_uri);
        }
        $this->params = array();
        parent::__construct();
    }

    /**
     * getQuery
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getQuery($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_GET;
        } elseif (isset($_GET[$name])) {
            return $_GET[$name];
        }
        return $default;
    }

    /**
     * getRequest
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getRequest($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_REQUEST;
        } elseif (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        }
        return $default;
    }

    /**
     * getPost
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getPost($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_POST;
        } elseif (isset($_POST[$name])) {
            return $_POST[$name];
        }
        return $default;
    }

    /**
     * getCookie
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getCookie($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_COOKIE;
        } elseif (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return $default;
    }

    /**
     * getFiles
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getFiles($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_FILES;
        } elseif (isset($_FILES[$name])) {
            return $_FILES[$name];
        }
        return $default;
    }

    /**
     * get [params -> post -> get -> cookie -> server]
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } elseif (isset($_POST[$name])) {
            return $_POST[$name];
        } elseif (isset($_GET[$name])) {
            return $_GET[$name];
        } elseif (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        } elseif (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        return $default;
    }

    /**
     * isXmlHttpRequest
     *
     * @param void
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        $header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
        if (is_string($header) && strncasecmp('XMLHttpRequest', $header, 14) == 0) {
            return true;
        }
        return false;
    }

    /**
     * __clone
     *
     * @param void
     */
    private function __clone()
    {

    }

}