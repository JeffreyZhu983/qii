<?php
return [
    //'index:index' => 'dirs:index',
    'home:index' => 'index:index',
    'main:*' => 'index:index',
    'api:*' => 'api\{1}:*'
];