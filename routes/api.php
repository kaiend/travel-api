<?php

use Illuminate\Http\Request;

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

//发送验证码
Route::get('/sendCode', 'UserController@sendCode');
//注册用户
Route::post('/register', 'UserController@register');
//用户登录
Route::post('/login', 'UserController@login');
