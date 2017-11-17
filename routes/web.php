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
Route::get('/api/sendCode', 'UserController@sendCode');
//注册用户
Route::post('/api/register', 'UserController@register');
//用户登录
Route::post('/api/login', 'UserController@login');
//验证码验证
Route::post('/api/verifyCode', 'UserController@verifyCode');
//修改密码
Route::post('/api/modifyPassword', 'UserController@modifyPassword');
//退出注销
Route::post('/api/logout', 'UserController@logout');
//获取用户信息
Route::get('/api/getUserInfo', 'UserController@getUserInfo');