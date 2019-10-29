<?php

// TODO: colocar testes unitários lindos aqui

require_once(dirname(__FILE__) . '/../src/AuthIdUFFS.php');
require_once(dirname(__FILE__) . '/../vendor/autoload.php');

$params = array(
    'user'     => '',
    'password' => ''
);

$auth = new CCUFFS\Auth\AuthIdUFFS();

$info = $auth->login($params);
var_dump($info);