<?php
namespace Controller;

class index extends \Qii\Base\Controller
{
    public $enableDB = true;
    public function indexAction()
    {
        $data = array();
        $data['lists'][] = $this->db->getRow('SELECT * FROM ipAddress ORDER BY id DESC LIMIT 1');
        $data['lists'][] = $this->db->getRow('SELECT * FROM ipAddress ORDER BY id ASC LIMIT 1');
        $data['querySeconds'] = $this->db->querySeconds;
        
        return new \Qii\Base\Response(array('format' => 'json', 'body' => $data));
    }
    public function dispatchAction()
    {
        $this->dispatch('test', 'index');
    }
    
    public function forwardAction()
    {
        $this->setForward('test', 'index');
    }
}