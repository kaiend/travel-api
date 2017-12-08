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
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    /**
     * 车系接口
     * @return mixed
     */
    public function getSeries()
    {
        $data = DB::table('car_series')
            ->where([
                ['parent_id' , 0 ],
                ['status',1]
            ])
            ->select("id","series_name")
            ->get();
        $bdata = json_decode(json_encode($data),true);

        return ReturnMessage::successData($bdata);
    }

    public function getCars( $id, Request $request )
    {

        $arr = CarValidator::userCar($request);

        $data = DB::table('charges_rule')
            ->join('car_series','charges_rule.cars_id','=','car_series.id')
            ->where('charges_rule.service_id',$arr['type'])
            ->select('type','price','cars_id','service_id','series_name','image','status','parent_id')
            ->where([
                ['hotel_id',$arr['hotel_id']],
                ['car_series.parent_id',$id]
            ])
            ->get();
        $bdata=json_decode(json_encode($data),true);

        if( count($bdata) != 0){
            $bdata[0]['image'] ='http://travel.shidaichuxing.com/upload/'.$bdata[0]['image'];
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