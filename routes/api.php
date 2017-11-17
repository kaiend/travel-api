
<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/17
 * Time: 16:44
 */
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers',
    // each route have a limit of 100 of 1 minutes
    //'limit' => 100, 'expires' => 1
], function ($api) {

    //APP登录
    Route::post('/login','HotelController@login');
    //APP发送验证码
    Route::get('/sendCode','UserController@sendCode');
    //APP验证密码
    Route::post('/verifyCode', 'UserController@verifyCode');
    //APP修改密码
    Route::post('/modifyPassword','HotelController@editPassword');

    $api->get('/test','HotelController@test');
});