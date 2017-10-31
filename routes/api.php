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
//获取用户信息
Route::get('/getUserInfo', 'UserController@getUserInfo');

//出行卡数据添加
Route::put('/travelCard', 'UserController@travelCard');
//上传出行卡
Route::post('/updateTravelCard', 'UserController@updateTravelCard');

//微信订单
Route::any('/WxOrder', 'PayController@WxOrder');
//微信支付异步回调
Route::any('/WxNotify', 'PayController@WxNotify');
//账户充值
Route::any('/topUp', 'PayController@topUp');

//创建订单
Route::post('/createOrder', 'OrderController@createOrder');
//订单列表
Route::get('/orderList', 'OrderController@orderList');
//普通账户支付
Route::post('/orderPay', 'OrderController@orderPay');
//获取微信openid
Route::post('/getOpenid','UserController@getOpenid');


Route::get('/test', 'ServiceController@carSeriesList');
