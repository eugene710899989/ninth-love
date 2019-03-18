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
    //用户信息
    Route::get("user/info", "UserController@info");
    //初始化用户信息
    Route::post("user/info", "UserController@init");
    //更新用户信息
    Route::post("user/update", "UserController@update");
    //标签列表
    Route::get("tag/list", "TagController@list");
    //用户约会信息
    Route::get("user/date_list", "UserController@dateList");
    //邀请用户
    Route::post("user/{user}/invite", "UserController@invite");
    //邀请列表
    Route::get("user/{user}/invites", "UserController@invites");
    //约会列表
    Route::get("user/{user}/invites", "UserController@invites");
    //同意邀请
    Route::get("agree/{user}", "UserController@agree");
    //文件上传
    Route::post("upload", "CommonController@upload");
    //推荐用户列表
    Route::get("recommend/list", "RecommendController@list");

    Route::get("schools/list", "CommonController@schools");
    //专业列表
    Route::get("majors/list", "CommonController@majors");

    //商家列表
    Route::get("seller/list", "SellerController@list");
    Route::get("seller/{seller}/productions", "SellerController@productions");

    //订单列表
    Route::get("order/list", "OrderController@list");
    Route::post("order/{seller}/create", "OrderController@create");
    Route::post("order/callback", "OrderController@callback");

    //恋爱吧
    Route::get("post/list", "PostController@list");
    Route::get("post/{post}", "PostController@detail");
    Route::post("post/{post}/comment", "PostController@comment");
    Route::post("post/create", "PostController@create");
    Route::post("post/{post}/delete", "PostController@delete");
    Route::post("post/{post}/zan", "PostController@like");
    Route::post("post/{post}/dislike", "PostController@dislike");


});
