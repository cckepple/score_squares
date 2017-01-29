<?php

//Routes to Handle Login & Registration
Route::get('/', 'Auth\AuthController@getLogin');
Route::post('/login' , 'Auth\AuthController@postLogin');
Route::get('/logout', 'Auth\AuthController@getLogout');
Route::get('/register', 'Auth\AuthController@getRegister');
Route::post('/register', 'Auth\AuthController@postRegister');

// Route::get('/home', 'HomeController@getHome');
Route::resource('/pool', 'PoolController');
Route::post('/pool/join', 'PoolController@join');


Route::get('/api/pool/{id}/squares', 'PoolController@getPoolSquares');
Route::get('/api/pool/{id}/players', 'PoolController@getPoolPlayers');

Route::get('/api/square/{id}/purchase', 'PoolController@purchaseSquare');

Route::post('/api/pool/player-paid', 'PoolController@playerPaid');
Route::post('/api/pool/remove-player-pay', 'PoolController@removePlayerPaid');
Route::post('/api/pool/remove-player-claim', 'PoolController@removePlayerClaim');


//first ver. score game
Route::get('/api/game/score-game/{quarter}/{gameId}', 'PoolController@scoreGameShow');
Route::post('/api/game/score-game/{quarter}/{gameId}', 'PoolController@scoreGamePost');
Route::get('/api/pool/{id}/set-scores', 'PoolController@testSetScore');

