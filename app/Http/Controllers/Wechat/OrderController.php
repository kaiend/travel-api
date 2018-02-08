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
use App\Http\Validators\WxorderValidator;
use App\Models\Trading;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    //订单状态 已支付
    private $order_pay = '2';

    //订单状态  已取消
    private $order_undo = '3';

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
                    'order_number'=>$input['order_number'],
                    'remarks'=>$input['remarks'],
                    'judgment'=>4,
                    'origin_position'=>$input['origin_position'],
                    'end_position'=>$input['end_position'],

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
                    'order_number'=>$input['order_number'],
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


        $obj = Order::orderList($input);
        foreach($obj as $key=>$val){
           // echo $val['type'];
            $types = Db::table("server_item")->where("id",$val['type'])->pluck('name');
            $obj[$key]['types'] = $types[0];
        }


        return ReturnMessage::successData(Common::formatTime($obj));

    }
    /*
     * 航站楼返回
     */
    public function flight(Request $request){
        $input = $request->input();
        if($input['city'] == '北京市'){
            $data = [
                [
                    'coordinate'=> '116.594566,40.086792',
                    'name' => '北京首都机场T1航站楼'
                ],
                [
                    'coordinate'=> '116.600726,40.086705',
                    'name' => '北京首都机场T2航站楼'
                ],
                [
                    'coordinate'=> '116.619758,40.072776',
                    'name' => '北京首都机场T3航站楼'
                ],
                [
                    'coordinate'=> '116.400712,39.790456',
                    'name' => '北京南苑机场'
                ],
            ];
            return ReturnMessage::successData($data);
        }else{
            $data = [
                [
                    'coordinate'=> '117.368077,39.13701',
                    'name' => '天津滨海国际机场T2航站楼'
                ]
            ];
            return ReturnMessage::successData($data);
        }
    }


    /**
     * 接收支付信息
     * @param $request
     * @return mixed
     * */
    public function createPayInfo( Request $request )
    {
        $input = $request->input();

        try {
            DB::table('pay_order')->insert([
                'price'=>$input['price'],
                'order_number'=>$input['order_number'],
                'openid'=>$input['openid'],
                'type'=>$input['type'],
                'create_at'=>time()
            ]);

        } catch (\Exception $e) {
            return ReturnMessage::success('添加支付信息失败',1002);
        }
        return ReturnMessage::success('添加支付信息成功',1000);
    }

    /**
     * 订单支付
     *
     * @param $request
     * @return mixed
     * */
    public function orderPay( Request $request )
    {
        $input = WxorderValidator::orderPay($request);

        $whereUser['id'] = $input['user_id'];
        $whereOrder['order_number'] = $input['order_number'];
        $user = User::getUserFirst($whereUser);
        $order = Order::getOrderFirst($whereOrder);


        if ($order['pay_status'] == $this->order_pay)
            return ReturnMessage::success('订单已支付，请勿重复支付',1002);

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

        $data['pay_status'] = $this->order_pay;
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
     * 取消订单
     *
     * @param $request
     * @return mixed
     * */
    public function undoOrder( Request $request )
    {
        $input = WxorderValidator::orderPay($request);

        $data['status'] = 0;

        try {
            Order::modifyOrder($input,$data);
        } catch (\Exception $e) {
            return ReturnMessage::success('撤销订单失败',1002);
        }
        return ReturnMessage::success();

    }


}