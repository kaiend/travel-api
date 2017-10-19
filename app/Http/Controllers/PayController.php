<?php

namespace App\Http\Controllers;

use App\Http\Pay\WxPay\WxPay;
use App\Http\Validators\PayValidator;
use Illuminate\Http\Request;

class PayController extends Controller
{

	/**
	 * 生成微信订单
	 * @author yxk
	 * @param $request
	 * */
	public function WxOrder( Request $request )
	{
		$res = (new WxPay)->createOrder(PayValidator::WxOrder($request));
		header("Content-Type: application/json");
		echo $res;
	}

	/**
	 * 微信支付回调
	 * */
	public function WxNotify(  )
	{

		
	}
}