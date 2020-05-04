<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => ['type'], 'prefix' => 'api/v1'], function() use ($router) {
    $router->get('/users/exchange', 'UsersController@currencyExchange');
    $router->post('/users/signup', 'UsersController@signup');
    $router->post('/auth/login', 'AuthController@login');
});

$router->group(['middleware' => ['jwt.auth', 'type'], 'prefix' => 'api/v1'], function() use ($router) {
    $router->post('/auth/refresh', 'AuthController@refreshToken');
    $router->get('/auth/logout', 'AuthController@logout');
    $router->put('/accounts/currency', 'AccountsController@setCurrency');
    $router->post('/transactions/deposit', 'TransactionsController@deposit');
    $router->post('/transactions/withdraw', 'TransactionsController@withdraw');
    $router->get('/transactions/balance', 'TransactionsController@getBalance');
});
