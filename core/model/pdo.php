<?php
Qii::requireOnce(dirname(__FILE__) . DS . 'pdo.class.php');
class Model extends pdo_class
{
	public $version = '1.1.0';
}
?>