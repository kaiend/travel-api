<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 9:58
 */

namespace App\Http\Controllers;


use App\Helpers\ReturnMessage;
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


    public function getCars( $pid )
    {

        $data = DB::table('car_series')
            ->where([
                ['parent_id' , intval( $pid ) ],
                ['status',1]
            ])
            ->select("id","series_name","sort","image","status")
            ->get();
        $bdata = json_decode(json_encode($data),true);

        if( count($bdata) != 0){
            $bdata[0]['image'] ='http://travel.shidaichuxing.com/upload/'.$bdata[0]['image'];
            $bdata[0]['fee']   = 220;
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

}