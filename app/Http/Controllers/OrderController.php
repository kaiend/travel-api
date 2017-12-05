<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/20
 * Time: 13:32
 */

namespace App\Http\Controllers;


use App\Helpers\Common;
use App\Helpers\ReturnMessage;
use App\Http\Validators\OrderValidator;
use App\Models\Hotel;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends  Controller
{
    /**
     * APP订单列表
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
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
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                        ['status','=',1],
                        ['user_id','=',$id],
                    ])->get();

                    break;
                case 'doing':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('user_id','=',$id)
                        ->whereIn('status', [2,3,4,5,6,7,8])
                        ->get();
                    break;
                case 'done' :
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('user_id','=',$id )
                        ->whereIn('status', [0,9])
                        ->get();
                    break;
                default :
                    return ReturnMessage::success('订单类型未知' , '1006');
            }
            $bdata=json_decode(json_encode($data),true);

            if( count($bdata) != 0){
                $final=ReturnMessage::toString($bdata);

                return ReturnMessage::successData($final);

            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }
    /**
     * APP酒店订单列表
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function getHotelList( Request $request)
    {
        //获取订单的类型type
        $arr =$request->all();
        try{
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            $user_data = Hotel::getUserFirst($id);
            //酒店id
            $hid =  $user_data['hotel_id'];
            if( empty($arr['type']) ){
                return ReturnMessage::success('缺少订单参数' , '1005');
            }
            $t = time();
            $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
            $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
            switch ($arr['type']){
                case 'wait':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['status','=',10],
                            ['hotel_id','=',$hid],
                        ])->get();

                    break;
                case 'doing':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('hotel_id','=',$hid)
                        ->whereBetween('create_at', [$start,$end])
                        ->get();
                    break;
                case 'done' :
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('hotel_id','=',$hid )
                        ->whereIn('status', [0,9])
                        ->get();
                    break;
                default :
                    return ReturnMessage::success('订单类型未知' , '1006');
            }
            $bdata=json_decode(json_encode($data),true);

            if( count($bdata) != 0){
                $final=ReturnMessage::toString($bdata);

                return ReturnMessage::successData($final);

            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }
    /**
     * APP 取消订单
     * @param $id
     * @return \App\Helpers\json
     */
    public function cancelOrder( $id )
    {
        $id=intval($id);
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
    /**
     * APP订单详情
     * @param $id
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function getDetail( $id )
    {
        $id = intval( $id ) ;
        try {
            JWTAuth::parseToken()->getPayload();
            $data=DB::table('order')
                ->where([
                    ['id',$id],
                    ['judgment',1]
                ])
                ->first();

            $bdata=json_decode(json_encode($data),true);
            if( count($bdata) != 0){

                $bdata['appointment'] = date('Y-m-d H:i',$bdata['appointment']);
                $bdata['created_at'] = date('Y-m-d H:i',$bdata['created_at']);
                //获取所属服务
                $data_to = DB::table('server_item')->where('id',$bdata['type'])->first();
                $bdata_to=json_decode(json_encode($data_to),true);
                $bdata['server_title'] = $bdata_to['name'];
                //判断是否有扩展字段
               if($bdata_to['type'] !== 'null' && !empty($bdata_to)) {

                    $type_name = json_decode($bdata_to['type_name']);
                    $field_name = json_decode($bdata_to['field_name']);

                    $data_way= [];
                    foreach (  $field_name as $k => $v) {

                        $data_way[$k]['title']= $type_name[$k];
                        $data_way[$k]['content'] = DB::table('way_to')->where('order_id',$bdata['id'])->where('name',$v)->value('content');
                        $data_way[$k]['content'] = json_decode($data_way[$k]['content'])[0];
                    }

                }

                $b = $bdata['car_id'];
                $bdata['car_series'] = Config::get('order.car_series.'.$b);

               return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => [
                        'formal'=> [$bdata],
                        'extra' => $data_way
                    ]
               ]);

            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }
        }catch (JWTException $e){
                return ReturnMessage::success('非法token' ,'1009');
        }
    }
    /**
     * App订单搜索
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function searchOrder( Request $request )
    {
        $arr =$request ->only('type','start','end','orders_name','order_number','room_number');

        try {
            JWTAuth::parseToken()->getPayload();
            $handle = DB::table('order');
            $where = [
                ['judgment',1]
            ];
           foreach( $arr as $k =>$v){
              if( $v ){
                  $where[$k] = $v;
              }
           }
           unset($where['start']);
           unset($where['end']);

           $start =intval( $arr['start'] );
           $end =  intval( $arr['end'] );
           if( !empty( $start ) && !empty( $end )){
               $data =$handle
                   ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                   ->where($where)
                   ->whereBetween('appointment', [$start, $end])->get();
           }else{

               $data =$handle
                   ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                   ->where($where)
                   ->get();
           }
            $bdata=json_decode(json_encode($data),true);

            if( count($bdata) != 0){
                $final=ReturnMessage::toString($bdata);

                return ReturnMessage::successData($final);

            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
        
    }
    /**
     * App 用车--特殊路线列表
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public  function  showList()
    {
        //$arr = $request->only('id');
        $id =30;
        try {
            JWTAuth::parseToken()->getPayload();
            //查询该一级服务下的服务详情
            $data = DB::table('server_item')
                ->select('id','parent_id','name','picture','field_name','content')
                ->where( 'parent_id',$id)
                ->get();
            $bdata=json_decode(json_encode($data),true);

            if( count($bdata) != 0){
                foreach( $bdata as $k=>$v) {
                    $bdata[$k]['field_name'] = json_decode($v['field_name']);
                    $bdata[$k]['content'] = json_decode($v['content']);
                    $bdata[$k]['extra']=array_combine($bdata[$k]['field_name'],$bdata[$k]['content']);
                    unset($bdata[$k]['field_name']);
                    unset($bdata[$k]['content']);
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
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
    }
    /**
     * 特殊线路详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getSpecial( $id )
    {
        $id=intval($id);
        try {
            JWTAuth::parseToken()->getPayload();

            $data = DB::table('server_item')
                ->where('id',$id)
                ->select('id','parent_id','name','picture','field_name','content')
                ->first();
            $bdata=json_decode(json_encode($data),true);
            $a=$bdata['field_name'];
            $b=$bdata['content'];
            $a_last =rtrim(ltrim($a,'['),']');
            $arr1= explode(',',$a_last);
            $last= [];
            foreach( $arr1 as $k=>$v){
                $v =rtrim(ltrim($v,'"'),'"');
                $last[]=$v;
            }
            $b_last =rtrim(ltrim($b,'['),']');
            $arr2= explode(',',$b_last);
            $lastb= [];
            foreach( $arr2 as $kk=>$vv){
                $vv =rtrim(ltrim($vv,'"'),'"');
                $lastb[]=$vv;
            }
            $arr3 = array_combine($last, $lastb);
            $res = array_merge($arr3,$bdata);
            unset($res['field_name']);
            unset($res['content']);
            if( count($bdata) != 0){

                return ReturnMessage::successData([$res]);
            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
    }
    /**
     * 特殊路线下单
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function sendSpecial( Request $request )
    {
        $arr = OrderValidator::sendSpecial($request);
        try{
            $user=JWTAuth::parseToken()->getPayload();

            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);

            //查询
            $re = DB::table('order')->insert(
                [
                    'appointment' => $arr['time'],
                    'passenger_name' => $arr['name'],
                    'passenger_phone' => $arr['phone'],
                    'passenger_people' => $arr['people'],
                    'room_number' => $arr['room_number'],
                    'order_number' =>Common::createNumber(),
                    'remarks' => $arr['remarks'],
                    'car_id'  => $arr['car_id'],
                    'created_at'  =>time(),
                    'end' => $arr['end'],
                    'origin' => $arr['origin'],
                    'origin_position' => $arr['origin_position'],
                    'price' => $arr['price'],
                    'type' =>  $arr['type'],
                    'orders_name' => $user_data['name'],
                    'orders_phone' => $user_data['mobile'],
                    'user_id' =>$user_data['id'],
                    'hotel_id'  =>$user_data['hotel_id'],
                    'judgment' => 1,
                    'bottom_number' =>$arr['hotel_number']

                ]
            );
            if($re){
                return ReturnMessage::success();
            }else{
                return ReturnMessage::success( '失败','1011');
            }

        }catch( JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }
    /**
     * APP 按时包车---套餐
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function getPackage(  )
    {
       $id = 21;
        try {
            JWTAuth::parseToken()->getPayload();
            //查询该一级服务下的服务详情
            $data = DB::table('server_item')
                ->select('id', 'parent_id', 'name')
                ->where('parent_id', $id)
                ->get();

            $bdata = json_decode(json_encode($data), true);

            if (count($bdata) != 0) {
                $final = ReturnMessage::toString($bdata);

                return ReturnMessage::successData($final);

            } else {
                return response()->json([
                    'code' => '1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }
        } catch (JWTException $e) {
            return ReturnMessage::success('非法token', '1009');
        }
    }
    /**
     * APP 按时包车---下单
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function sendPackage( Request $request )
    {
        $arr = OrderValidator::sendPackage( $request );

        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);

            //插入数据
            $re = DB::table('order')->insert(
                [
                    'appointment' => $arr['time'],
                    'passenger_name' => $arr['name'],
                    'passenger_phone' => $arr['phone'],
                    'passenger_people' => $arr['people'],
                    'room_number' => $arr['room_number'],
                    'order_number' =>Common::createNumber(),
                    'remarks' => $arr['remarks'],
                    'car_id'  => $arr['car_id'],
                    'created_at'  =>time(),
                    'end' => '',
                    'origin' => $arr['origin'],
                    'end_position' => $arr['end_position'],
                    'origin_position' => $arr['origin_position'],
                    'price' => $arr['price'],
                    'type' =>  $arr['type'],
                    'orders_name' => $user_data['name'],
                    'orders_phone' => $user_data['mobile'],
                    'user_id' =>$user_data['id'],
                    'hotel_id'  =>$user_data['hotel_id'],
                    'judgment' => 1,
                    'bottom_number' =>$arr['hotel_number']
                ]
            );
            if($re){
                return ReturnMessage::success();
            }else{
                return ReturnMessage::success( '失败','1011');
            }

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }
    /**
     * 接机
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse
     */
    public function getFlight( Request $request)
    {
        $t = $request->all();
        $type = intval($t['type']);
        switch ($type ){
            case 26:
                $arr = OrderValidator::getFlight($request);break;
            case 27:
                $arr = OrderValidator::sendFlight($request);break;
            default:
                return ReturnMessage::success('失败','1011');
        }
        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
    //        //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);

            DB::beginTransaction();
            try{
                //插入基础数据
                $id= DB::table('order')->insertGetId(
                    [
                        'appointment' => $arr['time'],
                        'passenger_name' => $arr['name'],
                        'passenger_phone' => $arr['phone'],
                        'passenger_people' => $arr['people'],
                        'room_number' => $arr['room_number'],
                        'order_number' =>Common::createNumber(),
                        'remarks' => $arr['remarks'],
                        'car_id'  => $arr['car_id'],
                        'created_at'  =>time(),
                        'end' => $arr['end'],
                        'origin' => $arr['origin'],
                        'end_position' => $arr['end_position'],
                        'origin_position' => $arr['origin_position'],
                        'price' => $arr['price'],
                        'type' =>  $type,
                        'orders_name' => $user_data['name'],
                        'orders_phone' => $user_data['mobile'],
                        'user_id' =>$user_data['id'],
                        'hotel_id'  =>$user_data['hotel_id'],
                        'judgment' => 1,
                        'bottom_number' =>$arr['hotel_number']
                    ]
                );
                //插入展字段
                $field =DB::table('server_item')->where('id',$type) ->value('field_name');
                $field_mame = json_decode($field);
                foreach(  $field_mame as $k =>$v){
                    DB::table('way_to') ->insert([
                        'order_id' =>$id,
                        'name' =>$v,
                        'content' =>json_encode([$arr[$v]])
                    ]);
                }
              DB::commit();
                return ReturnMessage::success();
            }catch (\Exception $e){
                DB::rollback();
                return response()->json([
                    'code' => $e->getCode(),
                    'info' => $e->getMessage(),
                ]);
            }
        } catch (JWTException $e) {
            return ReturnMessage::success('非法token', '1009');
        }

    }
    /**
     * 接送站
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse
     */
    public function getTrain( Request $request)
    {
        $arr = OrderValidator::takeTrain($request);
        $type = intval($arr['type']);
        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);

            DB::beginTransaction();
            try{
                //插入基础数据
                $id= DB::table('order')->insertGetId(
                    [
                        'appointment' => $arr['time'],
                        'passenger_name' => $arr['name'],
                        'passenger_phone' => $arr['phone'],
                        'passenger_people' => $arr['people'],
                        'room_number' => $arr['room_number'],
                        'order_number' =>Common::createNumber(),
                        'remarks' => $arr['remarks'],
                        'car_id'  => $arr['car_id'],
                        'created_at'  =>time(),
                        'end' => $arr['end'],
                        'origin' => $arr['origin'],
                        'end_position' => $arr['end_position'],
                        'origin_position' => $arr['origin_position'],
                        'price' => $arr['price'],
                        'type' =>$arr['type'],
                        'orders_name' => $user_data['name'],
                        'orders_phone' => $user_data['mobile'],
                        'user_id' =>$user_data['id'],
                        'hotel_id'  =>$user_data['hotel_id'],
                        'judgment' => 1,
                        'bottom_number' =>$arr['hotel_number']
                    ]
                );
                //插入展字段
                $field =DB::table('server_item')->where('id',$type) ->value('field_name');
                $field_mame = json_decode($field);
                foreach(  $field_mame as $k =>$v){
                    DB::table('way_to') ->insert([
                        'order_id' =>$id,
                        'name' =>$v,
                        'content' =>json_encode([$arr[$v]])
                    ]);
                }
                DB::commit();
                return ReturnMessage::success();
            }catch (\Exception $e){
                DB::rollback();
                return response()->json([
                    'code' => $e->getCode(),
                    'info' => $e->getMessage(),
                ]);
            }
        } catch (JWTException $e) {
            return ReturnMessage::success('非法token', '1009');
        }
    }
    /**
     * 追加订单
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function makeExtra( Request $request)
    {
        $arr = OrderValidator::makeExtra($request);

        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
             //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);
            $type = intval($arr['type']);
            if( $type == 21){
                $re = DB::table('extra_order')->insert([
                    'order_number' =>$arr['order_number'],
                    'remarks' => $arr['remarks'],
                    'car_id'  => $arr['car_id'],
                    'created_at'  =>time(),
                    'end' => $arr['end'],
                    'hours' => $arr ['hours'],
                    'origin' => $arr['origin'],
                    'bottom_number' => $arr['bottom_number'],
                    'end_position' => $arr['end_position'],
                    'origin_position' => $arr['origin_position'],
                    'user_id' =>$user_data['id'],
                    'hotel_id'  =>$user_data['hotel_id'],
                    'judgment' => 1
                ]);

            }else{
                $re = DB::table('extra_order')->insert([
                    'order_number' =>$arr['order_number'],
                    'remarks' => $arr['remarks'],
                    'car_id'  => $arr['car_id'],
                    'created_at'  =>time(),
                    'end' => $arr['end'],
                    'origin' => $arr['origin'],
                    'end_position' => $arr['end_position'],
                    'origin_position' => $arr['origin_position'],
                    'user_id' =>$user_data['id'],
                    'hotel_id'  =>$user_data['hotel_id'],
                    'judgment' => 1,
                    'bottom_number' => $arr['bottom_number'],
                ]);
            }

            if( $re ){
                return ReturnMessage::success();
            }else{
                return ReturnMessage::success('失败','1011');
            }

        } catch (JWTException $e) {
            return ReturnMessage::success('非法token', '1009');
        }

    }

}