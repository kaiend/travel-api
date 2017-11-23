
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
        \Dingo\Api\Facade\Route::get('/account' ,'HotelController@getAccount');
        //子账户列表
        \Dingo\Api\Facade\Route::get('/list' ,'HotelController@getList');
        //添加子账户
        \Dingo\Api\Facade\Route::post('/child' ,'HotelController@addChild');
        //禁用子账户
        \Dingo\Api\Facade\Route::patch('/disable/{id}' ,'HotelController@stopChild');
        //修改子账户密码
        \Dingo\Api\Facade\Route::post('/reset/{id}' ,'HotelController@restPassword');
    });
    //认证token
    $api->get('/authorization','HotelController@authToken');

    //APP用车接口
    $api->group(['prefix' => 'car'] , function(){
        //APP选车型
        \Dingo\Api\Facade\Route::get('/list' ,'CarController@getSeries');
        //App车系详情
        \Dingo\Api\Facade\Route::get('/detail/{pid}' ,'CarController@getCars');
    });
});





