<?php
namespace Controller;

class index extends \Qii\Base\Controller
{
    public $enableModel = true;
    public function indexAction()
    {
        echo __CLASS__;
        print_r($this->db->getRow('SELECT * FROM ipAddress ORDER BY id DESC LIMIT 1'));
    }
}