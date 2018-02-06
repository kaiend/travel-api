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
use App\Http\Validators\OrderValidator;
use App\Http\Validators\WxorderValidator;
use App\Models\Trading;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    //订单状态 已支付
    private $order_pay = 'pay';

    //订单状态  已取消
    private $order_undo = 'undo';

    private $phone ='18311161659';
    /**
     * 下订单
     * @param $request
     * @return mixed
     * */
    public function createOrder( Request $request )
    {
        $input = WxorderValidator::topUp($request);
        if(empty($input['passenger_name'])){
            $input['passenger_name'] = $input['orders_name'];
        }
        if(empty($input['passenger_phone'])){
            $input['passenger_name'] = $input['orders_phone'];
        }
        $order['order_number'] = $input['order_number'];
        try {
            if(empty($input['custom'])){
                DB::table('order')->insert([
                    'user_id'=>$input['user_id'],
                    'hotel_id'=>$input['city'],
                    'car_id'=>$input['car_series'],
                    'end'=>$input['end'],
                    'origin'=>$input['origin'],
                    'price'=>$input['price'],
                    'type'=>$input['type'],
                    'orders_name'=>$input['orders_name'],
                    'orders_phone'=>$input['orders_phone'],
                    'passenger_name'=>$input['passenger_name'],
                    'passenger_phone'=>$input['passenger_phone'],
                    'appointment'=>$input['appointment'],
                    'created_at'=>time(),
                    'remarks'=>$input['remarks'],
                    'judgment'=>4,
                    'origin_position'=>$input['origin_position'],
                    'end_position'=>$input['end_position']

                ]);
            }else{
                DB::table('order')->insert([
                    'user_id'=>$input['user_id'],
                    'hotel_id'=>$input['city'],
                    'car_id'=>$input['car_series'],
                    'end'=>$input['end'],
                    'origin'=>$input['origin'],
                    'price'=>$input['price'],
                    'type'=>$input['type'],
                    'orders_name'=>$input['orders_name'],
                    'orders_phone'=>$input['orders_phone'],
                    'passenger_name'=>$input['passenger_name'],
                    'passenger_phone'=>$input['passenger_phone'],
                    'appointment'=>$input['appointment'],
                    'created_at'=>time(),
                    'remarks'=>$input['remarks'],
                    'judgment'=>4,
                    'origin_position'=>$input['origin_position'],
                    'end_position'=>$input['end_position'],
                    'custom'=>$input['custom']

                ]);
            }

            //下单成功后给时代负责人发送短信
            $this->sendMessage($order['order_number']);
        } catch (\Exception $e) {
            return ReturnMessage::success('添加订单失败',1002);
        }
        return ReturnMessage::successData($order);
    }

    /**
     * 发送短信
     */
    private function sendMessage($data)
    {
        $msg ='您有新的订单了,订单编号'.$data.'【时代出行】';
        return (new Sms())->sendSMS($this->phone,$msg);
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


}