<?php



Route::get('/', 'MapController@index');




Route::get('/trayek', 'MapController@trayek');
Route::get('/edit', 'MapController@edit');
Route::get('/input', 'MapController@input');
Route::get('/cekdb', 'MapController@cekdb');
Route::get('/check', 'MapController@check');

Route::post('/update', 'EditController@update');
Route::post('/update_points', 'EditController@update_points');


Route::get('/graph', 'MapController@graph');