<?php

namespace App\Http\Validators;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Validator;


class CarValidator
{
   public static function  userCar( Request $request)
   {
       $only = ['type'];

       $rules = [
           'type' =>'required',
       ];

       $messages = [
           'type.required'  => '服务类型不能为空',
       ];

       $input = $request->only($only);


       $validator = Validator::make($input, $rules, $messages);


       if ($validator->fails())
           exit(json_encode(['info'=>$validator->errors()->first(),'code'=>'1011']));

       return $input;

   }

}