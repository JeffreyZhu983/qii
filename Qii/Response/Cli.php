<?php
namespace Qii\Response;

class Cli extends \Qii\Base\Response
{
    /**
     * Magic __toString functionality
     *
     * @return string
     */
    public function __toString()
    {
        $this->stdout($this->_body);
    }
    /**
     * 在windows cmd 情况下的中文输出乱码问题
     * @param string $string
     * @return bool|int
     */
    public function stdout($string)
    {
        $string = iconv('utf-8', 'gbk', $string);
        return fwrite(\STDOUT, $string);
    }
}