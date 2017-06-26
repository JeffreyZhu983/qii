<?php
require_once('../Qii/Qii.php');
$app = \Qii::getInstance();
$app->setConfig('site', ['env' => 'product', ['siteName' => 'Qii'] ]);
$app->run();
new s();