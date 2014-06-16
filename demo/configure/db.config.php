<?php
return array('readOrWriteSeparation' => false,
					'driven' => 'mysql',
					'master' => array('host' => '127.0.0.1', 'user' => 'root', 'password' => '', 'db' => 'emlog'),
					'slave' => array(
								array('host' => 'localhost', 'user' => 'root', 'password' => '', 'db' => 'emlog'),
					), 
					'charset'=> 'UTF8'
);
?>