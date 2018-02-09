
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
    //APP快捷登录
    $api ->post('/sign','HotelController@sign');
    //APP发送验证码
    $api->get('/message','UserController@sendCode');
    //APP验证密码
    $api->post('/verification', 'UserController@verifyCode');
    //APP修改密码
    $api->post('/password','HotelController@editPassword');
    //APP测试接口
    $api->get('/test','HotelController@test');
    //APP退出登录接口
    $api->get('/logout','HotelController@destroy');
    //APP选飞机或者车站的地址
    $api->get('/travel','HotelController@getTravel');
    //App首页服务类型接口
    $api->get('/index','HotelController@getServer');
    //App版本更新
    $api->get('/version','VersionController@getVersion');
    //APP投诉建议
    $api->post('/suggest','OrderController@getSuggest');
    //APP订单接口
    $api->group(['prefix' => 'order'] , function(){
        //APP订单列表(我的订单)
        \Dingo\Api\Facade\Route::get('/list' ,'OrderController@getList');
        //APP订单列表(酒店订单)
        \Dingo\Api\Facade\Route::get('/hotel/list' ,'OrderController@getHotelList');
        //APP取消订单
        \Dingo\Api\Facade\Route::get('/cancel/{id}' ,'OrderController@cancelOrder');
        //APP订单详情
        \Dingo\Api\Facade\Route::get('/detail/{id}' ,'OrderController@getDetail');
        //订单搜索
        \Dingo\Api\Facade\Route::post('/search' ,'OrderController@searchOrder');
        //APP首页---特殊路线
        \Dingo\Api\Facade\Route::get('/special/list' ,'OrderController@showList');
        //APP首页---特殊路线下单
        \Dingo\Api\Facade\Route::post('/special' ,'OrderController@sendSpecial');
        //APP按时包车---套餐
        \Dingo\Api\Facade\Route::get('/chartered' ,'OrderController@getPackage');
        //APP按时包车---下单
        \Dingo\Api\Facade\Route::post('/chartered' ,'OrderController@sendPackage');
        //App接送机--下单接口
        \Dingo\Api\Facade\Route::post('/flight' ,'OrderController@getFlight');
        //App接站--下单接口
        \Dingo\Api\Facade\Route::post('/train' ,'OrderController@getTrain');
        //APP追加订单
        \Dingo\Api\Facade\Route::post('/extra' ,'OrderController@makeExtra');
        //APP追加订单详情
//        \Dingo\Api\Facade\Route::get('/extra/detail/{id}' ,'OrderController@getExtraDetail');
        //APP订单审核
        \Dingo\Api\Facade\Route::post('/check' ,'OrderController@makeCheck');


    });

    //APP个人中心
    $api->group(['prefix' => 'user'] , function(){
        \Dingo\Api\Facade\Route::get('/account' ,'HotelController@getAccount');
        //子账户列表
        \Dingo\Api\Facade\Route::get('/list' ,'HotelController@getList');
        //子账户--上传照片的接口
        \Dingo\Api\Facade\Route::post('/avatar' ,'HotelController@uploadPhoto');
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
        //APP选车型(new)
        \Dingo\Api\Facade\Route::get('/lists' ,'CarController@getSerie');

    });

    //APP推送接口
    $api->group(['prefix' => 'push'] , function(){
        //状态变化推送接口
        \Dingo\Api\Facade\Route::get('/order/status' ,'PushController@pushStatus');
        //状态变化推送接口
        \Dingo\Api\Facade\Route::get('/ding' ,'PushController@makeDing');

    });
    //APP账户统计
    $api->group(['prefix' => 'account'] , function(){
        //账户统计接口
        \Dingo\Api\Facade\Route::get('/index' ,'HotelController@getAccount');

    });
    $api->group(['prefix' => 'finance'] , function(){
        //财务管理接口
        \Dingo\Api\Facade\Route::get('/index' ,'HotelController@getFinancial');
        //财务管理筛选接口
        \Dingo\Api\Facade\Route::get('/filter' ,'HotelController@getFilter');
    });

    //小程序
    $api->group(['prefix'=>'wechat'],function(){
        \Dingo\Api\Facade\Route::get('/distance' ,'Wechat\UserController@getDistance');
        //获取验证码
        \Dingo\Api\Facade\Route::get('/code' ,'Wechat\UserController@sendCode');
        //验证验证码
        \Dingo\Api\Facade\Route::post('/verification' ,'Wechat\UserController@verifyCode');
        //登陆
        \Dingo\Api\Facade\Route::post('/login' ,'Wechat\UserController@login');
        //注册
        \Dingo\Api\Facade\Route::post('/register' ,'Wechat\UserController@register');
        //下订单
        \Dingo\Api\Facade\Route::post('/createOrder' ,'Wechat\OrderController@createOrder');
        //订单列表
        \Dingo\Api\Facade\Route::get('/orderList', 'Wechat\OrderController@orderList');
        //注销
        \Dingo\Api\Facade\Route::post('/logout' ,'Wechat\UserController@logout');
        //获取用户信息
        \Dingo\Api\Facade\Route::get('/getUserInfo', 'Wechat\UserController@getUserInfo');
        //获取小程序openid
        \Dingo\Api\Facade\Route::get('/getOpenId', 'Wechat\UserController@getOpenId');
        //出行卡数据添加
        \Dingo\Api\Facade\Route::put('/travelCard', 'Wechat\UserController@travelCard');
        //上传出行卡
        \Dingo\Api\Facade\Route::post('/updateTravelCard', 'Wechat\UserController@updateTravelCard');
        //出行卡审核状态
        \Dingo\Api\Facade\Route::get('/cardAudit', 'Wechat\ServiceController@getAudit');
        //改变状态
        \Dingo\Api\Facade\Route::get('/status', 'Wechat\ServiceController@changeStatus');
        //账户充值
        \Dingo\Api\Facade\Route::any('/topUp', 'Wechat\PayController@topUp');
        //账户支付
        \Dingo\Api\Facade\Route::any('/orderPay', 'Wechat\OrderController@orderPay');
        //根据类型找到车系
        \Dingo\Api\Facade\Route::any('/typeCar', 'Wechat\ServiceController@typeCar');
        //企业申请
        \Dingo\Api\Facade\Route::any('/business', 'Wechat\UserController@business');
        //航站楼返回
        \Dingo\Api\Facade\Route::get('/flight', 'Wechat\OrderController@flight');
        //获取我的优惠券
        \Dingo\Api\Facade\Route::get('/coupon', 'Wechat\CouponController@getCoupon');
        //用户领取优惠券
        \Dingo\Api\Facade\Route::get('/getUserCoupon', 'Wechat\CouponController@getUserCoupon');
        //获取出行卡
        \Dingo\Api\Facade\Route::get('/getCard', 'Wechat\CouponController@getCard');
        //出行卡绑定
        \Dingo\Api\Facade\Route::get('/cardBind', 'Wechat\CouponController@cardBind');
        //我的出行卡
        \Dingo\Api\Facade\Route::post('/getMyCard', 'Wechat\CouponController@getMyCard');
        //支付
        \Dingo\Api\Facade\Route::post('/WxOrder', 'Wechat\PayController@WxOrder');
        //接收支付信息
        \Dingo\Api\Facade\Route::post('/createPayInfo', 'Wechat\OrderController@createPayInfo');
        //购买出行卡后发送短信
        \Dingo\Api\Facade\Route::post('/sendSms', 'Wechat\CouponController@sendSms');
        //获取车系以及费用
        \Dingo\Api\Facade\Route::post('/carRule', 'Wechat\CouponController@carRule');
        //查询用户优惠券
        \Dingo\Api\Facade\Route::any('/user_coupon', 'Wechat\UserController@user_coupon');
        //修改优惠券状态
        \Dingo\Api\Facade\Route::any('/Updatecoupon', 'Wechat\UserController@Updatecoupon');
        //撤销订单
        \Dingo\Api\Facade\Route::any('/undoOrder', 'Wechat\OrderController@undoOrder');
        //推送消息
        \Dingo\Api\Facade\Route::any('/push', 'Wechat\OrderController@push');
        //推送消息
        \Dingo\Api\Facade\Route::post('/modifyPassword', 'Wechat\UserController@modifyPassword');

    });

});





