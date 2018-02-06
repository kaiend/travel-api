<?php

namespace App\Http\Controllers\Wechat;

use App\Helpers\Common;
use App\Helpers\ReturnMessage;
use App\Http\Controllers\Controller;
use App\Helpers\Sms;
use App\Models\CarSeries;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
	/**
	 * 服务列表
	 * */
//	private function serviceList()
	public function serviceList()
	{
		return Service::getServiceList();
	}
	/**
	 * 车系列表
	 * */
	//	private function serviceList()
	public function carSeriesList()
	{
		return CarSeries::getCarSeriesList();
	}
    /**
     * 测距
     * @param \Illuminate\Http\Request $request
     * @return bool|mixed
     */
	public function getDistance(Request $request)
    {
        $arr =$request->only('origins','destinations');
        $url ='http://api.map.baidu.com/routematrix/v2/driving?output=json&origins='.$arr['origins'].'&destinations='.$arr['destinations'].'&ak=RGwhFRkSZfva32BN96csoObm4FIfiCAY';
        function getUrl($url, $timeout = 5)
        {
            $url = str_replace("&amp;", "&", urldecode(trim($url)));
            //$cookie = tempnam ("/tmp", "CURLCOOKIE");
            $ch = curl_init();
            //curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
            curl_setopt($ch, CURLOPT_URL, $url);
            //curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $content = curl_exec($ch);
            $response = curl_getinfo($ch);
            if ($response['http_code'] != 200) {
                return false;
            }
            return $content;
        }
        return getUrl($url);
    }
    /**
     * 派车后发送短信
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function sendMessage(Request $request)
    {
        $arr =$request->only('oid');
        $id =$arr['oid'];
        $odata =Order::getOrderFirst(['id'=>$id]);
        $odata =json_decode(json_encode($odata),true);
        $msg='<pre>';
        $msg .="时代出行欢迎您
您好，阁下预定的贵宾车资料如下：
接载客人：".$odata['passenger_name']."
联络电话：".$odata['passenger_phone']."
接载日期：".date('m-d ',$odata['appointment'])."
接载时间：".date('h:1 ',$odata['appointment'])."
接载地点：".$odata['origin']."
途径地点：
终点：".$odata['end']."
贵宾车车号：(".$odata['car_number'].")
司机姓名：".$odata['chauffeur_name'].
"司机电话：".$odata['chauffeur_phone'].
"如有任何疑问，欢迎致电62818018，谢谢！
祝您旅途愉快!【时代出行】";
        $msg .='</pre>';
        $phone =$odata['passenger_phone'];
        return (new Sms())->sendSMS($phone,$msg);
    }
    /**
     * 订单审核状态查询
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse
     */
    public function getAudit(Request $request)
    {
        $arr =$request->only('phone');
        if(!isset($arr['phone'])){
            return ReturnMessage::success('失败','1001');
        }
        $user_data=DB::table('personal_user')
            ->join('card_audit','personal_user.id','=','card_audit.uid')
            ->where('phone',$arr['phone'])
            ->orderBy('card_audit.id','desc')
            ->first();
        $user_data=Common::json_array($user_data);
        if($user_data){
            return response()->json([
                'code' =>'1000',
                'info' => 'success',
                'data' => [
                    'status'   =>$user_data['card_audit_status'],
                    'message'  =>$user_data['content']
                ]
            ]);
        }else{
            //用户存在，但是未提交出行卡审核
            return response()->json([
                'code' =>'1000',
                'info' => 'success',
                'data' =>[
                    'status'=>3,
                    'message' =>''
                ]
            ]);
        }

    }
    /**
     * 通知用户后，改变审核状态
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function changeStatus(Request $request){
        $arr =$request->only('phone');
        if(!isset($arr['phone'])){
            return ReturnMessage::success('失败','1001');
        }
        $user_data=DB::table('personal_user')->where('phone',$arr['phone'])->first();
        $user_data=Common::json_array($user_data);
        if($user_data['card_audit_status'] == 1){
            $re =DB::table('personal_user')->where('phone',$arr['phone'])->update(['card_audit_status'=>3]);
            if($re){
                DB::table('card_audit')->insert([
                    'card_audit_status'=>3,
                    'uid' =>$user_data['id'],
                    'create_time' =>time(),
                    'content' => '已通知用户'
                ]);
                return ReturnMessage::success();
            }else{
                return ReturnMessage::success('失败','1001');
            }
        }
    }

    /*
     * 用户优惠券
     */
    public function coupon(Request $request){
        $res = $request->only('user_id');
        if($res){

        }else{
            return ReturnMessage::success('参数不能为空','1003');
        }

    }

    /*
     * 服务类型下的车系
     */
    public function typeCar(Request $request){
        $par = $request->only('type');
        $data = DB::table('personal_charges_rule')
            ->join('car_series','personal_charges_rule.cars_id','=','car_series.id')
            ->where('personal_charges_rule.service_id',$par['type'])
            ->select('type','price','cars_id','service_id','series_name','image','status','parent_id','market_price')
            ->distinct('personal_charges_rule.cars_id')
            ->get();
        $data = Common::json_array($data);
        if($data){
            return ReturnMessage::successData($data);
        }else{
            return ReturnMessage::success('暂无数据','1001');
        }
    }

}