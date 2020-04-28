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

$router->get('/curencyExchange', 'BotController@curencyExchange');
$router->post('/signUp', 'BotController@signUp');
$router->post('/login', 'BotController@login');

$router->group(['middleware' => ['auth']], function() use ($router) {
    $router->put('/deposit', 'BotController@deposit');
    $router->put('/withdraw', 'BotController@withdraw');
    $router->get('/balance', 'BotController@balance');
});
