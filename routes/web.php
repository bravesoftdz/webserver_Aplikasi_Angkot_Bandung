<?php


Auth::routes();
Route::get('/', 'MapController@index');




Route::get('/trayek', 'MapController@trayek');
Route::get('/edit', 'MapController@edit')->middleware('auth');
Route::get('/input', 'MapController@input')->middleware('auth');
Route::get('/cekdb', 'MapController@cekdb');
Route::get('/check', 'MapController@check');

Route::post('/update', 'EditController@update');
Route::post('/update_points', 'EditController@update_points');

Route::post('/insert', 'InputController@insert');
Route::post('/insert_points', 'InputController@insert_points');

Route::get('/graph', 'MapController@graph');
Auth::routes();

Route::get('/home', 'HomeController@index');