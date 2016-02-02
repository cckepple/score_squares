<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Routes to Handle Login & Registration
Route::get('/', 'Auth\AuthController@getLogin');
Route::post('/login' , 'Auth\AuthController@postLogin');
Route::get('/logout', 'Auth\AuthController@getLogout');
Route::get('/register', 'Auth\AuthController@getRegister');
Route::post('/register', 'Auth\AuthController@postRegister');

// Route::get('/home', 'HomeController@getHome');
Route::resource('/pool', 'PoolController');


Route::get('/api/pool/{id}/squares', 'PoolController@getPoolSquares');
Route::get('/api/pool/{id}/players', 'PoolController@getPoolPlayers');

Route::get('/api/square/{id}/purchase', 'PoolController@purchaseSquare');

Route::post('/api/pool/player-paid', 'PoolController@playerPaid');
Route::post('/api/pool/remove-player-pay', 'PoolController@removePlayerPaid');

