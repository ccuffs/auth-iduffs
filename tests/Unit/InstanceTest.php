<?php

it('tests construtor with no params', function() {
    $auth = new CCUFFS\Auth\AuthIdUFFS();
    expect($auth)->toBeInstanceOf(CCUFFS\Auth\AuthIdUFFS::class);
});
