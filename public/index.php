<?php
require_once('../Qii/Application.php');
$app = \Qii\Application::getInstance();
$app->setConfig('site', ['env' => 'product']);
$app->run();