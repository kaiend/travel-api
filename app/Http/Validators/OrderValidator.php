<?php

namespace App\Http\Validators;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderValidator
{
	/**
	 * 充值数据验证
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function topUp( Request $request )
	{
		$only = ['user_id','type','price','origin','end','orders_name','orders_phone','appointment','passenger_name','passenger_phone','cip'];

		$rules = [
			'type' => 'required',
			'price' => 'required',
			'origin' => 'required',
			'end' => 'required',
			'orders_name' => 'required',
			'orders_phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
			'appointment' => 'required',
			'user_id' => 'required|exists:user,id',
		];

		$messages = [
			'user_id.required' => '用户id不能为空',
			'user_id.exists' => '用户不存在',

			'price.required' => '价格不能为空',

			'origin.required' => '起点地址不能为空',

			'end.required' => '终点地址不能为空',

			'type.required' => '服务类型不能为空',

			'orders_name.required' => '下单人姓名不能为空',

			'orders_phone.required' => '下单人手机号不能为空',
			'orders_phone.regex' => '下单人手机号错误',

			'appointment.required' => '预约时间不能为空',


		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		if (empty($input['cip']))
			unset($input['cip']);

		$input['appointment'] = strtotime($input['appointment']);
		$input['order_number'] = Common::createNumber();
		return $input;
	}

}