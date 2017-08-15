<?php 
 return array (
  'readOrWriteSeparation' => '0',
  'driver' => 'pdo',
  'debug' => '1',
  'use_db_driver' => 'mysql',
  'master' => 
  array (
    'db' => 'wecv',
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'A119328118a',
  ),
  'slave' => 
  array (
    0 => 
    array (
      'db' => 'istudy',
      'host' => '127.0.0.1',
      'user' => 'wecv',
      'password' => 'A119328118a',
    ),
    1 => 
    array (
      'db' => 'istudy',
      'host' => '127.0.0.1',
      'user' => 'wecv',
      'password' => 'A119328118a',
    ),
  ),
)
?>