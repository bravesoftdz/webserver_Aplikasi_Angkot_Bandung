<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/check', 'ApiControl@check');

Route::get('/trips/get_jalur_terdekat', 'ApiController@get_jalur_terdekat');
Route::get('/get_jalur_terdekat_cepat_new', 'ApiController@get_jalur_terdekat_cepat_new');

Route::get('/trips/get_jalur_terdekat_baru', 'ApiController@get_jalur_terdekat_baru');

Route::get('/get_last_shapes_id', 'ApiControl@get_last_shapes_id');
Route::get('/import_placeName', 'ApiController@import_placeName'); //untuk import database

Route::get('/trips/get_jalur', 'ApiController@get_jalur');
Route::get('/test', 'ApiController@test');
Route::get('/jalurTerpendek', 'ApiController@jalurTerpendek');
Route::get('/djikstra', 'ApiController@djikstra');
Route::get('/djikstra_cepat', 'ApiController@djikstra_cepat_new');

Route::get('/getlocinfo', 'ApiController@getLocInfo2');

Route::get('/get_fastest_route', 'ApiController@get_fastest_route');
Route::get('/get_placeinfo', 'ApiController@get_placeinfo');
Route::get('/get_angkot_info', 'ApiController@get_angkot_info');

Route::get('/object', 'ApiController@object');
Route::get('/get_koordinat', 'ApiController@get_koordinat');

Route::get('/get_fastest_route3', 'ApiController@get_fastest_route3');

Route::get('/cetak_jalur', 'ApiController@cetak_jalur');
Route::get('/cetak_jalur2', 'ApiController@cetak_jalur2');
Route::get('/cetak_jalur3', 'ApiController@cetak_jalur3');
Route::get('/cetak_jalur3_api', 'ApiController@cetak_jalur3_api');

Route::get('/jajal', 'ApiController@jajal');

Route::get('/efisiensi', 'ApiController@efisiensi');
Route::get('/efisiensi2', 'ApiController@efisiensi2');

Route::get('/get_position', 'ApiController@get_position');

Route::get('/trips/get_walking_route', 'ApiController@get_walking_route');
Route::get('/trips/get_car_route', 'ApiController@get_car_route');

Route::get('/trips/get_trayek', 'ApiController@get_trayek');

Route::get('/trips/get_trayek_akbar', 'ApiController@get_trayek_akbar');
Route::get('/trips/get_trayek_akbar3', 'ApiController@get_trayek_akbar3');

Route::get('/trips/export', 'ApiController@export');


Route::get('/trips/get_trayek_akbar2', 'ApiController@get_trayek_akbar2');

Route::get('/trips/get_angkot', 'ApiController@get_angkot');



Route::get('/cetak_jalur', 'ApiControl@cetak_jalur');
