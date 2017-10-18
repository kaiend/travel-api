<?php

namespace App\Http\Validators;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;

class UserValidator
{
	/**
	 * 发送验证码手机号验证
	 * @param Request $request
	 * @return mixed
	 * */
	public static function sendCode( Request $request )
	{
		$only = ['phone'];

		$rules = [
			'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
		];

		$messages = [
			'phone.required' => '手机号不能为空',
			'phone.regex' => '手机号错误'
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		return $input;
	}

	/**
	 * 注册数据验证
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function register( Request $request )
	{
		$only = ['phone','code','user_nickname','avatar','user_pass'];

		$rules = [
			'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/|unique:user,phone',
			'code' => 'required',
		];

		$messages = [
			'phone.required' => '手机号不能为空',
			'phone.regex' => '手机号错误',
			'phone.unique'=> '用户已存在',

			'code.required' => '验证码不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		if (!Redis::exists($input['phone']) || (Redis::get($input['phone']) != $input['code']))
			exit(json_encode(['info'=>'验证码错误','code'=>'1002']));

		unset($input['code']);
		$input['user_pass'] = '###'.md5(md5($input['user_pass']));
		$input['create_time'] = time();

		return $input;
	}

}