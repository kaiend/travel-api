<?php

namespace App\Http\Validators;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class PayValidator
{
	/**
	 * 登录数据验证
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function WxOrder( Request $request )
	{
		$only = ['body','out_trade_no','total_fee','openid'];

		$rules = [
			'body' => 'required',
			'total_fee' => 'required|numeric',
			'openid' => 'required',
		];

		$messages = [
			'body.required' => '商品描述不能为空',
			'total_fee.required' => '商品价格不能为空',
			'total_fee.numeric' => '商品价格错误',

			'openid.required' => 'openid不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));


		$input['out_trade_no'] = self::createNumber();
		return $input;
	}

	/**
	 * 生成订单号
	 * */
	private function createNumber()
	{
		return date('ymds').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 7);
	}

}