<?php
namespace controller;

class index extends \Qii\Base\Controller
{
    public $enableDB = true;
    public $enableView = true;
    public function indexAction()
    {
        $html = [];
        $html[] = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>示例程序</title><style>ul{list-style:none;}</style></head><body>';
        $html[] = '<ul>示例程序';
        $html[] = '<li>><a href="'. _link('dirs.html') .'">文件管理</a></li>';
        $html[] = '<li>><a href="'. _link('database/creator.html') .'">数据表管理</a></li>';
        $html[] = '<li>><a href="'. _link('index/dispatch.html') .'">Dispatch</a></li>';
        $html[] = '<li>><a href="'. _link('index/forward.html') .'">Forward</a></li>';
        $html[] = '<li>><a href="'. _link('index/display.html') .'">使用指定目录中的模板</a></li>';
        $html[] = '</ul>';
        $html[] = '</body></html>';
        return $this->setResponse(new \Qii\Base\Response(array('format' => 'html', 'body' => join("\n", $html))));
        return;
        $data = array();
        $data['lists'][] = $this->db->getRow('SELECT * FROM ipAddress ORDER BY id DESC LIMIT 1');
        $data['lists'][] = $this->db->getRow('SELECT * FROM ipAddress ORDER BY id ASC LIMIT 1');
        $data['querySeconds'] = $this->db->querySeconds;
        
        return new \Qii\Base\Response(array('format' => 'json', 'body' => $data));
    }
    
    public function dispatchAction()
    {
        echo "<p>Dispatch start ". __CLASS__ ."</p>";
        $this->dispatch('test', 'index');
        echo "<p>Dispatch end ". __CLASS__ ."</p>";
    }
    
    public function forwardAction()
    {
        echo "<p>This is start section " . __CLASS__ . " Forward</p>";
        $this->setForward('test', 'index');
        echo "<p>This is end section " . __CLASS__ . " Forward</p>";
    }

    public function displayAction()
    {
        //可以从这里设置加载模板的路径
        $this->view->setTemplateDir(__DIR__ . "/view/");
        echo $this->view->fetch('index.tpl');
    }
}