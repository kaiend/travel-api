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
use App\Models\Chauffeur;
use App\Models\Hotel;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use JPush\Client;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends  Controller
{
    /**
     * APP我的订单列表
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
                //全部订单
                case 'all':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['user_id','=',$id],
                        ])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //待执行
                case 'wait':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['user_id','=',$id],
                        ])
                        ->whereIn('status', [2,3,4])
                        ->orderBy('id','desc')
                        ->get();

                    break;
                //执行中
                case 'doing':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('user_id','=',$id)
                        ->whereIn('status', [5,6,7,8])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //取消订单
                case 'cancel':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['status','=',0],
                            ['user_id','=',$id]
                        ])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //待审核
                case 'undo':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['status','=',10],
                            ['user_id','=',$id]
                        ])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //历史订单
                case 'done' :
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['status','=',9],
                            ['user_id','=',$id]
                        ])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //最近订单
                case 'addToday':
                    $data =DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('user_id','=',$id)
                        ->orderBy('id','desc')
                        ->limit(10)
                        ->get();
                    break;
                //已改派
                case 'reassigned':
                    $data = DB::table('order')
                        ->join('order_information',function($join){
                            $join->on('order.order_number','=','order_information.order_number')
                                ->where('order_information.title','=','更换司机');
                        })
                        ->where('order.user_id','=',$id)
                        ->select('order.id','order.end','order.origin','order.type','order.orders_name','order.orders_phone','order.order_number','order.created_at','order.appointment','order.status','order.bottom_number')
                        ->get();
                    break;
                default :
                    return ReturnMessage::success('订单类型未知' , '1006');
            }
            $type_data =Config::get('order.type');
            $status_name =Config::get('order.status_name');
            //待审核
            $count =DB::table('order')
                ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                ->where([
                    ['user_id','=',$id],
                    ['status','=',10]
                ])
                ->count();

            $bdata=json_decode(json_encode($data),true);

            if( count($bdata) != 0){
                foreach( $bdata as $k=>$v) {
                    //添加是否取消字段
                    if(in_array($v['status'],[1,2,3,4,5,6])){
                        $bdata[$k]['cancel'] =1;
                    }else{
                        $bdata[$k]['cancel'] =0;
                    }
                    $bdata[$k]['status_name'] = $status_name[$v['status']];
                    $bdata[$k]['type_name'] = $type_data[$v['type']];
                }
                $final=ReturnMessage::toString($bdata);

                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'count'=>"$count",
                    'data' => $final,
                ]);
            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'count'=>"$count",
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

            if( !isset($arr['type']) ){
                return ReturnMessage::success('缺少订单参数' , '1005');
            }

            $t = time();
            $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
            $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
            switch ($arr['type']){
                //全部订单
                case 'all':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('hotel_id','=',$hid)
                        ->whereIn('status', [0,1,2,3,4,5,6,7,8,9,10])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //今日新增
                case 'today':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('hotel_id','=',$hid)
                        ->whereIn('status', [0,1,2,3,4,5,6,7,8,9,10])
                        ->whereBetween('created_at',[$start,$end])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //待审核
                case 'undo':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['status','=',10],
                            ['hotel_id','=',$hid],
                        ])
                        ->orderBy('id','desc')
                        ->get();

                    break;
                //待执行
                case 'wait':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('hotel_id','=',$hid)
                        ->whereIn('status', [2,3,4])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //执行中
                case 'doing':
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('hotel_id','=',$hid)
                        ->whereIn('status', [5,6,7,8])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //历史订单
                case 'done' :
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['status','=',9],
                            ['hotel_id','=',$hid]
                        ])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //取消订单
                case 'cancel' :
                    $data = DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where([
                            ['hotel_id','=',$hid],
                            ['status','=',0]
                        ] )
                        ->orderBy('id','desc')
                        ->get();
                    break;
                //最近订单
                case 'addToday':
                    //今日新增
                    $data =DB::table('order')
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                        ->where('hotel_id','=',$hid)
                        ->whereIn('status', [0,1,2,3,4,5,6,7,8,9,10])
                        ->whereBetween('created_at',[$start,$end])
                        ->orderBy('id','desc')
                        ->get();
                    break;
                default :
                    return ReturnMessage::success('订单类型未知' , '1006');
            }
            $type_data =Config::get('order.type');
            $status_name =Config::get('order.status_name');
            //待审核
            $count =DB::table('order')
                ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                ->where([
                    ['hotel_id','=',$hid],
                    ['status','=',10]
                ])
                ->count();
            $bdata=json_decode(json_encode($data),true);

            if( count($bdata) != 0){
                foreach($bdata as $k=>$v) {
                    $bdata[$k]['status_name'] = $status_name[$v['status']];
                    $bdata[$k]['type_name'] = $type_data[$v['type']];
                }

                $final=ReturnMessage::toString($bdata);
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'count'=>"$count",
                    'data' => $final,
                ]);


            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'count'=>"$count",
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
            $where=[
                'id' =>$id
            ];
            //当前订单的数据
            $order_data =Order::getOrderFirst($where);
            if( $re ==$uid ){
                $data = DB::table('order')->where('id',$id)->update(['status' => 0 ]);
                //取消订单插入一条记录
                DB::table('order_status')
                    ->insert([
                        'order_number'=>$order_data['order_number'],
                        'status'=> 0,
                        'update_time' =>time()
                    ]);
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
                ])
                ->first();
            $bdata=json_decode(json_encode($data),true);
            $hid =$bdata['hotel_id'];
            if( count($bdata) != 0){
                //添加一个详情页顶部的状态标示
                $detail_status =Config::get('order.detail_status_name');
                $bdata['status_title'] = $detail_status[$bdata['status']];
                //对应订单不同状态联系人不同
                if(in_array($bdata['status'],[0,1,2,10])){
                    $high_data=DB::table('hotel')->where('id',$hid)->first();
                    $high_data=Common::json_array($high_data);
                    //1.负责人信息
                    $concat=[
                        'name'    =>'主管: '.$high_data['principal'],
                        'picture' =>$high_data['pic'],
                        'phone_number' =>$high_data['mobile'],
                        'concat' => '联系主管',
                        'series_name' =>'',
                        'car_number'  =>'',
                        'car_corlor'  =>''
                    ];
                }else{
                    //2.司机信息
                    $chauffer_data=Chauffeur::getUserFirst($bdata['chauffeur_id']);
                    if($chauffer_data['car_id']!= 0){
                        //司机的车详细信息
                        $new_Data =DB::table('cars')
                            ->join('car_series','cars.series_id','=','car_series.id')
                            ->join('motorcade','cars.fleet_id','motorcade.id')
                            ->where('cars.chauffeur_id',$chauffer_data['id'])
                            ->join('chauffeur','cars.chauffeur_id','=','chauffeur.id')
                            ->first();
                        $new_Data=Common::json_array($new_Data);
                    }else{
                        //司机没有绑定车辆
                        $new_Data =DB::table('chauffeur')
                            ->join('motorcade','chauffeur.team_id','=','motorcade.id')
                            ->where('chauffeur.id',$chauffer_data['id'])
                            ->first();
                        $new_Data=Common::json_array($new_Data);
                        $new_Data['series_name'] ='';
                        $new_Data['car_number'] ='';
                        $new_Data['car_corlor'] ='';
                    }
                    $concat=[
                        'name'    =>'司机: '.$chauffer_data['name'],
                        'picture' =>$chauffer_data['picture'],
                        'phone_number' =>$chauffer_data['phone'],
                        'concat' => '联系司机',
                        'series_name' =>$new_Data['series_name'],
                        'car_number'  =>$new_Data['car_number'],
                        'car_corlor'  =>$new_Data['car_corlor']
                    ];
                }
                $created_time =$bdata['created_at'];
                $bdata['appointment'] = date('Y-m-d H:i',$bdata['appointment']);
                $bdata['created_at'] = date('Y-m-d H:i',$bdata['created_at']);
                //获取所属服务
                $data_to = DB::table('server_item')->where('id',$bdata['type'])->first();
                $bdata_to=json_decode(json_encode($data_to),true);
                $bdata['server_title'] = $bdata_to['name'];
                $data_way= [];
                //判断是否有扩展字段
                if($bdata_to['type'] !== 'null' && !empty($bdata_to)) {

                   $type_name = json_decode($bdata_to['type_name']);
                   $field_name = json_decode($bdata_to['field_name']);
                   $field_names =array_flip( $field_name );
                   if($bdata['type'] == 27){
                       unset($field_names['cip']);
                       unset($field_names['origin']);
                   }else if( $bdata['type'] == 26){
                       unset($field_names['cip']);
                       unset($field_names['end']);
                   }else if( $bdata['type'] == 28 ){
                       unset($field_names['end']);
                   }else if( $bdata['type'] == 29){
                       unset($field_names['origin']);
                   }else if(in_array($bdata['type'],[20,39,40,41,31,32])){
                       unset($field_names['origin']);
                       unset($field_names['end']);
                   }
                    foreach (  array_flip($field_names) as $k => $v) {

                        $data_way[$k]['title']= $type_name[$k];
                        $data_way[$k]['content'] = DB::table('way_to')->where('order_id',$bdata['id'])->where('name',$v)->value('content');
                        $data_way[$k]['content'] = json_decode($data_way[$k]['content'])[0];
                    }
                }
                //文字化某些字段
                $detail['order_number']=$bdata['order_number'];
                $detail['orders_name']=$bdata['orders_name'];
                $detail['server_title']=$bdata['server_title'];
                $detail['appointment']=$bdata['appointment'];
                $detail['passenger_name']=$bdata['passenger_name'];
                $detail['passenger_people']=$bdata['passenger_people'];
                $detail['passenger_phone']=$bdata['passenger_phone'];
                //车系文字化
                $b = $bdata['car_id'];
                $detail['car_id'] = Config::get('order.car_series.'.$b);
                //$detail['price']=$bdata['price'];
                $bdata['car_name'] = $detail['car_id'];
                $detail['bottom_number']=$bdata['bottom_number'];
                $detail['remarks']=$bdata['remarks'];


                $config = Config::get('order.detail');
                $last_data =[];
                $x=0;
                foreach( $detail as $k =>$v){

                   $last_data[$x]['title'] =$config[$k];
                   $last_data[$x]['content'] =$v;
                   $x ++;
                }
                $ff=array_merge($last_data,$data_way);
                $bdata['word'] =$ff;
                //查询订单的编号
                $oid =$bdata['order_number'];
                //查询订单的轨迹
                $trace_data = OrderStatus::getOrderTrace( $oid );
                $con = Config::get('order.trace');
                //组装数据
                $server_start=[];
                foreach($trace_data as $k =>$v){
                    $trace_data[$k]['status_name'] = $con[$v['status']] ;
                    $trace_data[$k]['status'] =$v['status'];
                    if($v['status'] == 4){
                      $server_start =$v['update_time'];
                    }
                }
                $first =[
                    "status" => 1,
                    "update_time" =>$created_time,
                    "status_name" => "下单"
                ] ;
                //向后插入一个数组
                array_push($trace_data,$first);
                $bdata['trace'] =$trace_data;
                $bdata['contact'] =$concat;
                //dd($trace_data);
                //为订单详情添加一个服务时间长度字段
                if(in_array($bdata['status'],[1,2,10])){
                    $str='已等待';
                    $time =$trace_data[0]['update_time'];
                    $re =Common::timeInterval($time,time());
                    $bdata['wait_time'] =$str.$re;
                }if($bdata['status'] == 3){
                    $bdata['wait_time']='等待服务';
                }else if($bdata['status'] == 9){
                    $str ='已服务';
                    $end =$trace_data[0]['update_time'];
                    $start =$server_start;
                    $re =Common::timeInterval($start,$end);
                    $bdata['wait_time'] =$str.$re;
                }
                $type_data =Config::get('order.type');
                $bdata['type_name'] =$type_data[$bdata['type']];
                $bdata['dispatch'] ='18510570020';
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => ReturnMessage::toString($bdata)
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
     * APP订单搜索
     * from:manage酒店订单 ,my 我的订单搜索
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function searchOrder( Request $request )
    {
        $arr =$request ->only('from','type','status','start','end','orders_name','bottom_number','room_number');

        try {
            $user =JWTAuth::parseToken()->getPayload();
            $id =$user['foo'];
            $user_data =Hotel::getUserFirst($id);
            $hid =$user_data['hotel_id'];
            if($arr['from'] == 'manage'){
                $where = [
                    ['hotel_id',$hid]
                ];
            }else{
                $where = [
                    ['user_id',$id]
                ];
            }
            $handle = DB::table('order');

            foreach( $arr as $k =>$v){
                if( $v ){
                    $where[$k] = $v;
                }
            }
            unset($where['start']);
            unset($where['end']);
            unset($where['from']);
            $start =intval( $arr['start'] );
            $end =  intval( $arr['end'] );
            if(isset($where['status'])){
                switch ($where['status']){
                    //全部
                    case '1':
                        $whereIn=[1,2,3,4,5,6,7,8,9,0,10];
                    break;
                    //待执行
                    case '2':
                        $whereIn=[2,3,4];
                    break;
                    //执行中
                    case '3':
                        $whereIn=[5,6,7,8];
                    break;
                    //已完成
                    case '4':
                        $whereIn=[9];
                        break;
                    //已取消
                    case '5':
                        $whereIn=[0];
                        break;
                    //待审核
                    case '6':
                        $whereIn=[10];
                        break;
                    default:
                        $whereIn =[];
                }
                unset($where['status']);
                if( !empty( $start ) && !empty( $end )){
                    $data =$handle
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number','room_number')
                        ->where($where)
                        ->whereBetween('appointment', [$start, $end])
                        ->whereIn('status',$whereIn)
                        ->get();
                }else{

                    $data =$handle
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number','room_number')
                        ->where($where)
                        ->whereIN('status',$whereIn)
                        ->get();
                }
            }else{
                if( !empty( $start ) && !empty( $end )){
                    $data =$handle
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number','room_number')
                        ->where($where)
                        ->whereBetween('appointment', [$start, $end])
                        ->get();
                }else{

                    $data =$handle
                        ->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number','room_number')
                        ->where($where)
                        ->get();
                }
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
            $user = JWTAuth::parseToken()->getPayload();
            $uid = $user['foo'];
            $user_data= Hotel::getUserFirst($uid);
            $hid = $user_data['hotel_id'];
            $cdata =DB::table('hotel_server')->where('hotel_id',$hid)->pluck('server_id');
            $cdata = Common::json_array( $cdata );
            //查询该一级服务下的服务详情
            $data = DB::table('server_item')
                ->select('id','parent_id','name','picture','field_name','content')
                ->where( 'parent_id',$id)
                ->whereIn('id',$cdata)
                ->get();
            $bdata=json_decode(json_encode($data),true);

            if( count($bdata) != 0){
                foreach( $bdata as $k=>$v) {
                    $bdata[$k]['field_name'] = json_decode($v['field_name']);
                    $bdata[$k]['content'] = json_decode($v['content']);
                    $bdata[$k]['extra']=array_combine($bdata[$k]['field_name'],$bdata[$k]['content']);
                    unset($bdata[$k]['field_name']);
                    unset($bdata[$k]['content']);
                    $bdata[$k]['picture'] ='http://travel.shidaichuxing.com/upload/'.$bdata[$k]['picture'];
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
     * 判断下单时订单的状态
     * @param array $user_data
     * @return int
     */
    private function  orderStatus(array $user_data)
    {
        $hid =$user_data['hotel_id'];
        //查询酒店是否开启审核
        $hotel_ids =DB::table('order_audit')->pluck('hotel_id');
        $hotel_ids =Common::json_array($hotel_ids);
        if(in_array($hid,$hotel_ids)){
            if(in_array($user_data['type'],[1,2])){
                $status =1;
            }else{
                $status =10;
            }
        }else{
            $status =1;
        }
        return $status;
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
            $type = intval($arr['type']);
            $status=$this->orderStatus($user_data);
            DB::beginTransaction();
            try{
                $order_number = Common::createNumber();
                //查询
                $id = DB::table('order')->insertGetId(
                    [
                        'appointment' => $arr['time'],
                        'passenger_name' => $arr['name'],
                        'passenger_phone' => $arr['phone'],
                        'passenger_people' => $arr['people'],
                        'room_number' => $arr['room_number'],
                        'order_number' =>$order_number,
                        'remarks' => $arr['remarks'],
                        'car_id'  => $arr['car_id'],
                        'created_at'  =>time(),
                        'end' => $arr['end'],
                        'origin' => $arr['origin'],
                        'end_position' => $arr['end_position'],
                        'origin_position' => $arr['origin_position'],
                        'price' => $arr['price'],
                        'type' =>  $type ,
                        'orders_name' => $user_data['name'],
                        'orders_phone' => $user_data['mobile'],
                        'user_id' =>$user_data['id'],
                        'hotel_id'  =>$user_data['hotel_id'],
                        'judgment' => 1,
                        'bottom_number' =>$arr['hotel_number'],
                        'status' =>$status,
                        'service_type' =>$arr['service_type']

                    ]
                );
                $field =DB::table('server_item')->where('id',$type) ->first();
                $bdata = json_decode(json_encode($field), true);
                $field_mame =json_decode($bdata['field_name']);
                $content = json_decode($bdata['content']);
                $res = array_combine($field_mame,$content);
                $res['coordinate'] =$arr['end_position'];
                foreach(  $res as $k =>$v){
                    DB::table('way_to') ->insert([
                        'order_id' =>$id,
                        'name' =>$k,
                        'content' =>json_encode([$res[$k]])
                    ]);
                }
                DB::commit();
                $this->hotelLog($id,$user_data['name'],'APP创建了订单',$user_data['hotel_id'],$order_number);
                //查询插入新订单的数据
                $new_data =DB::table('order')->where('id',$id)->first();
                $new_data=Common::json_array($new_data);
                $alert =new PushController();
                $alert->createOrder($new_data);
                return ReturnMessage::success();
            }catch (\Exception $e){
                DB::rollback();
                return response()->json([
                    'code' => $e->getCode(),
                    'info' => $e->getMessage(),
                ]);
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
            $status=$this->orderStatus($user_data);
            DB::beginTransaction();

            try{
                $order_number = Common::createNumber();
                //插入数据
                $insert_id =DB::table('order')->insertGetId(
                    [
                        'appointment' => $arr['time'],
                        'passenger_name' => $arr['name'],
                        'passenger_phone' => $arr['phone'],
                        'passenger_people' => $arr['people'],
                        'room_number' => $arr['room_number'],
                        'order_number' =>$order_number,
                        'remarks' => $arr['remarks'],
                        'car_id'  => $arr['car_id'],
                        'created_at'  =>time(),
                        'end' => $arr['end'],
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
                        'bottom_number' =>$arr['hotel_number'],
                        'status' =>$status,
                        'service_type' =>$arr['service_type']
                    ]
                );

                //插入展字段
                $field =DB::table('server_item')->where('id',$arr['type']) ->value('field_name');

                $field_mame = json_decode($field);
                if(!is_null($field_mame)){
                    foreach(  $field_mame as $k =>$v){
                        DB::table('way_to') ->insert([
                            'order_id' =>$insert_id,
                            'name' =>$v,
                            'content' =>json_encode([$arr[$v]])
                        ]);
                    }
                }

                DB::commit();
                $this->hotelLog($id,$user_data['name'],'APP创建了订单',$user_data['hotel_id'],$order_number);
                //查询插入新订单的数据
                $new_data =DB::table('order')->where('id',$insert_id)->first();
                $new_data=Common::json_array($new_data);
                $alert =new PushController();
                $alert->createOrder($new_data);
                return ReturnMessage::success();
            }catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'code' => $e->getCode(),
                    'info' => $e->getMessage(),
                ]);
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
            $status=$this->orderStatus($user_data);
            DB::beginTransaction();
            try{
                $order_number = Common::createNumber();
                //插入基础数据
                $id= DB::table('order')->insertGetId(
                    [
                        'appointment' => $arr['time'],
                        'passenger_name' => $arr['name'],
                        'passenger_phone' => $arr['phone'],
                        'passenger_people' => $arr['people'],
                        'room_number' => $arr['room_number'],
                        'order_number' =>$order_number,
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
                        'user_id' =>$id,
                        'hotel_id'  =>$user_data['hotel_id'],
                        'judgment' => 1,
                        'bottom_number' =>$arr['hotel_number'],
                        'cip' => $arr['cip'],
                        'status' =>$status,
                        'service_type' =>$arr['service_type']
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
                $this->hotelLog($id,$user_data['name'],'APP创建了订单',$user_data['hotel_id'],$order_number);
                //查询插入新订单的数据
                $new_data =DB::table('order')->where('id',$id)->first();
                $new_data=Common::json_array($new_data);
                $alert =new PushController();
                $alert->createOrder($new_data);
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
            $status=$this->orderStatus($user_data);
            DB::beginTransaction();
            try{
                $order_number = Common::createNumber();
                //插入基础数据
                $id= DB::table('order')->insertGetId(
                    [
                        'appointment' => $arr['time'],
                        'passenger_name' => $arr['name'],
                        'passenger_phone' => $arr['phone'],
                        'passenger_people' => $arr['people'],
                        'room_number' => $arr['room_number'],
                        'order_number' =>$order_number,
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
                        'bottom_number' =>$arr['hotel_number'],
                        'status' =>$status,
                        'service_type' =>$arr['service_type']
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
                $this->hotelLog($id,$user_data['name'],'APP创建了订单',$user_data['hotel_id'],$order_number);
                //查询插入新订单的数据
                $new_data =DB::table('order')->where('id',$id)->first();
                $new_data=Common::json_array($new_data);
                $alert =new PushController();
                $alert->createOrder($new_data);
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
                    'type'  =>$type,
                    'origin' => $arr['origin'],
                    'bottom_number' => $arr['hotel_number'],
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
                    'type'  =>$type,
                    'end' => $arr['end'],
                    'origin' => $arr['origin'],
                    'end_position' => $arr['end_position'],
                    'origin_position' => $arr['origin_position'],
                    'user_id' =>$user_data['id'],
                    'hotel_id'  =>$user_data['hotel_id'],
                    'judgment' => 1,
                    'bottom_number' => $arr['hotel_number'],
                ]);
            }
            if( $re ){
                $this->hotelLog($id,$user_data['name'],'APP追加了订单',$user_data['hotel_id'],$arr['order_number']);
                return ReturnMessage::success();
            }else{
                return ReturnMessage::success('失败','1011');
            }

        } catch (JWTException $e) {
            return ReturnMessage::success('非法token', '1009');
        }

    }
    /**
     * 追加订单详情
     * @param $id
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse
     */
    public function getExtraDetail( $id )
    {
        //订单编号 查 追加订单
        try {
            JWTAuth::parseToken()->getPayload();
            $data=DB::table('extra_order')
                ->where('order_id',$id)->get();
            $bdata=json_decode(json_encode($data),true);
            if( count($bdata) != 0){

                $bdata['appointment'] = date('Y-m-d H:i',$bdata['appointment']);
                $bdata['created_at'] = date('Y-m-d H:i',$bdata['created_at']);
                //获取所属服务
                $data_to = DB::table('server_item')->where('id',$bdata['type'])->first();
                $bdata_to=json_decode(json_encode($data_to),true);
                $bdata['server_title'] = $bdata_to['name'];
                $data_way= [];
                //判断是否有扩展字段
                if($bdata_to['type'] !== 'null' && !empty($bdata_to)) {

                    $type_name = json_decode($bdata_to['type_name']);
                    $field_name = json_decode($bdata_to['field_name']);

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
                        'formal'=> ReturnMessage::toString([$bdata]),
                        'extra' => ReturnMessage::toString($data_way)
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
     * 订单审核、驳回
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function makeCheck( Request $request)
    {
        $arr = OrderValidator::check($request);
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
//            $user_data = Hotel::getUserFirst($id);
            //查询当前订单信息
            $order_data = Order::getOrderFirst(['id' => $arr['order_id']]);
            $status = 0;
            $o_stataus = 0;
            switch ($arr['type']) {
                case 'agree':
                    $status = 1;
                    $o_stataus = 1;
                    break;
                case 'reject':
                    $status = 2;
                    $o_stataus = 0;
                    break;
                default:
                    ReturnMessage::success('失败', '1011');
            }
            $re =DB::table('order_audit_content')->insert([
                'order_number' =>$order_data['order_number'],
                'content'      =>$arr['reason'],
                'user_id'      =>$id,
                'created_at'   =>time(),
                'status'       =>$status
            ]);
            if($re){
                DB::table('order')->where('id', $arr['order_id'])->update(['status' => $o_stataus]);

                //判断该订单是否审核通过，通过则进行员工消息推送
                if ($o_stataus == 1) {
                    //获取审核通过消息格式
                    $message_list = Common::json_array(Db::table('message')->where('id', 10)->first());
                    //获取员工姓名
                    $user_name = DB::table('hotel_user')->where('id',$arr['user_id'])->value('name');
                    $preg = '/\[([^\[\]]*)\]/';
                    $preg_message_list = preg_replace($preg,$user_name,$message_list['content']);

                    //获取该订单的员工信息
                    $order_sql = Common::json_array(Db::table('order')->where('id', $arr['order_id'])->first());
                    $user_reg = Db::table('hotel_user')->where('id', $order_sql['user_id'])->value('jpush_code');

                    //新建消息信息
                    $message = DB::table('message_list')->insert([
                        'mid' => $message_list['id'],
                        'order_id' => $arr['order_id'],
                        'title' => $message_list['title'],
                        'content' => $preg_message_list,
                        'create_time' => time(),
                        'user_id' => $order_sql['user_id']
                    ]);

                    //新建成功后推送消息
                    if($message){
                        $alert = $preg_message_list;
                        $messages =[
                            "extras" => array(
                                'status'=> '126',
                                "data" => ['order_id' =>$arr['order_id']],
                            )
                        ];
                        $appKey = '50505e64af2ea4b5e8e27e26';
                        $master_secret = 'f90b3ccdce62056bb134aaaf';
                        $result =$this ->sendNotifySpecial($user_reg,$alert,$messages,$appKey,$master_secret);
                    }
                }

                //取消订单插入一条记录
                DB::table('order_status')
                    ->insert([
                        'order_number'=>$order_data['order_number'],
                        'status'=> $o_stataus,
                        'update_time' =>time()
                    ]);

                return ReturnMessage::success();
            }else{
                return ReturnMessage::success('失败','1001');
            }
        } catch (JWTException $e) {
            return ReturnMessage::success('非法token', '1009');
        }
    }


    /**
     * 投诉建议
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function getSuggest(Request $request)
    {
        $arr =$request->only('content','phone');
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            $user_data = Hotel::getUserFirst($id);
            $re = DB::table('suggest')->insert([
                    'suggest_number' =>Common::createNumber(),
                    'title' => 'APP酒店端投诉建议(统一)',
                    'content' => $arr['content'].$arr['phone'],
                    'created_at' =>time(),
                    'type'  =>1,
                    'hotel_id' =>$user_data['hotel_id'],
                    'suggest_name'=>$user_data['name'],
                    'send' =>1,
                    'parent_id'=>0

            ]);
            if($re){
                return ReturnMessage::success();
            }else{
                return ReturnMessage::success('失败','1001');
            }
        } catch (JWTException $e) {
            return ReturnMessage::success('非法token', '1009');
        }
    }

    /**
     * 我的消息
     * @param Request request
     * @return \App\Helpers\json
     */
    public function myNews( Request $request)
    {
        $param = OrderValidator::news($request);

        try{
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            $user_data = Hotel::getUserFirst($id);

            //获取消息列表里面的数据
            $massage = DB::table('message')
                ->where('id',10)
                ->get();
            //获取消息列表里面的数据
            $user_message = DB::table('message_list')
                ->where([
                    ['mid', '=', 10],
                    ['user_id' ,'=', $id]
                ])
                ->get();
            //获取
            $user_message = Common::json_array($user_message);

            if(!empty($user_message)){
                return ReturnMessage::success('success', '1000',$user_message);
            }else{
                return ReturnMessage::success('内容为空', '1011');
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token', '1009');
        }
    }

    /**
     * 财务结算表
     */
    public function getMyFinance(Request $request)
    {
        $param = OrderValidator::news($request);

        try{
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            $user_data = Hotel::getUserFirst($id);

            $order = DB::table('settlement_log')
                ->where([
                    ['hotel_id','=',$user_data['hotel_id']]
                ])
                ->get();
            //获取
            $order = Common::json_array($order);
            if(!empty($order)){
                //重组数组
                $data = array(
                    'tobesettled' => array_sum(Common::json_array(DB::table('settlement_log')
                        ->where([
                            ['hotel_id','=',$user_data['hotel_id']],
                            ['credentials_type','=',1],
                        ])
                        ->pluck('settlement_amount'))),
                    'settled' => array_sum(Common::json_array(DB::table('settlement_log')
                        ->where([
                            ['hotel_id','=',$user_data['hotel_id']],
                            ['credentials_type','=',3],
                        ])
                        ->pluck('settlement_amount'))),
                    'data' => $order
                );

                if(!empty($data)){
                    return ReturnMessage::success('success', '1000',$data);
                }else{
                    return ReturnMessage::success('内容为空', '1011');
                }
            }else{
                return ReturnMessage::success('内容为空', '1011');
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token', '1009');
        }
    }

    /**
     * 财务详情表
     */
    public function getFinance(Request $request)
    {
        $param = OrderValidator::newsFinance($request);

        try{
            $order = DB::table('settlement_log')
                    ->where([
                        ['id','=',$param['log_id']]
                    ])
                    ->first();
            //获取
            $order = Common::json_array($order);
            if(!empty($order)){
                return ReturnMessage::success('success', '1000',$order);
            }else{
                return ReturnMessage::success('内容为空', '1011');
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token', '1009');
        }
    }

    /**
     * 荒废方法
     * @param array $user_data
     */
    public function getRemind(array $user_data)
    {
        //下单的提醒
        $order_number='180119165653995';
        $result =1;//订单id
        $message=DB::table('message')->where('id',7)->first();
        $message =Common::json_array($message);
        $common =new Common();
        $message_data=[
            'user'=>'APP的ceshi',
            'time' => date('Y-m-d H:i:s',time())
        ];
        $user_data['hotel_id']=1;
        $order_number_url = url('http://travel.shidaichuxing.com/home/homeorder/orderdetails?id='.$order_number);
        $message['content'] .='<a id="order_number_buchongfu" href="javascript:openapp(\''.$order_number_url.'\',\'189admin\',\'订单详情\');" class="btn btn-primary" data-dismiss="modal">订单号：'.$order_number.'</ a>';
        if($message){
            $msg = $common->goEasy($result,$message['id'],$message['title'],$message['mark'].'_'.$user_data['hotel_id'],$message['content'],$message_data);
        }
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
}