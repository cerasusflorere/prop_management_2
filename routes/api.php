<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 登場人物取得
Route::get('/informations/characters', 'App\Http\Controllers\InformationController@index_character')->name('imformation.index_character');

// 持ち主取得
Route::get('/informations/owners', 'App\Http\Controllers\InformationController@index_owner')->name('information.index_owner');

// 小道具一覧取得
Route::get('/props', 'App\Http\Controllers\PropController@index')->name('prop.index');

// 小道具詳細取得
Route::get('/props/{id}', 'App\Http\Controllers\PropController@show')->name('prop.show');

// 小道具投稿
Route::post('/props', 'App\Http\Controllers\PropController@store')->name('prop.store');

// 使用シーン一覧取得
Route::get('/scenes', 'App\Http\Controllers\SceneController@index')->name('scene.index');

// 使用シーン詳細取得
Route::get('/scenes/{id}', 'App\Http\Controllers\SceneController@index')->name('scene.index');

// 使用シーン投稿
Route::post('/scenes', 'App\Http\Controllers\SceneController@create')->name('scene.create');