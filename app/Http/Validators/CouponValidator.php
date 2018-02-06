<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/6
 * Time: 13:58
 */

namespace App\Http\Validators;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponValidator
{
    /**
     * 优惠券数据验证
     *
     * @param Request $request
     * @return mixed
     * */
    public static function myCoupon( Request $request )
    {
        $only = ['user_id'];

        $rules = [
            'user_id' => 'required|exists:personal_user,id',
        ];

        $messages = [
            'user_id.required' => '用户id不能为空',
            'user_id.exists' => '用户不存在',
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        return $input;
    }

    /**
     * 优惠券领取数据验证
     *
     * @param Request $request
     * @return mixed
     * */
    public static function buyCoupon( Request $request )
    {
        $only = ['user_id','coupon_id'];

        $rules = [
            'user_id' => 'required|exists:personal_user,id',
            'coupon_id' => 'required|exists:coupon_groups,id',
        ];

        $messages = [
            'user_id.required' => '用户id不能为空',
            'user_id.exists' => '用户不存在',

            'coupon_id.required' => '优惠券id不能为空',
            'coupon_id.exists' => '优惠券不存在',
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        return $input;
    }

    /**
     * 出行卡获取
     *
     * @param Request $request
     * @return mixed
     * */
    public static function cardGoBind( Request $request )
    {
        $only = ['coupon_code','coupon_pass','user_id'];

        $rules = [
            'user_id' => 'required|exists:personal_user,id',
            'coupon_code' => 'required',
            'coupon_pass' => 'required',
        ];

        $messages = [
            'user_id.required' => '用户id不能为空',
            'user_id.exists' => '用户不存在',

            'coupon_code.required' => '出行卡账号不能为空',

            'coupon_pass.required' => '出行卡密码不能为空',
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        return $input;
    }

    /**
     * 出行卡
     *
     * @param Request $request
     * @return mixed
     * */
    public static function MyCard( Request $request )
    {
        $only = ['user_id'];

        $rules = [
            'user_id' => 'required|exists:personal_user,id',
        ];

        $messages = [
            'user_id.required' => '用户id不能为空',
            'user_id.exists' => '用户不存在',
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1002']));

        return $input;
    }
}