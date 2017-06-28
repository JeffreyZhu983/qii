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
        return $this->_body;
    }
}