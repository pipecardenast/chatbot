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
    // return $router->app->version();
    echo '<h1>Hello</h1>';
});

$router->group(['middleware' => ['type']], function() use ($router) {
    $router->get('/exchange', 'BotController@currencyExchange');
    $router->post('/signup', 'BotController@signup');
    $router->post('/login', 'BotController@login');
});

$router->group(['middleware' => ['auth', 'type']], function() use ($router) {
    $router->put('/setcurrency', 'BotController@setCurrencyAccount');
    $router->put('/deposit', 'BotController@deposit');
    $router->put('/withdraw', 'BotController@withdraw');
    $router->get('/balance', 'BotController@balance');
});
