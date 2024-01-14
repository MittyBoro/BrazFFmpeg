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

Route::get('/state/{id}', 'IndexController@state');
Route::get('/delete/{id}', 'IndexController@delete');

Route::get('/info', 'IndexController@info');
Route::get('/images', 'IndexController@images');
Route::get('/thumbnails', 'IndexController@thumbnails');
Route::get('/trailer', 'IndexController@trailer');
