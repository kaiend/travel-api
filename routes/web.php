<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('foo', function () {
    return 'Hello Baby';
});

/**
 *
 *Other person do this
 */
//发送验证码
//Route::get('/api/sendCode', 'UserController@sendCode');
////注册用户
//Route::post('/api/register', 'UserController@register');
////用户登录
//Route::post('/api/login', 'UserController@login');
////验证码验证
//Route::post('/api/verifyCode', 'UserController@verifyCode');
////修改密码
//Route::post('/api/modifyPassword', 'UserController@modifyPassword');
////退出注销
//Route::post('/api/logout', 'UserController@logout');
////获取用户信息
//Route::get('/api/getUserInfo', 'UserController@getUserInfo');
////创建订单
//Route::post('/api/createOrder', 'OrderController@createOrder');
////订单列表
//Route::get('/api/orderList', 'OrderController@orderList');
////撤销订单
//Route::post('/api/undoOrder', 'OrderController@undoOrder');
////普通账户支付
//Route::post('/api/orderPay', 'OrderController@orderPay');
////获取微信openid
//Route::post('/api/getOpenid','UserController@getOpenid');
//
//
//Route::get('/api/test', 'ServiceController@carSeriesList');