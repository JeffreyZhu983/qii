<?php
Qii::requireOnce(dirname(__FILE__) . DS . 'mysqli.class.php');
class Model extends mysqli_class
{
	public $version = '1.1.0';
}
?>