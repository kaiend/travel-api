<?php

namespace App\Http\Controllers;

use App\Helpers\ReturnMessage;
use App\Http\Validators\OrderValidator;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
	/**
	 * 下订单
	 * @param $request
	 * @return mixed
	 * */
	public function createOrder( Request $request )
	{
		$input = OrderValidator::topUp($request);

		try {
			Order::create($input);
		} catch (\Exception $e) {
			return ReturnMessage::success('添加订单失败',1002);
		}
		return ReturnMessage::success();
	}



}