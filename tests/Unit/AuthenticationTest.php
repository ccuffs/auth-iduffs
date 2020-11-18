<?php

$auth = new CCUFFS\Auth\AuthIdUFFS();

it('tests valid user authentication', function($user, $password) use ($auth) {
    $info = $auth->login([
        'user' => $user,
        'password' => $password
    ]);

    expect($info)->toHaveProperty('username');
    expect($info)->toHaveProperty('uid');
    expect($info)->toHaveProperty('email');
    expect($info)->toHaveProperty('pessoa_id');
    expect($info)->toHaveProperty('name');
    expect($info)->toHaveProperty('cpf');
    expect($info)->toHaveProperty('token_id');
    expect($info)->toHaveProperty('authenticated');

})->with([
    [getenv('AUTH_IDUFFS_TEST_USERNAME'), getenv('AUTH_IDUFFS_TEST_PASSOWRD')]
]);

it('tests invalid user authentication', function() use ($auth)  {
    $params = array(
        'user'     => 'naoexiste',
        'password' => 'naoexiste'
    );
    
    $info = $auth->login($params);

    expect($info)->toBeNull();
});
