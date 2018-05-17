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
                $bdata[$k]['image']='http://travel.shidaichuxing.com/upload/'.$bdata[$k]['image'];
            }

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