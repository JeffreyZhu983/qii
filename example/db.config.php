<?php
return array('readOrWriteSeparation' => true,//是否支持读写分离，如果需要支持读写分离，请手动配置slave中的数组
					'driven' => 'mysql',
					'master' => array('host' => '{dbhost}', 'user' => '{dbuser}', 'password' => '{dbpassword}', 'db' => '{dbname}'),
					'slave' => array(
								array('host' => '{dbhost}', 'user' => '{dbuser}', 'password' => '{dbpassword}', 'db' => '{dbname}'),
					), 
					'charset'=> '{charset}'
);
?>