<?php

namespace App\Http\Validators;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayValidator
{
	/**
	 * 支付数据验证
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
			'out_trade_no' => 'required',
		];

		$messages = [
			'body.required' => '商品描述不能为空',
			'total_fee.required' => '商品价格不能为空',
			'total_fee.numeric' => '商品价格错误',

			'openid.required' => 'openid不能为空',
			'out_trade_no.required' => '订单编号不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));


//		$input['out_trade_no'] = self::createNumber();
		return $input;
	}


	/**
	 * 充值数据验证
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function topUp( Request $request )
	{
		$only = ['body','out_trade_no','total_fee','openid','user_id'];

		$rules = [
			'body' => 'required',
			'total_fee' => 'required|numeric',
			'openid' => 'required',
			'user_id' => 'required|exists:user,id',
		];

		$messages = [
			'body.required' => '商品描述不能为空',
			'total_fee.required' => '商品价格不能为空',
			'total_fee.numeric' => '商品价格错误',

			'openid.required' => 'openid不能为空',
			'user_id.required' => 'user_id不能为空',
			'user_id.exists' => '用户不存在',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		$input['out_trade_no'] = self::createTopUpNumber($input['user_id']);
		return $input;
	}

	/**
	 * 生成充值支付单号
	 * @param int $userId 用户id
	 * @return string
	 * */
	protected static function createTopUpNumber($userId)
	{

		$data = date("ymd");
		$userId = sprintf("%06d", $userId);

		return 'CZ'.$data.$userId.rand(1000,9999);
	}

}