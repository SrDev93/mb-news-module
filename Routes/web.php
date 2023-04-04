<?php

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
use \Illuminate\Support\Facades\Route;

Route::prefix('')->middleware('auth')->group(function() {
    Route::resource('NewsCategory', 'NewsCategoryController');
    Route::post('NewsCategory-sort', 'NewsCategoryController@sort_item')->name('NewsCategory-sort');

    Route::resource('News', 'NewsController');
});
