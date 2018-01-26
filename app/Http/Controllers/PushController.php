<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6
 * Time: 14:59
 */

namespace App\Http\Controllers;


use App\Helpers\Common;
use App\Helpers\ReturnMessage;
use App\Models\Chauffeur;
use App\Models\Hotel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use JPush\Client;

class PushController extends Controller
{
    private $appKey ='50505e64af2ea4b5e8e27e26';
    private $master_secret ='f90b3ccdce62056bb134aaaf';
    /**
     * 派单推送接口(后台专用)
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function pushStatus( Request $request )
    {
        $arr =$request->only('order_id','type');

        //查询
        $order_data =DB::table('order')
            ->join('hotel_user','order.user_id','=','hotel_user.id')
            ->join('hotel','order.hotel_id','=','hotel.id')
            ->select(
                'order.id',
                'order.user_id',
                'order.hotel_id',
                'order.car_id',
                'order_number',
                'end',
                'origin',
                'order.type',
                'orders_name',
                'orders_phone',
                'appointment',
                'passenger_name',
                'passenger_phone',
                'passenger_people',
                'bottom_number',
                'room_number',
                'chauffeur_name',
                'chauffeur_phone',
                'cip',
                'created_at',
                'order.status',
                'chauffeur_id',
                'remarks',
                'origin_position',
                'end_position',
                'complete_at',
                'judgment',
                'mileage',
                'send_id',
                'send_type',
                'avatar',
                'title',
                'principal',
                'price',
                'title'
            )
            ->where('order_number',$arr['order_id'])
            ->first();

        $bdata = Common::json_array($order_data);
        $con =Config::get('order.type');
        $bdata['type_name'] =$con[$bdata['type']];
        $cid = $bdata['hotel_id'];
        $uid = $bdata['user_id'];
        $status= $bdata['status'];
        $config =Config::get('order.trace');
        //$alert='订单号:'.$bdata['order_number'].'---'.$config[$status];

        $chauffeur_id =$bdata['chauffeur_id'];
        $chauffeur_data =Chauffeur::getUserFirst($chauffeur_id);
        switch($arr['type']){
            //下单通知司机----下单通知酒店管理员
            case 'make':
                if(!empty($chauffeur_data['jpush_code']) && $chauffeur_data['status_login'] == 1 ){

                    $regids =$chauffeur_data['jpush_code'];
                    $message =[
                        "extras" => array(
                            'status'=> '112',
                            "data" => ReturnMessage::toString($bdata),
                        )
                    ];
                    $appkey ='e3aa521e067467d9e4dba5bb';
                    $secret ='1ec040fbba99095178d35521';
                    $alert ='调度已将新订单指派给您，请及时接单。点击查看订单！';
                    $result =$this ->sendNotifySpecial($regids,$alert,$message,$appkey,$secret);
                    if( $result['http_code']){
                        return ReturnMessage::success();
                    }else{
                        return ReturnMessage::success('失败','1011');
                    }
                }
                //推送管理员
                $cdata = DB::table('hotel_user')
                    ->where([
                        ['hotel_id',$cid],
//                        ['type',2]
                    ])
                    ->get();
                //dd($cdata);
                $cdata =Common::json_array( $cdata );
                $message =[
                        "extras" => array(
                            "status" => $bdata['status'],
                        )
                    ];
                if( $cdata ){
                    foreach( $cdata as $k=>$v){
                        $regid= $v['jpush_code'];
                        if($regid){
                            $result =$this ->sendNotifySpecial($regid,$alert,$message,$this->appKey,$this->master_secret);
                            if( $result['http_code']){
                                return ReturnMessage::success();
                            }else{
                                return ReturnMessage::success('失败','1011');
                            }
                        }
                    }
                }else{
                    return ReturnMessage::success('失败','1011');
                }
                break;
            //通知下单人
            case 'form':
                $user_data = Hotel::getUserFirst( $uid );
                $regid =$user_data['jpush_code'];
                $message =[
                    "extras" => array(
                        "status" => $bdata['status'],
                    )
                ];
                $result =$this ->sendNotifySpecial($regid,$alert,$message,$this->appKey,$this->master_secret);
                if( $result['http_code']){
                    return ReturnMessage::success();
                }else{
                    return ReturnMessage::success('失败','1011');
                }
            break;
            default :
                return ReturnMessage::success('失败','1011');
        }
    }
    public  function createOrder($data){
        //下单推送-管理员
        //1.查询消息详情
        $where =[
            'channel' =>2,
            'type' => 2,
            'condition'=>1 //1为下单
        ];
        $re =Message::getMessageFirst($where);
        //return $re;
        $config =Config::get('order.type');
        $type =$data['type'];
        //return $config[$type];
        $data['appointment'] =strtotime($data['appointment']);
        $data['appointment'] =date('m月d日 H:i');

        $alert =str_replace("[type]服务",$config[$type],$re['title']);
        $message =str_replace("[passenger_name]/[appointment]/[origin]至[end]",$data['passenger_name'].'/'.$data['appointment'].'/'.$data['origin'].'至'.$data['end'],$re['content']);
        $m_data =[
            "extras" => array(
                'status'=> '120',
                "data" => $message,
            )
        ];
        //$result =$this->sendNotifySpecial();
        return $m_data;
        return $re['content'];
        return $re['title'];

        return $data['type'];
        return $re;
        //$alert =$re[''];


    }


    /**
     * 向所有设备推送消息-广播
     * @param $alert  消息的标题
     * @param $message  需要推送的消息
     * @return mixed
     */
    public function notifyAllUser($alert, $message)
    {
        $client = new Client($this->appKey,$this->master_secret);

        $result = $client->push()
            ->addAllAudience() // 推送所有观众
            ->setPlatform('all')
            ->options(['apns_production'=>true])
            ->iosNotification($alert, $message)
            ->androidNotification($alert, $message)
            ->send();

        return Common::json_array($result);
    }
    /**
     * 向特定设备推送消息
     * @param $regid   接收推送的设备标识
     * @param $alert   消息标题
     * @param $message 需要推送的消息体
     * @return mixed
     */
    public function sendNotifySpecial($regid, $alert, $message,$appkey,$secret)
    {
        $client = new Client($appkey,$secret);

        $result = $client->push()
            ->setPlatform('all')
            ->options(['apns_production'=>true])
            ->addRegistrationId($regid)
            ->iosNotification($alert, $message)
            ->androidNotification($alert, $message)

            ->send();

        return Common::json_array($result);
    }
    /**
     * 创建一条定时推送消息
     * @param $alert 消息标题
     * @param $message 需要推送的消息体
     * @param $time    发送时间
     * @param $regid   接收推送的设备标识
     * @return array
     */
    public function sendSingleSchedule($alert,$message,$time,$regid)
    {
        $client = new Client($this->appKey,$this->master_secret);

        $payload = $client->push()
            ->addRegistrationId($regid)
            ->setPlatform("all")
            ->setNotificationAlert($message)
            ->build();


        // 创建一个2016-12-22 13:45:00触发的定时任务
        $response = $client->schedule()->createSingleSchedule($alert, $payload, array("time" => $time));

        return $response;
    }
    /**
     * 获取定时推送消息
     * @param $schedule_id
     * @return mixed
     */
    public function sendDataSingleSchedule($schedule_id)
    {
        $client = new Client($this->appKey,$this->master_secret);
        $result = $client->schedule()->getSchedule($schedule_id);

        return Common::json_array($result);
    }
    /**
     * 删除一条定时推送消息
     * @param $schedule_id
     * @return array
     */
    public function delSingleSchedule($schedule_id)
    {
        $client = new Client($this->appKey,$this->master_secret);

        // 创建一个2016-12-22 13:45:00触发的定时任务
        $payload = $client->schedule()->deleteSchedule($schedule_id);

        return $payload;
    }
}