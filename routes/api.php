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

// 区分一覧取得
Route::get('/informations/sections', 'App\Http\Controllers\InformationController@index_section')->name('imformation.index_section');

// 区分詳細取得
Route::get('/informations/sections/{id}', 'App\Http\Controllers\InformationController@show_section')->name('imformation.show_section');

// 区分投稿
Route::post('/informations/sections', 'App\Http\Controllers\InformationController@store_section')->name('imformation.store_section');

// 区分更新
Route::post('/informations/sections/{id}', 'App\Http\Controllers\InformationController@update_section')->name('imformation.update_section');

// 区分削除
Route::delete('/informations/sections/{id}', 'App\Http\Controllers\InformationController@destroy_section')->name('imformation.destroy_section');

// 登場人物一覧取得
Route::get('/informations/characters', 'App\Http\Controllers\InformationController@index_character')->name('imformation.index_character');

// 登場人物詳細取得
Route::get('/informations/characters/{id}', 'App\Http\Controllers\InformationController@show_character')->name('imformation.show_character');

// 登場人物投稿
Route::post('/informations/characters', 'App\Http\Controllers\InformationController@store_character')->name('imformation.store_character');

// 登場人物更新
Route::post('/informations/characters/{id}', 'App\Http\Controllers\InformationController@update_character')->name('imformation.update_character');

// 登場人物削除
Route::delete('/informations/characters/{id}', 'App\Http\Controllers\InformationController@destroy_character')->name('imformation.destroy_character');

// 持ち主一覧取得
Route::get('/informations/owners', 'App\Http\Controllers\InformationController@index_owner')->name('information.index_owner');

// 持ち主詳細取得
Route::get('/informations/owners/{id}', 'App\Http\Controllers\InformationController@show_owner')->name('imformation.show_owner');

// 持ち主登録
Route::post('/informations/owners', 'App\Http\Controllers\InformationController@store_owner')->name('information.store_owner');

// 持ち主更新
Route::post('/informations/owners/{id}', 'App\Http\Controllers\InformationController@update_owner')->name('information.update_owner');

// 持ち主削除
Route::delete('/informations/owners/{id}', 'App\Http\Controllers\InformationController@destroy_owner')->name('imformation.destroy_owner');

// 小道具一覧取得
Route::get('/props', 'App\Http\Controllers\PropController@index')->name('prop.index');

// 小道具一覧詳細込み取得
Route::get('/props_all', 'App\Http\Controllers\PropController@index_all')->name('prop.index_all');

// 小道具詳細取得
Route::get('/props/{id}', 'App\Http\Controllers\PropController@show')->name('prop.show');

// 小道具投稿
Route::post('/props', 'App\Http\Controllers\PropController@store')->name('prop.store');

// 小道具更新
Route::post('/props/{id}', 'App\Http\Controllers\PropController@update')->name('prop.update');

// 小道具更新
Route::post('/props_deep/{id}', 'App\Http\Controllers\PropController@update_deep')->name('prop.update_deep');

// 小道具削除
Route::delete('/props/{id}', 'App\Http\Controllers\PropController@destroy')->name('prop.destroy');

// 小道具複数削除
Route::delete('/props_many/{id}', 'App\Http\Controllers\PropController@destroy_many')->name('prop.destroy_many');

// 小道具一覧ダウンロード
Route::post('/props_list', 'App\Http\Controllers\PropController@down')->name('prop.down');

// 小道具メモ投稿
Route::post('/prop_comments', 'App\Http\Controllers\Prop_CommentController@store')->name('prop_comment.store');

// 小道具メモ更新
Route::post('/prop_comments/{id}', 'App\Http\Controllers\Prop_CommentController@update')->name('prop_comments.update');

// 小道具メモ削除
Route::delete('/prop_comments/{id}', 'App\Http\Controllers\Prop_CommentController@destroy')->name('prop_comment.destroy');

// 使用シーン一覧取得
Route::get('/scenes', 'App\Http\Controllers\SceneController@index')->name('scene.index');

// 使用シーン詳細取得
Route::get('/scenes/{id}', 'App\Http\Controllers\SceneController@show')->name('scene.show');

// 使用シーン投稿
Route::post('/scenes', 'App\Http\Controllers\SceneController@store')->name('scene.store');

// 使用シーン更新
Route::post('/scenes/{id}', 'App\Http\Controllers\SceneController@update')->name('scene.update');

// 使用シーン削除
Route::delete('/scenes/{id}', 'App\Http\Controllers\SceneController@destroy')->name('scene.destroy');

// 使用シーンメモ投稿
Route::post('/scene_comments', 'App\Http\Controllers\Scene_CommentController@store')->name('scene_comment.store');

// 使用シーンメモ更新
Route::post('/scene_comments/{id}', 'App\Http\Controllers\Scene_CommentController@update')->name('scene_comments.update');

// 使用シーンメモ削除
Route::delete('/scene_comments/{id}', 'App\Http\Controllers\Scene_CommentController@destroy')->name('scene_comment.destroy');