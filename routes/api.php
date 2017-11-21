
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
    $api->post('/login','HotelController@login');
    //APP发送验证码
    $api->get('/message','UserController@sendCode');
    //APP验证密码
    $api->post('/verification', 'UserController@verifyCode');
    //APP修改密码
    $api->post('/password','HotelController@editPassword');
    //APP测试接口
    $api->get('/test','HotelController@test');

    //APP订单接口
    $api->group(['prefix' => 'order'] , function(){
        \Dingo\Api\Facade\Route::get('/list' ,'OrderController@getList');
        \Dingo\Api\Facade\Route::get('/cancel/{id}' ,'OrderController@cancelOrder');
    });

    //APP个人中心
    $api->group(['prefix' => 'user'] , function(){
        \Dingo\Api\Facade\Route::get('/account' ,'UserController@getAccount');
        \Dingo\Api\Facade\Route::post('/child' ,'UserController@addChild');
    });

});

