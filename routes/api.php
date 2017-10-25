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
//验证码验证
Route::post('/verifyCode', 'UserController@verifyCode');
//修改密码
Route::post('/modifyPassword', 'UserController@modifyPassword');
//退出注销
Route::post('/logout', 'UserController@logout');

//出行卡数据添加
Route::post('/travelCard', 'UserController@travelCard');
//上传出行卡
Route::post('/updateTravelCard', 'UserController@updateTravelCard');

//微信订单
Route::any('/WxOrder', 'PayController@WxOrder');
//微信支付异步回调
Route::any('/WxNotify', 'PayController@WxNotify');




Route::get('/test', 'ServiceController@carSeriesList');
