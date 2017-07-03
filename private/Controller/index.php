<?php
namespace Controller;

class index extends \Qii\Base\Controller
{
    public $enableDB = true;
    public $enableView = true;
    public function indexAction()
    {
        return $this->setResponse(new \Qii\Base\Response(array('format' => 'html', 'body' => 'This is html')));
        return;
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

    public function displayAction()
    {
        //可以从这里设置加载模板的路径
        $this->view->setTemplateDir(__DIR__ . "/view/");
        echo $this->view->fetch('index.tpl');
    }
}