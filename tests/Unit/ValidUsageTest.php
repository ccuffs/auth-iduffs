<?php

$auth = new CCUFFS\Auth\AuthIdUFFS();

it('reports invalid login param', function() use ($auth) {
    $info = $auth->login([]);
})->throws(Exception::class);

it('reports no user key', function() use ($auth) {
    $info = $auth->login([
        'password' => 'something'
    ]);
})->throws(Exception::class);

it('reports empty user key', function() use ($auth) {
    $info = $auth->login([
        'user' => ''
    ]);
})->throws(Exception::class);

it('reports no password key', function() use ($auth) {
    $info = $auth->login([
        'user' => 'something'
    ]);
})->throws(Exception::class);
