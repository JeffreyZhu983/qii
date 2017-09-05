<?php
namespace controller;

class base extends \Qii\Base\Controller
{
    public $enableDB = true;
    public $enableView = true;
    public function indexAction()
    {
        parent::__construct();
    }
}