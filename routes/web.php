<?php



Route::get('/', 'MapController@index');




Route::get('/trayek', 'MapController@trayek');
Route::get('/edit', 'MapController@edit');
Route::get('/input', 'MapController@input');
Route::get('/cekdb', 'MapController@cekdb');
Route::get('/check', 'MapController@check');



Route::get('/graph', 'MapController@graph');