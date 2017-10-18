<?php

namespace App\Http\Validators;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
		dd($request->input('phone'));

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		return $input;
	}

}