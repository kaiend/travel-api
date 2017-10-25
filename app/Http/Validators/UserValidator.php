<?php

namespace App\Http\Validators;

use App\Helpers\Common;
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
			'user_pass' => 'required',
		];

		$messages = [
			'phone.required' => '手机号不能为空',
			'phone.regex' => '手机号错误',
			'phone.unique'=> '用户已存在',

			'code.required' => '验证码不能为空',
			'user_pass.required' => '密码不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		self::redisVerify($input);

		unset($input['code']);
		$input['user_pass'] = Common::createPassword($input['user_pass']);;
		$input['create_time'] = time();

		return $input;
	}

	/**
	 * 登录数据验证
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function login( Request $request )
	{
		$only = ['phone','user_pass'];

		$rules = [
			'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
			'user_pass' => 'required',
		];

		$messages = [
			'phone.required' => '手机号不能为空',
			'phone.regex' => '手机号错误',

			'user_pass.required' => '密码不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		$input['user_pass'] = Common::createPassword($input['user_pass']);;

		return $input;
	}

	/**
	 * 验证码验证verifyCode
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function verifyCode( Request $request )
	{
		$only = ['phone','code'];

		$rules = [
			'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
			'code' => 'required',
		];

		$messages = [
			'phone.required' => '手机号不能为空',
			'phone.regex' => '手机号错误',

			'code.required' => '验证码不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		self::redisVerify($input);

		return;
	}

	//验证码验证
	private static function redisVerify($input)
	{
		if (!Redis::exists($input['phone']) || (Redis::get($input['phone']) != $input['code']))
			exit(json_encode(['info'=>Redis::get($input['phone']),'code'=>'1002']));
	}

	/**
	 * 修改密码数据验证
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function modifyPassword( Request $request )
	{
		$only = ['phone','code','user_pass'];

		$rules = [
			'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/|exists:user,phone',
			'code' => 'required',
			'user_pass' => 'required',
		];

		$messages = [
			'phone.required' => '手机号不能为空',
			'phone.regex' => '手机号错误',
			'phone.exists'=> '用户不存在',

			'code.required' => '验证码不能为空',
			'user_pass.required' => '密码不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		self::redisVerify($input);

		return $input;
	}

	/**
	 * 出行卡填写
	 *
	 * @param Request $request
	 * @return mixed
	 * */
	public static function travelCard( Request $request )
	{
		$only = ['id','travel_card','name','id_card','travel_card_number'];

		$rules = [
			'id' => 'required|exists:user,id',
			'name' => 'required',
			'id_card' => 'required',
		];

		$messages = [
			'id.required' => '用户id不能为空',
			'id.exists'=> '用户不存在',

			'name.required' => '姓名不能为空',
			'id_card.required' => '身份证号不能为空',
		];

		$input = $request->only($only);

		$validator = Validator::make($input, $rules, $messages);

		if ($validator->fails())
			exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

		if (empty($input['travel_card'])){
			unset($input['travel_card']);
		}else{
			unset($input['travel_card_number']);
		}

		return $input;
	}
}