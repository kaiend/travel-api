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
        $order['order_number'] = $input['order_number'];
        try {
       /*     DB::table('users')->insert([
                'name' => str_random(10),
                'email' => str_random(10).'@gmail.com',
                'password' => bcrypt('secret'),
            ]);*/
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
}