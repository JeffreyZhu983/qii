<?php
Qii::requireOnce(dirname(__FILE__) . DS . 'mysql.class.php');
class Model extends mysql_class
{
	public $version = '1.1.0';
}
?>