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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

/**
 * The new API
 */
//APP登录
Route::post('/V1/login','HotelController@login');
//APP发送验证码
Route::get('/V1/sendCode','UserController@sendCode');
//APP验证密码
Route::post('/V1/verifyCode', 'UserController@verifyCode');
//APP修改密码
Route::post('/V1/modifyPassword','HotelController@editPassword');
Route::get('/V1/test','HotelController@test');


