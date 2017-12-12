<?php

namespace App\Http\Validators;

use App\Helpers\Common;
use Dingo\Api\Http\Request;
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

	public static function sign( Request $request)
    {
        $only = ['phone','code','model_status', 'jpush_code', 'model_code'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/|exists:hotel_user,mobile',
            'code' => 'required',
        ];

        $messages = [
            'phone.required' => '手机号不能为空',
            'phone.regex' => '手机号错误',
            'phone.exists'=> '用户不存在',
            'code.required' => '验证码不能为空',

        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        self::redisVerify($input);

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
			exit(json_encode(['info'=>'验证码错误','code'=>'1004']));
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


    /**
     * APP端的酒店用户验证
     * @param Request $request
     * @return array
     */
    public static function hotelLogin( Request $request)
    {
        $only = ['phone','password','model_status','jpush_code','model_code'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
            'password' => 'required',
        ];

        $messages = [
            'phone.required' => '手机号不能为空',
            'phone.regex' => '手机号错误',

            'password' => '密码不能为空',
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        $input['password'] = Common::createPassword($input['password']);

        return $input;
    }
    /**
     * 修改密码数据验证
     *
     * @param Request $request
     * @return mixed
     * */
    public static function editPassword( Request $request )
    {
        $only = ['phone','code','password','model_status','jpush_code','model_code'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/|exists:hotel_user,mobile',
            'code' => 'required',
            'password' => 'required',
        ];

        $messages = [
            'phone.required' => '手机号不能为空',
            'phone.regex' => '手机号错误',
            'phone.exists'=> '用户不存在',

            'code.required' => '验证码不能为空',
            'password.required' => '密码不能为空',
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        self::redisVerify($input);

        return $input;
    }

    /**
     * 添加子账户的验证
     * @param Request $request
     * @return array
     */
    public static function addChild( Request $request )
    {

        $only = ['name','phone','department','position','type','avatar'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/|unique:hotel_user,mobile',
            'name' => 'required',
            'department' => 'required',
            'type' => 'required',
            'avatar' => 'required',
            'position' => 'required'
        ];

        $messages = [
            'phone.required' => '手机号不能为空',
            'phone.regex' => '手机号错误',
            'phone.unique'=> '用户已经存在',
            'name.required' => '姓名不能为空',
            'department.required' => '部门名称不能为空',
            'type.required' => '角色不能为空',
            'avatar.required' => '头像不能为空',
            'position.required' => '职位不能为空'

        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        return $input;
    }

    /**
     * 子账户禁用开启
     * @param Request $request
     * @return array
     */
    public static function childDisable( Request $request)
    {
        $only = ['status'];

        $rules = [
            'status' => 'required'
        ];

        $messages = [
            'status.required' => '是否禁用标示不能为空'

        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        return $input;

    }
    public static function getTravel( Request $request)
    {
        $only = ['area','type'];

        $rules = [
            'area' => 'required',
            'type' => 'required'
        ];

        $messages = [
            'area.required' => '地区不能为空',
            'type.required' => '类型不能为空'

        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        return $input;
    }
}