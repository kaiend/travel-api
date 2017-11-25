<?php

namespace App\Http\Validators;

use App\Helpers\Common;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Validator;


class OrderValidator
{
	public static function sendSpecial( Request $request)
    {
        $only = ['time','name','phone','people','room_number','remarks','car_id','end','origin','price'];

        $rules = [
            'phone' => 'required|regex:/^1[34578]{1}[\d]{9}$/',
            'time'=>'required',
            'name'=>'required',
            'people'=>'required',
            'room_number'=>'required',
            'car_id' =>'required',
            'end' =>'required',
            'origin' =>'required',
            'price' =>'required'
        ];

        $messages = [
            'phone.required' => '乘车人手机号不能为空',
            'phone.regex' => '手机号错误',
            'time.required'=>'乘车时间不能为空',
            'name.required'=>'乘车人姓名不能为空',
            'people.required'=>'乘车人数不能为空',
            'room_number.required'=>'房间号不能为空',
            'car_id.required' =>'车辆不能为空',
            'end.required' =>'终点不能为空',
            'origin.required' =>'起点不能为空',
            'price.required' =>'车费不能为空'
        ];

        $input = $request->only($only);

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails())
            exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

        return $input;
    }
}