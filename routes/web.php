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

$router->post('uploadcsv', [
    'as' => 'uploadcsv',
    'uses' => 'AdminController@uploadcsv',
    'middleware' => 'auth',
]);

$router->post('uploadzip', [
    'as' => 'uploadzip',
    'uses' => 'AdminController@uploadzip'
]);

$router->post('findimages', [
    'as' => 'findimages',
    'uses' => 'SearchController@findimages'
]);

$router->post('login', [
    'as' => 'login',
    'uses' => 'UserController@login'
]);

$router->get('/', function () use ($router) {
    return $router->app->version();
});
