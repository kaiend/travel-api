<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/20
 * Time: 13:32
 */

namespace App\Http\Controllers;


use App\Helpers\ReturnMessage;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends  Controller
{
    //APP订单列表
    public function getList( Request $request)
    {
       //获取订单的类型type
        $arr =$request->all();
        try{
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            if( empty($arr['type']) ){
                return ReturnMessage::success('缺少订单参数' , '1005');
            }
            switch ($arr['type']){
                case 'wait':
                    $data = DB::table('order')->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment')->where([
                        ['status'=>'1'],
                        ['user_id'=>$id],
                    ])->get();

                    break;
                case 'doing':
                    $data = DB::table('order')->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment')
                        ->where(['user_id'=>$id])
                        ->whereIn('status', [2,3,4,5,6,7,8])
                        ->get();
                    break;
                case 'done' :
                    $data = DB::table('order')->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment')
                        ->where(['user_id' =>$id ])
                        ->whereIn('status', [0,9])
                        ->get();
                    break;
                default :
                    return ReturnMessage::success('订单类型未知' , '1006');
            }
            if(!empty($data)){
                $bdata=json_decode(json_encode($data),true);

                $final=ReturnMessage::toString($bdata);

                return ReturnMessage::successData($final);
            }else{
                return ReturnMessage::success('没有订单' ,'1008');
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }

    //APP 取消订单
    public function cancelOrder( $id )
    {
        intval($id);
        $re = DB::table('order')->where('id',$id)->value('user_id');
        try{
            $user = JWTAuth::parseToken()->getPayload();
            $uid = $user['foo'];
            if( $re ==$uid ){
                $data = DB::table('order')->where('id',$id)->update(['status' => 0 ]);
                if( $data == 0){
                    return ReturnMessage::success('订单取消失败，请重试', '1007');
                }
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

        return  ReturnMessage::success('订单取消成功', '1000');
    }

}