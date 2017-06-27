<?php
namespace Qii\Response;

class Cli extends Base
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