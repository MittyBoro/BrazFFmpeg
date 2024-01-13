<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('{id}')->group(function () {
  Route::get('/state', 'IndexController@state');
  Route::post('/delete', 'IndexController@delete');

  Route::post('/info', 'IndexController@info');
  Route::post('/images', 'IndexController@images');
  Route::post('/thumbnails', 'IndexController@thumbnails');
  Route::post('/trailer', 'IndexController@trailer');
  Route::post('/resize', 'IndexController@resize');
});
