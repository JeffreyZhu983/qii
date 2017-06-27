<?php
require_once('../Qii/Qii.php');
$app = \Qii::getInstance();
$app->setWorkspace('../private')
->setCachePath('tmp')
->setAppConfigure('configure/app.ini')
->setUseNamespace('Bootstrap', false)
->setLoger('Plugins\loger')
->setDB('configure/db.ini')
->setRouter('configure/router.config.php')
->setBootstrap()
->run();