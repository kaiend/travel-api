<?php

namespace App\Http\Validators;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Validator;


class OrderValidator
{
    /**
     * 特殊路线的验证
     * @param Request $request
     * @return array
     */
	public static function sendSpecial( Request $request)
    {
        $only = ['time','name','phone','people','room_number','remarks','car_id','end','origin','price','type','end_position','origin_position','hotel_number','service_type'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
            'time'=>'required',
            'name'=>'required',
            'people'=>'required',
            //'room_number'=>'required',
            'car_id' =>'required',
            'end' =>'required',
            'end_position' =>'required',
            'origin' =>'required',
            'origin_position'=>'required',
            'price' =>'required',
            'type'  => 'required',
            //'hotel_number' =>'required'
        ];

        $messages = [
            'phone.required' => '乘车人手机号不能为空',
            'phone.regex' => '手机号错误',
            'time.required'=>'乘车时间不能为空',
            'name.required'=>'乘车人姓名不能为空',
            'people.required'=>'乘车人数不能为空',
            //'room_number.required'=>'房间号不能为空',
            'car_id.required' =>'车辆不能为空',
            'end.required' =>'终点不能为空',
            'origin.required' =>'起点不能为空',
            'price.required' =>'车费不能为空',
            'type.required'  => '服务类型不能为空',
            'origin_position.required' =>'起点经纬度不能为空',
            'end_position.required' =>'终点经纬度不能为空',
            //'hotel_number.required' =>'酒店订单号不能为空'
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    /**
     * 按时包车--验证
     * 终点可不填写
     * @param Request $request
     * @return array
     */
    public static function sendPackage( Request $request )
    {
        $only = ['time','name','phone','people','room_number','remarks','car_id','type','price','end','origin','end_position','origin_position','hotel_number','service_type'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
            'time'=>'required',
            'name'=>'required',
            'people'=>'required',
            //'room_number'=>'required',
            'origin' =>'required',
            'car_id' =>'required',
            'price' =>'required',
            'type' =>'required',
            'origin_position'=>'required',
            //'hotel_number' =>'required'
        ];

        $messages = [
            'phone.required' => '乘车人手机号不能为空',
            'phone.regex' => '手机号错误',
            'time.required'=>'乘车时间不能为空',
            'name.required'=>'乘车人姓名不能为空',
            'people.required'=>'乘车人数不能为空',
            //'room_number.required'=>'房间号不能为空',
            'car_id.required' =>'车辆不能为空',
            'origin.required' =>'起点不能为空',
            'origin_position.required' =>'起点经纬度不能为空',
            'price.required' =>'车费不能为空',
            'type.required'  => '服务类型不能为空',
            //'hotel_number.required' =>'酒店订单号不能为空'
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    /**
     * 接机验证
     * @param Request $request
     * @return array
     */
    public static function getFlight( Request $request )
    {
        $only = ['flight_number','terminal','time','name','phone','people','room_number','remarks','car_id','type','price','end','origin','end_position','origin_position','cip','hotel_number','getFlight','sendFlight','service_type'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
            'time'=>'required',
            'name'=>'required',
            'people'=>'required',
            //'room_number'=>'required',
            'origin' =>'required',
            'end' =>'required',
            'car_id' =>'required',
            'price' =>'required',
            'type' =>'required',
            'origin_position'=>'required',
            'end_position'=>'required',
            'flight_number' =>'required',
            'terminal' => 'required',
            'cip' =>'required',
            'service_type' => 'required'
            //'hotel_number' =>'required'
        ];

        $messages = [
            'phone.required' => '乘车人手机号不能为空',
            'phone.regex' => '手机号错误',
            'time.required'=>'乘车时间不能为空',
            'name.required'=>'乘车人姓名不能为空',
            'people.required'=>'乘车人数不能为空',
            //'room_number.required'=>'房间号不能为空',
            'car_id.required' =>'车辆不能为空',
            'origin.required' =>'起点不能为空',
            'end.required' =>'终点不能为空',
            'end_position.required' =>'终点经纬度不能为空',
            'origin_position.required' =>'起点经纬度不能为空',
            'price.required' =>'车费不能为空',
            'type.required'  => '服务类型不能为空',
            'flight_number.required' =>'航班号不能为空',
            'terminal.required' => '航站楼不能为空',
            'cip.required' =>'cip服务不能为空',
            'service_type.required' =>'类型不能为空',
            //'hotel_number.required' =>'酒店订单号不能为空'
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    /**
     * 送机验证
     * @param Request $request
     * @return array
     */
    public static function sendFlight( Request $request)
    {
        $only = ['time','name','phone','people','room_number','remarks','car_id','type','price','end','origin','end_position','origin_position','cip','hotel_number','terminal','service_type'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
            'time'=>'required',
            'name'=>'required',
            'people'=>'required',
            //'room_number'=>'required',
            'origin' =>'required',
            'end' =>'required',
            'car_id' =>'required',
            'price' =>'required',
            'type' =>'required',
            'origin_position'=>'required',
            'end_position'=>'required',
            'cip' =>'required',
            'terminal' => 'required',
            'service_type' => 'required'
            //'hotel_number'=>'required'
        ];

        $messages = [
            'phone.required' => '乘车人手机号不能为空',
            'phone.regex' => '手机号错误',
            'time.required'=>'乘车时间不能为空',
            'name.required'=>'乘车人姓名不能为空',
            'people.required'=>'乘车人数不能为空',
            //'room_number.required'=>'房间号不能为空',
            'car_id.required' =>'车辆不能为空',
            'origin.required' =>'起点不能为空',
            'end.required' =>'终点不能为空',
            'end_position.required' =>'终点经纬度不能为空',
            'origin_position.required' =>'起点经纬度不能为空',
            'price.required' =>'车费不能为空',
            'type.required'  => '服务类型不能为空',
            'cip.required' =>'cip服务不能为空',
            'terminal.required' => '航站楼不能为空',
            'service_type.required' => '类型不能为空'
            //'hotel_number.required' =>'酒店订单不能为空'
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    /**
     * 接送站
     * @param Request $request
     * @return array
     */
    public static function takeTrain( Request $request )
    {
        $only = ['trips','train_station','time','name','phone','people','room_number','remarks','car_id','type','price','end','origin','end_position','origin_position','hotel_number','service_type'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
            'time'=>'required',
            'name'=>'required',
            'people'=>'required',
            //'room_number'=>'required',
            'origin' =>'required',
            'end' =>'required',
            'car_id' =>'required',
            'price' =>'required',
            'type' =>'required',
            'origin_position'=>'required',
            'end_position'=>'required',
            'trips' =>'required',
            'train_station' => 'required',
            //'hotel_number' => 'required',
        ];

        $messages = [
            'phone.required' => '乘车人手机号不能为空',
            'phone.regex' => '手机号错误',
            'time.required'=>'乘车时间不能为空',
            'name.required'=>'乘车人姓名不能为空',
            'people.required'=>'乘车人数不能为空',
            //'room_number.required'=>'房间号不能为空',
            'car_id.required' =>'车辆不能为空',
            'origin.required' =>'起点不能为空',
            'end.required' =>'终点不能为空',
            'end_position.required' =>'终点经纬度不能为空',
            'origin_position.required' =>'起点经纬度不能为空',
            'price.required' =>'车费不能为空',
            'type.required'  => '服务类型不能为空',
            'trips.required' =>'车次不能为空',
            'train_station.required' => '火车站不能为空',
            //'hotel_number.required' =>'酒店订单不能为空'
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    /**
     * 追加订单
     * @param Request $request
     * @return array
     */
    public static function makeExtra( Request $request )
    {
        $only = ['order_number','remarks','car_id','type','end','origin','end_position','origin_position','hotel_number','hours'];

        $rules = [
            'order_number' =>'required',
            //'origin' =>'required',
            //'origin_position'=>'required',
            //'end' =>'required',
            //'end_position'=>'required',
            'car_id' =>'required',
            'type' =>'required',
        ];

        $messages = [
            'order_number.required' =>'原订单编号不能为空',
            'car_id.required' =>'车辆不能为空',
            //'origin.required' =>'起点不能为空',
            //'end.required' =>'终点不能为空',
            //'end_position.required' =>'终点经纬度不能为空',
            //'origin_position.required' =>'起点经纬度不能为空',
            'type.required'  => '服务类型不能为空'
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    public static function check( Request $request)
    {
        $only = ['type','order_id','user_id','reason'];

        $rules = [
            'type' =>'required',
            'order_id' => 'required',
            'user_id' => 'required'
        ];

        $messages = [
            'type.required'  => '操作类型不能为空',
            'order_id.required'  => '订单id不能为空',
            'user_id.required'  => '用户id不能为空',
        ];

        $input = $request->only($only);


        $validator = Validator::make($input, $rules, $messages);


        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    /**
     * 订单支付数据验证
     *
     * @param Request $request
     * @return mixed
     * */
    public static function orderPay( Request $request )
    {
        $only = ['user_id','order_number'];

        $rules = [
            'user_id' => 'required|exists:personal_user,id',
            'order_number' => 'required|exists:wx_order,order_number',
        ];

        $messages = [
            'user_id.required' => '用户id不能为空',
            'user_id.exists' => '用户不存在',

            'order_number.required' => '订单编号不能为空',
            'order_number.exists' => '订单不存在'

        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));


        return $input;
    }

    /**
     * 我的消息
     *
     * @param Request $request
     * @return post
     */
    public static function news(Request $request)
    {
        $only = ['token'];

        $rules = [
            'token' =>'required',
        ];

        $messages = [
            'token.required'  => '用户token不能为空',
        ];

        $input = $request->only($only);


        $validator = Validator::make($input, $rules, $messages);


        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }

    /**
     * 我的消息
     *
     * @param Request $request
     * @return post
     */
    public static function newsFinance(Request $request)
    {
        $only = ['token','log_id'];

        $rules = [
            'token' =>'required',
            'log_id' =>'required',
        ];

        $messages = [
            'token.required'  => '用户token不能为空',
            'log_id.required'  => '用户ID不能为空',
        ];

        $input = $request->only($only);


        $validator = Validator::make($input, $rules, $messages);


        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }
}