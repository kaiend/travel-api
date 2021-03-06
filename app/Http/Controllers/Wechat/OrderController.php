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

    private $phone ='18510570020';
    /**
     * 下订单
     * @param $request
     * @return mixed
     * */
    public function createOrder( Request $request )
    {
        $input = WxorderValidator::topUp($request);

        $price = $input['price'] / 100;

        if(empty($input['passenger_name'])){
            $input['passenger_name'] = $input['orders_name'];
        }
        if(empty($input['passenger_phone'])){
            $input['passenger_name'] = $input['orders_phone'];
        }
        $order['order_number'] = $input['order_number'];

        try {
            if(empty($input['custom'])){
                $result = DB::table('order')->insertGetId([
                    'user_id'=>$input['user_id'],
                    'hotel_id'=>$input['city'],
                    'car_id'=>$input['car_series'],
                    'end'=>$input['end'],
                    'origin'=>$input['origin'],
                    'price'=>$price,
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
                    'passenger_people' => $input['passenger_people']
                ]);
            }else{
                $result = DB::table('order')->insertGetId([
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
                    'custom'=>$input['custom'],
                    'passenger_people' => $input['passenger_people']
                ]);
            }

            DB::table('way_to')->insert([
                ['order_id' => $result,'name' => 'origin', 'content' => json_encode([$input['origin']])],
                ['order_id' => $result,'name' => 'end', 'content' => json_encode([$input['end']])]
            ]);

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
            $obj[$key]['price'] = $val['price'] * 100;
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

/*
 * 接送站返回
 */
    public function trainStation(Request $request){
        $input = $request->input();
        if($input['city'] == '北京市'){
            $data = [
                [
                    'coordinate'=> '116.433737,39.90978',
                    'name' => '北京站'
                ],
                [
                    'coordinate'=> '116.359489,39.951655',
                    'name' => '北京北站'
                ],
                [
                    'coordinate'=> '116.385814,39.871182',
                    'name' => '北京南站'
                ],
                [
                    'coordinate'=> '116.328097,39.900858',
                    'name' => '北京西站'
                ],
                [
                    'coordinate'=> '116.489951,39.907681',
                    'name' => '北京东站'
                ],
            ];
            return ReturnMessage::successData($data);
        }else{
            $data = [
                [
                    'coordinate'=> '117.216586,39.141773',
                    'name' => '天津站'
                ],
                [
                    'coordinate'=> '117.169986,39.16427',
                    'name' => '天津西站'
                ],
                [
                    'coordinate'=> '117.067499,39.062802',
                    'name' => '天津南站'
                ],
                [
                    'coordinate'=> '117.215946,39.172524',
                    'name' => '天津北站'
                ],
                [
                    'coordinate'=> '117.649419,39.031483',
                    'name' => '塘沽站'
                ],
                [
                    'coordinate'=> '117.689958,39.011058',
                    'name' => '于家堡站'
                ],
                [
                    'coordinate'=> '117.404624,40.032737',
                    'name' => '蓟州站'
                ],
                [
                    'coordinate'=> '117.008631,39.148694',
                    'name' => '杨柳青站'
                ],
                [
                    'coordinate'=> '117.617575,39.085301',
                    'name' => '滨海站'
                ],
                [
                    'coordinate'=> '117.767464,39.241501',
                    'name' => '滨海北站'
                ],
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
        $price = $order['price'] * 100;

        if ($order['pay_status'] == $this->order_pay)
            return ReturnMessage::success('订单已支付，请勿重复支付',1002);

        if ($user['travel_card_money'] >= $price){
            $res['travel_card_money'] = $price;
        }else{
            if ($user['travel_card_money'] > 0 ){
                if ($user['travel_card_money'] + $user['balance'] >= $price){
                    $res['travel_card_money'] = $user['travel_card_money'];
                    $res['balance'] = $price - $user['travel_card_money'];
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
        $data['pay_status'] = 3;

        try {
            Order::modifyOrder($input,$data);
        } catch (\Exception $e) {
            return ReturnMessage::success('撤销订单失败',1002);
        }
        return ReturnMessage::success();

    }

    /**
     * 充值成功数据操作
     *
     * @author yxk
     * @param $data
     * @return bool
     * */
    public function topUpDate(  Request $request)
    {
        $input = $request;
        $data = array(
            'user_id' => $input['user_id'],
            'price' => $input['total_fee'],
            'created_at' => time(),
        );
        try {
            DB::table('top_up')->insert($data);
            DB::table('personal_user')->where('id',$data['user_id'])->increment('balance',$data['price']);

            return ReturnMessage::success('添加支付信息成功',1000);
        } catch (\Exception $e) {
            return ReturnMessage::success('添加支付信息失败',1002);
        }
    }

    /*
     * 推送消息
     */
    public function push(Request $request){
        $input = $request->input();

        $where['condition'] = 1;
        $where['status'] = 1;
        $where['channel'] = 5;

    $message_sql = Db::table('message')
        ->where($where)
        ->first();

    $message_data = array(
        'user' => $input['user_id'],
        'time' => date('Y-m-d H:i:s',time()),
    );
    $result = Db::table('order')->where('order_number',$input['order_number'])->value('id');
    $order_number_url = url('/home/homeorder/orderdetails',array('id'=>$input['order_number']));
    $message_sql['content'] .='<a id="order_number_buchongfu" href="javascript:openapp(\''.$order_number_url.'\',\'189admin\',\'订单详情\');" class="btn btn-primary" data-dismiss="modal">订单号：'.$input['order_number'].'</a>';
    if($message_sql){
        $msg = $this->goEasy($result,$message_sql['id'],$message_sql['title'],'',$message_sql['content'],$message_data);
    }
}

    //https 请求post
    public function vpost($url,$post_data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * 测试消息提醒
     */
    public function goEasy($order_id,$mid,$title,$mark,$content,$data)
    {


        if(strpos($content,'xxx') !== false){
            $preg = preg_replace("[xxx]", $data['user'], $content);
        }

        if(strpos($content,'time') !== false){
            $preg = preg_replace("[time]", $data['time'], $preg);
        }
        if(!isset($preg)){
            $preg = $content;
        }

        $msg = array(
            'order_id' => $order_id,
            'mid' => $mid,
            'title' => $title,
            'content' => $preg,
            'create_time' => time(),
        );
        $list_id = Db::table('message_list')->insertGetId($msg);

        $data = array(
            'appkey' => 'BC-af1909bf4e844d7f8d9d18604a910fc4',
            'channel' => $mark,
            'content' => $list_id,
        );

        $url = 'http://rest-hangzhou.goeasy.io/publish';

        $result = $this->vpost($url,$data);
        return $result;
    }

    /**
     * cip
     */
    public function getCip( Request $request )
    {
        $input = $request->input();

        try {

            $json = json_decode($input['cip'],true);

            $cip = (array)$json;

            foreach ($cip as $k => $v) {

                $data[] = array(
                    'order_number' => $input['order_number'],
                    'name' => $v['name'],
                    'phone' => $v['phone']
                );

            }

            DB::table('order')->where('order_number',$input['order_number'])->update(['cip_number' => count($data)]);
            DB::table('order_cip_number')->insert($data);

            return response()->json([
                'code' => '1000',
                'info' => 'success',
                'data' => []
            ]);

        } catch (\Exception $e) {
            return ReturnMessage::success('添加CIP人数失败', 1002);
        }

    }
}