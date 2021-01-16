<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/major/{major}', 'ScrapeController@getMajorInfo');
Route::get('/lecturer', 'ScrapeController@getAllLecturerSinta');
Route::get('/articles/{userID}', 'ScrapeController@getArticlesPerUser');
Route::get('/stats/{userID}', 'ScrapeController@getStatisticsPerUser');
