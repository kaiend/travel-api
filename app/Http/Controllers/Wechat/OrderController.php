<?php

namespace App\Http\Controllers\Wechat;

use App\Helpers\Common;
use App\Helpers\Sms;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Validators\UserValidator;
use App\Http\Controllers\Controller;
use App\Helpers\ReturnMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class OrderController extends Controller
{
	/**
	 * 发送验证码
	 * @author yxk
	 * @param $request
	 * @return mixed;
	 * */
	public function sendCode( Request $request )
	{
        $only = ['user_id','provice','car_id','origin','end','price','type','user_nickname','avatar','user_pass'];
        $input = $request->only($only);
        dump($input);


	}
}