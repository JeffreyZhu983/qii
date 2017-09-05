<?php
namespace Qii\Response;

class Cli extends \Qii\Base\Response
{
    public function __construct($body = null)
    {
        $this->body = $body;
    }
    /**
     * Magic __toString functionality
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body;
    }
    /**
     * 在windows cmd 情况下的中文输出乱码问题
     * @param string $string
     * @return bool|int
     */
    public function stdout($string)
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $string = iconv('utf-8', 'gbk', $string);
        fwrite(\STDOUT, $string);
    }
}