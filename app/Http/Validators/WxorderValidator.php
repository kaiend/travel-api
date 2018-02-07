<?php

namespace App\Http\Validators;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WxorderValidator
{
	/**
	 * 充值数据验证
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function topUp( Request $request )
	{
		$only = ['user_id','type','price','origin','end','car_series','orders_name','orders_phone','appointment','passenger_name','passenger_phone','cip','origin_position','end_position','city','remarks','custom'];

		$rules = [
			'type' => 'required',
			'price' => 'required',
			'origin' => 'required',
			'end' => 'required',
			'car_series' => 'required',
			/*'orders_name' => 'required',
			'orders_phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',*/
			'appointment' => 'required',
			'user_id' => 'required|exists:personal_user,id',
		];

		$messages = [
			'user_id.required' => '用户id不能为空',
			'user_id.exists' => '用户不存在',

			'price.required' => '价格不能为空',

			'car_series.required' => '车系不能为空',

			'origin.required' => '起点地址不能为空',

			'end.required' => '终点地址不能为空',

			'type.required' => '服务类型不能为空',

		/*	'orders_name.required' => '下单人姓名不能为空',

			'orders_phone.required' => '下单人手机号不能为空',
			'orders_phone.regex' => '下单人手机号错误',*/

			'appointment.required' => '预约时间不能为空',
            'origin_position.required' => '起点经纬度不能为空',
            'end_position.required' => '终点经纬度不能为空',
            'city.required' => '城市编码不能为空',


		];

		$input = $request->only($only);


		$validator = Validator::make($input, $rules, $messages);

		/*if ($validator->fails()){
            echo  111;die;
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));
        }else{
			    echo 222;die;
    }*/

		if (empty($input['cip']))
			unset($input['cip']);

		$input['appointment'] = strtotime($input['appointment']);
		$input['order_number'] = Common::createNumber();
		$input['created_at'] = time();

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
			'order_number' => 'required|exists:order,order_number',
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


}