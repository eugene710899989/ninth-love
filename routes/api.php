<?php

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


Route::get('/wechat/auth', 'WeChatController@auth');
Route::any('/wechat', 'WeChatController@serve');


Route::middleware(['api.user_jwt'])->group(function () {
    Route::post("user/{user}/info", "UserController@init");
    Route::post("user/{user}/update", "UserController@update");
    Route::get("info/{user}", "UserController@info");
    Route::get("agree/{user}", "UserController@agree");
    Route::get("tag/list", "TagController@list");
    Route::post("upload", "CommonController@upload");
});
