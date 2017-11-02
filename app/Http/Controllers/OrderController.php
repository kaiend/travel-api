<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Helpers\ReturnMessage;
use App\Http\Validators\OrderValidator;
use App\Models\Order;
use App\Models\Trading;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
	//订单状态 已支付
	private $order_pay = 'pay';

	//订单状态  已取消
	private $order_undo = 'undo';

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
		$order['order_number'] = $input['order_number'];
		return ReturnMessage::successData($order);
	}

	/**
	 * 订单支付
	 *
	 * @param $request
	 * @return mixed
	 * */
	public function orderPay( Request $request )
	{
		$input = OrderValidator::orderPay($request);

		$whereUser['id'] = $input['user_id'];
		$whereOrder['order_number'] = $input['order_number'];
		$user = User::getUserFirst($whereUser);
		$order = Order::getOrderFirst($whereOrder)->toArray();

		if ($user['travel_card_money'] >= $order['price']){
			$res['travel_card_money'] = $order['price'];
		}else{
			if ($user['travel_card_money'] > 0 ){
				if ($user['travel_card_money'] + $user['balance'] >= $order['price']){
					$res['travel_card_money'] = $user['travel_card_money'];
					$res['balance'] = $order['price'] - $user['travel_card_money'];
				}else{
					return ReturnMessage::success('账户金额不足',1002);
				}
			}else{
				if ($user['balance'] >= $order['price']){
					$res['balance'] = $order['price'];
				}else{
					return ReturnMessage::success('账户金额不足',1002);
				}
			}
		}

		$data['status'] = $this->order_pay;
		$trading['order_number'] = $input['order_number'];
		$trading['user_id'] = $input['user_id'];
		$trading['created_at'] = time();

		DB::beginTransaction();
		try {
			if (isset($res['travel_card_money'])){
				User::where('id',$input['user_id'])->decrement('travel_card_money',$res['travel_card_money']);
				$trading['money'] = $res['travel_card_money'];
				$trading['pay_way'] = 'card';

				Trading::create($trading);
			}
			if (isset($res['balance'])){
				User::where('id',$input['user_id'])->decrement('balance',$res['balance']);
				$trading['money'] = $res['balance'];
				$trading['pay_way'] = 'balance';
				Trading::create($trading);
			}

			Order::modifyOrder($whereOrder,$data);
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			return ReturnMessage::success('支付失败',1002);
		}

		return ReturnMessage::success();

	}
	
	/**
	 * 订单列表
	 *
	 * @param $request
	 * @return mixed
	 * */
	public function orderList( Request $request )
	{
		$input['user_id'] = $request->input('user_id');

		if (!$input['user_id'])
			return ReturnMessage::success('用户不能为空',1002);

		return ReturnMessage::successData(Common::formatTime(Order::orderList($input)));

	}

	/**
	 * 取消订单
	 *
	 * @param $request
	 * @return mixed
	 * */
	public function undoOrder( Request $request )
	{
		$input = OrderValidator::orderPay($request);

		$data['status'] = $this->order_undo;

		try {
			Order::modifyOrder($input,$data);
		} catch (\Exception $e) {
			return ReturnMessage::success('撤销订单失败',1002);
		}
		return ReturnMessage::success();

	}
}