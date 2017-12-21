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
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use JPush\Client;

class PushController extends Controller
{
    private $appKey ='50505e64af2ea4b5e8e27e26';
    private $master_secret ='f90b3ccdce62056bb134aaaf';

    public function pushStatus( Request $request )
    {
        $arr =$request->only('order_id','type');
        //查询
        $order_data =DB::table('order')->where('id',$arr['order_id'])->first();
        $bdata = Common::json_array($order_data);
        $cid = $bdata['hotel_id'];
        $uid = $bdata['user_id'];
        $status= $bdata['status'];
        $config =Config::get('order.trace');
        $alert='订单号:'.$bdata['order_number'].'---'.$config[$status];
        switch($arr['type']){
            //下单通知管理员
            case 'make':
                $cdata = DB::table('hotel_user')
                    ->where([
                        ['hotel_id',$cid],
                        ['type',2]
                    ])
                    ->get();
                $cdata =Common::json_array( $cdata );
                if( $cdata ){
                    foreach( $cdata as $k=>$v){
                        $regid = $v['jpush_code'];
                        $message =[
                            "extras" => array(
                                "status" => $bdata['status'],
                            )
                        ];
                        $result =$this ->sendNotifySpecial($regid,$alert,$message);
                        if( $result['http_code']){
                            return ReturnMessage::success();
                        }else{
                            return ReturnMessage::success('失败','1011');
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
                $result =$this ->sendNotifySpecial($regid,$alert,$message);
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
    public function sendNotifySpecial($regid, $alert, $message)
    {
        $client = new Client($this->appKey,$this->master_secret);

        $result = $client->push()
            ->setPlatform('all')
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