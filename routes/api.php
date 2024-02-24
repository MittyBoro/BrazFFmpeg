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

Route::middleware('replaceSrcForLocal')->group(function () {
  Route::post('/info', 'InfoController');
  Route::prefix('/create')->group(function () {
    Route::post('/images', 'CreateController@images');
    Route::post('/thumbnails', 'CreateController@thumbnails');
    Route::post('/trailer', 'CreateController@trailer');
    Route::post('/resize', 'CreateController@resize');
  });
  Route::post('/state/{id}', 'StateController@state');
  Route::post('/stop/{id}', 'StateController@stop');
});
