<?php
namespace Qii\Base;

class Response
{
    /**
     * Default body name
     */
    const DEFAULT_BODY = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_HTML = 'html';

    /**
     * Body content
     * @var array
     */
    protected $body = array();
    
    /**
     * data 
     * @param array $data
     */
    protected $data = array();
    /**
     * Array of headers. Each header is an array with keys 'name' and 'value'
     * @var array
     */
    protected $headers = array();

    /**
     * Determine to send the headers or not
     * @var unknown_type
     */
    protected $_sendHeader = false;
	
    
	public function __construct($data = array())
	{
        $this->format = isset($data['format']) ? isset($data['format']) : self::FORMAT_HTML;
		$this->data = $data;
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
        if (!isset($this->body[$key])) {
            $this->body[$key] = '';
        }
        $this->body[$key] .= (string) $body;
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
            if (array_key_exists($key, $this->body)) {
                unset($this->body[$key]);
            }
        } else {
            $this->body = array();
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
        $this->headers = array();
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
        return array_key_exists($key, $this->body) ? $this->body[$key] : null;
    }

    /**
     * Return array of headers; see {@link $headers} for format
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->headers;
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
        if (!isset($this->body[$key])) {
            $this->body[$key] = '';
        }
        $this->body[$key] = $body . $this->body[$key];
        return $this;
    }

    /**
     * Send the response, including all headers
     *
     * @return void
     */
    public function response()
    {
        if($this->data && isset($this->data['body']))
        {
            switch($this->data['format'])
            {
                case self::FORMAT_JSON:
                    $this->setHeader('Content-Type', 'text/json');
                    $this->sendHeaders();
                    echo json_encode($this->data['body'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                break;
                default:
					$body = $this->data['body'];
					if(is_array($this->data['body'])) {
						$body = '';
						if(isset($this->data['body']['render']) && $this->data['body']['render'] instanceof  \Qii\View\Intf)
						{
							$tplData = isset($this->data['body']['tplData']) ? $this->data['body']['tplData'] : [];
							$this->data['body']['render']->assign($tplData);
							$body = $this->data['body']['render']->fetch($this->data['body']['tpl']);
						}
					}
                    echo (IS_CLI ? (new \Qii\Response\Cli())->stdout($body) : $body);
                break;
            }
            return;
        }
        if ($this->_sendHeader == true) {
            $this->sendHeaders();
        }
        foreach ($this->body as $key => $body) {
            echo IS_CLI ? new \Qii\Response\Cli($body) : $body;
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
        $this->body[$key] = (string) $body;
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
            foreach ($this->headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->headers[$key]);
                }
            }
        }

        $this->headers[] = array(
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
        foreach ($this->headers as $header) {
            header(
                $header['name'] . ': ' . $header['value'],
                $header['replace']
            );
        }
        return $this;
    }
}