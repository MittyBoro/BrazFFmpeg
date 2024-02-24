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
  Route::prefix('/tasks/create')->group(function () {
    Route::post('/images', 'CreateController@images');
    Route::post('/thumbnails', 'CreateController@thumbnails');
    Route::post('/trailer', 'CreateController@trailer');
    Route::post('/resize', 'CreateController@resize');
  });

  Route::get('/tasks/{id}', 'TaskController@status');
  Route::post('/tasks/{id}/stop', 'TaskController@stop');

  Route::post('/specifications', 'InfoController');
});
