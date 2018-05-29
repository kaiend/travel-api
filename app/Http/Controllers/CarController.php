<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 9:58
 */

namespace App\Http\Controllers;


use App\Helpers\ReturnMessage;
use App\Http\Validators\CarValidator;
use App\Models\Hotel;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helpers\Sms;

class CarController extends Controller
{
    /**
     * 车系接口
     * @return mixed
     */
    public function getSeries(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = intval($user['foo']);
            $arr =$request->only('type');
            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);
            $hid =$user_data['hotel_id'];
            $cdata =DB::table('server_car')
                ->join('car_series','server_car.series_id','car_series.id')
                ->where([
                    ['company_id',$hid],
                    ['item_id',$arr['type']]
                ])
                ->pluck('parent_id');
            $data = DB::table('car_series')
                ->where([
                    ['parent_id' , 0 ],
                    ['status',1]
                ])
                ->whereIn('id',$cdata)
                ->select("id","series_name")
                ->get();
            $bdata = json_decode(json_encode($data),true);
            if(count($bdata) != 0){
                return ReturnMessage::successData($bdata);
            }else{
                return response()->json([
                    'code' => '1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }
        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }

    /**
     * 车辆详情
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getCars( $id, Request $request )
    {

        $arr = CarValidator::userCar($request);

        $data = DB::table('charges_rule')
            ->join('car_series','charges_rule.cars_id','=','car_series.id')
            ->where('charges_rule.service_id',$arr['type'])
            ->select('type','price','cars_id','service_id','series_name','image','status','parent_id','market_price')
            ->where([
                ['hotel_id',$arr['hotel_id']],
                ['car_series.parent_id',$id],
                ['charges_rule.service_type',$arr['service_type']]
            ])
            ->distinct('charges_rule.cars_id')
            ->get();
            //dd($data);
        $bdata=json_decode(json_encode($data),true);

        if( count($bdata) != 0){
            foreach( $bdata as $k=>$v){
                if(!is_null($v['market_price'])){
                    $bdata[$k]['price'] = $v['market_price'];
                }
                unset($bdata[$k]['market_price']);
                $bdata[$k]['image'] = 'http://travel.shidaichuxing.com/upload/'.$bdata[$k]['image'];

                $price = $this->fee2($arr['origins'],$arr['destinations'],$arr['usetime'],$v['cars_id'],$arr['type'],$arr['hotel_id'],$arr['service_type']);
                $bdata[$k]['price'] = $price['price'];
            }
            print_r($bdata);exit;
            $final=ReturnMessage::toString($bdata);
            return ReturnMessage::successData($final);

        }else{
            return response()->json([
                'code' =>'1000',
                'info' => 'success',
                'data' => []
            ]);
        }

    }

    /**
     * @param  [type] $origins      array,起点经纬度，[纬，经]
     * @param  [type] $destinations array,终点经纬度，[纬，经]
     * @param  [type] $usetime      用车时间
     * @param  [type] $carid        车系id
     * @param  [type] $serid        服务id
     * @param  [type] $hotel_id     酒店id
     * @param  [type] $service_type 内部服务or协议服务
     */
    public function fee2( $origins, $destinations, $usetime, $carid, $serid, $hotel_id, $service_type)
    {
        $ori = explode(',', $origins);
        $des = explode(',', $destinations);
        $ori = $ori[1].','.$ori[0];
        $des = $des[1].','.$des[0];
        $url = 'http://api.map.baidu.com/routematrix/v2/driving?output=json&origins=' . $ori . '&destinations=' . $des . '&ak=RGwhFRkSZfva32BN96csoObm4FIfiCAY';
        // return  $url;
        $result = (new Sms)->get_curl_json($url);

        $time = round($result['result'][0]['duration']['value'] / 60);//时间
        $dis = round($result['result'][0]['distance']['value'], 1);//距离
        $return = [];
        $price = 0;
        $re = Db::table('charges_rule')
            ->where([
                ['cars_id',$carid],
                ['service_id', $serid],
                ['hotel_id', $hotel_id],
                ['service_type', $service_type],
            ])->first();
        $re=json_decode(json_encode($re),true);
        //获取该酒店的免费里程数
        if($service_type == 2){
            $hotel_fee = Db::table('hotel_fee')->where('company_id', $hotel_id)->first();
            $hotel_fee = json_decode(json_encode($hotel_fee),true);
            //如果该酒店的免费里程小于该距离则往下执行，否则价格返回0
            if($hotel_fee['free_mileage_compute'] >= $dis){
//            $free_mileage_compute = $hotel_fee['free_mileage_compute'] - $dis;
//            Db::table('hotel_fee')->where('company_id', $hotel_id)->update(['free_mileage_compute' => $free_mileage_compute]);
                return $return['price'] = 0;
            }
        }
        if (!empty($re)) {
            $difdis = $dis - $re['basis_km'];//实际距离与后台设置距离差
            if($difdis > 0){//实际距离大
                $difdis2 = $dis - $re['super_km_km'];//超过多少公里以后
                if($difdis2 < $re['super_km_shortage_km']){//不做多少公里不计费
                    $price = $re['price'];
                }else{//计算白天和夜间的超公里收费
                    $usetime1 = explode(' ',$usetime);
                    $usetime2 = strtotime($usetime);
                    $day_start_time = strtotime($usetime1[0] . $re['day_start_time']);
                    $day_end_time = strtotime($usetime1[0] . $re['day_end_time']);
                    if(!empty($re['super_km_per_km'])){
                        $avgdis = $difdis2 / $re['super_km_per_km'];
                    }
                    if($day_start_time < $usetime2 && $usetime2 < $day_end_time){
                        if(!empty($re['day_commission'])){
                            $price = $re['price'] + $avgdis * $re['day_commission'];
                        }
                    }else{
                        if(!empty($re['night_commission'])) {
                            $price = $re['price'] + $avgdis * $re['night_commission'];
                        }
                    }
                }
            }else{//实际距离小
                $price = $re['price'];
            }
            $diftime = $time - $re['basis_time'];

            if($diftime > 0){//超时
                $diftime2 = $time - $re['starting_starting_time'];//起步后多少分钟内不收费
                if($diftime2 < 0){
                    $price = $price;
                }else{
                    $diftime3 = $diftime2 - $re['starting_shortage_time'];
                    if($diftime3 < 0){
                        $price = $price;
                    }else{
                        if($re['starting_exceed_time'] > 0){
                            $avgtime = $diftime3 / $re['starting_exceed_time'];
                        }else{
                            $avgtime = $diftime3;
                        }
                        $price = $price + $avgtime * $re['starting_add_money'];
                    }
                }
            }else{//不超时
                $price = $price;
            }
            $return['price'] = $price;
        } else {
            $return['price'] = 0;
            $return['msg'] = '没有该服务';
        }
        return $return;
    }

    public function getSerie( Request $request )
    {
        $arr = $request ->all();
        $type = $arr['type'];

        $item_data = DB::table('car_series')
            ->select('id','parent_id','series_name','image','status')
            ->where([
                ['status',1]
            ])
            ->get();

        $bdata=json_decode(json_encode($item_data ),true);
        $items = array();
        foreach( $bdata as $k=>$v){
            $items[$v['id']] = $v;
        }
        foreach($items as $item){

            if(isset($items[$item['parent_id']])){
                $items[$item['parent_id']]['son'][] = &$items[$item['id']];
            }else{
                $tree[] = &$items[$item['id']];

            }
        }
        $data = DB::table('server_car') ->where('item_id',$type) ->pluck('series_id');
        $ids=json_decode(json_encode($data ),true);
        $final_data=[];
        foreach($tree as $k1=>$v1){
            foreach( $v1['son'] as $k2 =>$v2){
                if(in_array( $v2['id'], $ids)){
                    $final_data[]=$v1;
                }
            }
        }
        return ReturnMessage::successData($final_data);

    }


}