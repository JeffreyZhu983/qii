<?php
namespace Qii\Response;

class Response
{
    /**
     * Default body name
     */
    const DEFAULT_BODY = 'html';

    /**
     * Body content
     * @var array
     */
    protected $_body = array();

    /**
     * Array of headers. Each header is an array with keys 'name' and 'value'
     * @var array
     */
    protected $_headers = array();

    /**
     * Determine to send the headers or not
     * @var unknown_type
     */
    protected $_sendHeader = false;
	public function __construct()
	{
		
	}

    /**
     * Append content to the body content
     *
     * @param string $content
     * @param string $key
     * @return Qii_Response_Abstract
     */
    public function appendBody($body, $key = NULL)
    {
        if (!strlen($key)) {
            $key = self::DEFAULT_BODY;
        }
        if (!isset($this->_body[$key])) {
            $this->_body[$key] = '';
        }
        $this->_body[$key] .= (string) $body;
        return $this;
    }

    /**
     * Clear the entire body
     *
     * @param string $key
     * @return boolean
     */
    public function clearBody($key = NULL)
    {
        if (strlen($key)) {
            if (array_key_exists($key, $this->_body)) {
                unset($this->_body[$key]);
            }
        } else {
            $this->_body = array();
        }
        return true;
    }

    /**
     * Clear headers
     *
     * @return Qii\Response\Abstract
     */
    public function clearHeaders()
    {
        $this->_headers = array();
        return $this;
    }

    /**
     * Return the body content
     *
     * @param string $key
     * @return string
     */
    public function getBody($key = NULL)
    {
        if (!strlen($key)) {
            $key = self::DEFAULT_BODY;
        }
        return array_key_exists($key, $this->_body) ? $this->_body[$key] : null;
    }

    /**
     * Return array of headers; see {@link $_headers} for format
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->_headers;
    }

    /**
     * Prepend content the body
     *
     * @param string $body
     * @param string $key
     * @return Qii_Response_Abstract
     */
    public function prependBody($body, $key = null)
    {
        if (!strlen($key)) {
            $key = self::DEFAULT_BODY;
        }
        if (!isset($this->_body[$key])) {
            $this->_body[$key] = '';
        }
        $this->_body[$key] = $body . $this->_body[$key];
        return $this;
    }

    /**
     * Send the response, including all headers
     *
     * @return void
     */
    public function response()
    {
        if ($this->_sendHeader == true) {
            $this->sendHeaders();
        }
        foreach ($this->_body as $key => $body) {
            echo $body;
        }
    }

    public function setAllHeaders()
    {
        return false;
    }
    /**
     * Set body content
     *
     * @param string $body
     * @param string $key
     * @return Qii_Response_Abstract
     */
    public function setBody($body, $key = NULL)
    {
        if (!strlen($key)) {
            $key = self::DEFAULT_BODY;
        }
        $this->_body[$key] = (string) $body;
        return $this;
    }

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return Qii_Response_Abstract
     */
    public function setHeader($name, $value, $replace = false)
    {
        $name  = $this->_normalizeHeader($name);
        $value = (string) $value;

        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }

        $this->_headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        );

        return $this;
    }

    /**
     * Set redirect URL
     *
     * Sets Location header. Forces replacement of any prior redirects.
     *
     * @param string $url
     * @return Qii_Response_Abstract
     */
    public function setRedirect($url)
    {
        $this->setHeader('Location', $url, true);
        return $this;
    }

    /**
     * Magic __toString functionality
     *
     * Returns response value as string
     * using output buffering.
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this->response();
        return ob_get_clean();
    }

    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function _normalizeHeader($name)
    {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }

    /**
     * Send all headers
     *
     * Sends any headers specified.
     * If an {@link setHttpResponseCode() HTTP response code}
     * has been specified, it is sent with the first header.
     *
     * @return Qii_Response_Abstract
     */
    protected function sendHeaders()
    {
        foreach ($this->_headers as $header) {
            header(
                $header['name'] . ': ' . $header['value'],
                $header['replace']
            );
        }
        return $this;
    }

    public function render()
    {
        
    }
}