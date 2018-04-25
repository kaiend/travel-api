<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/15
 * Time: 14:12
 */
namespace App\Http\Controllers;
use App\Helpers\Common;
use App\Helpers\ReturnMessage;
use App\Helpers\Sms;
use App\Http\Validators\UserValidator;
use App\Models\Hotel;
use App\Models\Hotels;
use App\Models\Log;
use App\Models\Order;
use App\Models\ServerItem;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;


class HotelController extends Controller
{
    private $appKey ='50505e64af2ea4b5e8e27e26';
    private $master_secret ='f90b3ccdce62056bb134aaaf';
    /**
     *生成token
     * @param $data 用户id
     * @return mixed
     */
    private function token( $data )
    {
        $re=JWTFactory::sub(123)->aud('foo')->foo( $data )->make();
        $token = JWTAuth::encode($re)->get();
        return $token;
    }
    /**
     * 酒店用户的登录方法
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $input = UserValidator::hotelLogin($request);

        $input['mobile'] =$input['phone'];
        unset($input['phone']);
        $info = DB::table('hotel_user')
            ->where([
                ['mobile',$input['mobile']],
                ['password',$input['password']]
            ])->first();
        if (!empty($info)){
            $info = json_decode(json_encode($info),true);
            $dat['status_login'] =1;
            $dat['last_login_time'] =time();
            if( !empty( $info['jpush_code'] )){
                if( $info['model_code'] != $input['model_code'] ){
                    $dat['model_code'] = $input['model_code'];
                    //向原设备发送提醒
                    $alert = "您的账号已经在另一地登录";
                    $msg = array(
                        "extras" => array(
                            "status" => "104",
                        )
                    );
                    $push =new PushController();

                    $result = $push->sendNotifySpecial($info['jpush_code'],$alert,$msg,$this->appKey,$this->master_secret);
                    if( $result['http_code']){
                        $dat['jpush_code'] = $input['jpush_code'];

                    }
                }

            }else{
                $dat[ 'jpush_code']= $input['jpush_code'];
                $dat['model_code'] = $input['model_code'];
            }
            $dat['model_status'] = $input['model_status'];
            $result = Db::table('hotel_user')->where('id',$info['id'])->update($dat);
            if( $result ){
                $new_Data= DB::table('hotel')
                    ->join('hotel_user','hotel.id','=','hotel_user.hotel_id')
                    ->where('hotel_user.id',$info['id'])
                    ->first();
                $new_Datas = json_decode(json_encode($new_Data),true);
                if(!is_null($new_Datas['hotel_logo'])){
                    $new_Datas['hotel_logo']='http://travel.shidaichuxing.com/upload/'.$new_Datas['hotel_logo'];
                }
                $new_Datas['token'] = $this->token( $new_Datas['id'] );
                return ReturnMessage::successData($new_Datas);
            }else{
                return ReturnMessage::success('失败','1011');
            }
        }
        return ReturnMessage::success('用户不存在或密码错误',1002);
    }
    /**
     * APP用户快捷登录
     * @param Request $request
     * @return \App\Helpers\json|mixed
     */
    public function sign( Request $request )
    {
        $input = UserValidator::sign($request);
        $input['mobile'] = $input['phone'];
        unset($input['phone']);
        $data = DB::table('hotel_user')->where('mobile','=',$input['mobile'])->first();
        $info = Common::json_array($data);

        $dat['status_login'] =1;
        $dat['last_login_time'] =time();
        if( !empty( $info['jpush_code'] )){
            if( $info['model_code'] != $input['model_code']) {
                $dat['model_code'] = $input['model_code'];
                //向原设备发送提醒
                $alert = "您的账号已经在另一地登录";
                $msg = array(
                    "extras" => array(
                        "status" => "104",
                    )
                );
                $push = new PushController();
                $result = $push->sendNotifySpecial($info['jpush_code'], $alert, $msg,$this->appKey,$this->master_secret);
                if ($result['http_code']) {
                    $dat['jpush_code'] = $input['jpush_code'];

                }
            }
        }else{
            $dat[ 'jpush_code']= $input['jpush_code'];
            $dat['model_code'] = $input['model_code'];
        }
        $dat['model_status'] = $input['model_status'];
        $result = Db::table('hotel_user')->where('id',$info['id'])->update($dat);
        if( $result ){
            $new_Data= DB::table('hotel')
                ->join('hotel_user','hotel.id','=','hotel_user.hotel_id')
                ->where('hotel_user.id',$info['id'])
                ->first();
            $new_Datas = json_decode(json_encode($new_Data),true);
            if(!is_null($new_Datas['hotel_logo'])){
                $new_Datas['hotel_logo']='http://travel.shidaichuxing.com/upload/'.$new_Datas['hotel_logo'];
            }
            $new_Datas['token'] = $this->token( $new_Datas['id'] );
            return ReturnMessage::successData($new_Datas);
        }else{
            return ReturnMessage::success('失败','1011');
        }
    }
    /**
     * 用户修改密码
     * @param Request $request
     * @return \App\Helpers\json|mixed
     */
    public function editPassword( Request $request)
    {

        $input = UserValidator::editPassword($request);
        $input['mobile'] = $input['phone'];
        unset($input['phone']);
        $data['password'] = Common::createPassword($input['password']);
        try {

            DB::table('hotel_user')
                ->where('mobile','=',$input['mobile'])
                ->update([
                    'password' => $data['password'],
                    'model_status' =>$input['model_status'],
                    'jpush_code' => $input['jpush_code'],
                    'model_code ' =>$input['model_code']
                ]);
            $data = DB::table('hotel_user')->where('mobile','=',$input['mobile'])->get();
            $info = json_decode(json_encode($data),true);
            $info['token'] = $this->token( $info['id'] );

        } catch (\Exception $e) {
            return ReturnMessage::success('修改密码失败',1011);
        }

        return ReturnMessage::successData($info);
    }
    /**
     * APP子账户列表
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function getList()
    {
        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = intval($user['foo']);

            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);
            if( $user_data['type'] == 3 ){
                return ReturnMessage::success('没有权限' ,'1010');
            }else if( $user_data['type'] == 2 ){
                //管理者查询 -----员工账号
                $data = DB::table('hotel_user')
                    ->select('id','name','mobile','position','type','avatar','status')
                    ->where([
                        ['hotel_id' , $user_data['hotel_id']]
                    ])
                    ->whereIn('type', [3,4])
                    ->get();
            }else{

                $data = DB::table('hotel_user')
                    ->select('id','name','mobile','position','type','avatar','status')
                    ->where('hotel_id' , $user_data['hotel_id'])
                    ->whereIn('type', [2,3,4])
                    ->get();
            }
            $bdata=json_decode(json_encode($data),true);
            if( count($bdata) != 0){
//                $final=ReturnMessage::toString($bdata);
                $final=[];
                foreach( $bdata as $k => $v){
                    $final[$k]=$v;
                    if(!empty($v['avatar'])){

                        $final[$k]['avatar'] ='http://travel-api.times-vip.com/'.$v['avatar'];
                    }else{
                        $final[$k]['avatar'] =$v['avatar'];
                    }

                }


                return ReturnMessage::successData( $final);

            }else{
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => []
                ]);
            }

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }
    /**
     * 个人中心--上传头像
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse
     */
    public function uploadPhoto( Request $request )
    {
        try {
            $user=JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];

            //$user_data =Hotel::getUserFirst( $id );

            $file = $request->file('avatar');

            if( $file ){
                $file_path ='uploads/'. date("Ym")."/";
                $extension = $file->getClientOriginalExtension();
                $file_name =md5(time().mt_rand(10, 99)).'.'.$extension;
                $info = $file->move($file_path ,$file_name);
                if (!$info) {
                    $error = $file->getError();
                    return ReturnMessage::success('失败','1011');
                }
                $data['avatar'] = $file_path.$file_name;
                $re = DB::table('hotel_user') ->where('id',$id) -> update(['avatar'=>$data['avatar']]);

                if( $re ){
                    return response()->json([
                        'code' => '1000',
                        'info' =>  'success',
                        'data' =>'http://travel-api.times-vip.com/'.$data['avatar']
                    ]);
                }else{
                    return ReturnMessage::success('失败','1011');
                }

            }else{
                return ReturnMessage::success('失败','1011');
            }

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }


    }
    /**
     * APP 个人账户-添加子账户
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function addChild( Request $request)
    {
        $arr=$request->all();
        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);
            //手机号来做唯一限制
            $mobile = $arr['phone'];
            $c_id=DB::table('hotel_user') -> where('mobile',$mobile) ->value( 'id' );
            $avatar = self::makePhoto($request);
            if( $c_id ){
                return ReturnMessage::success('账户已被占用','1008');
            }else{
                if( $user_data['type'] == 3 || $user_data['type'] == 4){
                    return ReturnMessage::success('没有权限' ,'1010');
                }else if( $user_data['type'] == 2 ){
                    //管理者添加-----员工账号
                    if(in_array(intval($arr['type']),[1,2]) ){
                       return ReturnMessage::success('没有权限','1010');
                    }else{

                        $role_id =DB::table('hotel_user')->insertGetId(
                            [
                                'name' => $arr['name'],
                                'mobile'=> $arr['phone'],
                                'department'=>$arr['department'],
                                'position'=> $arr['position'],
                                'type'=>$arr['type'],
                                'hotel_id' =>$user_data['hotel_id'],
                                'avatar'   =>$avatar,
                                'create_time' =>time(),
                                'password' =>Common::createPassword(substr($arr['phone'],-6))
                            ]
                        );
                        $rid='';
                        switch($arr['type']){
                            case '2':
                                //插入超级管理员权限
                                $rid = 1;
                            break;
                            case '3':
                                //插入财务管理权限
                                $rid = 8;
                            break;
                            case '4':
                                //插入财务管理权限
                                $rid = 3;
                                break;

                        }
                        DB::table('hotel_role_user')->insert([
                            'role_id' =>$rid,
                            'user_id' =>$role_id
                        ]);

                        return ReturnMessage::success('success','1000');
                    }

                }else{
                    if(in_array(intval($arr['type']),[1,2,3,4]) ){
                        $role_id =DB::table('hotel_user')->insertGetId(
                            [
                                'name' => $arr['name'],
                                'mobile'=> $arr['phone'],
                                'department'=>$arr['department'],
                                'position'=> $arr['position'],
                                'type'=> intval($arr['type']),
                                'hotel_id' =>$user_data['hotel_id'],
                                'avatar'   =>$avatar,
                                'create_time' =>time(),
                                'password' =>Common::createPassword(substr($arr['phone'],-6))
                            ]
                        );
                        $rid='';
                        switch($arr['type']){
                            case '2':
                                //插入超级管理员权限
                                $rid = 1;
                                break;
                            case '3':
                                //插入财务管理权限
                                $rid = 8;
                                break;
                            case '4':
                                //插入财务管理权限
                                $rid = 3;
                                break;

                        }
                        DB::table('hotel_role_user')->insert([
                            'role_id' =>$rid,
                            'user_id' =>$role_id
                        ]);

                        return ReturnMessage::success('success','1000');
                    }
                }
            }
//

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
    }
    /**
     * 子账户上传头像静态方法
     * @param Request $request
     * @return \App\Helpers\json|string
     */
    private static  function makePhoto(Request $request )
    {
        $file = $request->file('avatar');

        if ($file) {
            $file_path = 'uploads/' . date("Ym") . "/";
            $extension = $file->getClientOriginalExtension();
            $file_name = md5(time() . mt_rand(10, 99)) . '.' . $extension;
            $info = $file->move($file_path, $file_name);
            if (!$info) {
                //$error = $file->getError();
                return ReturnMessage::success('失败', '1011');
            }
            return $data['avatar'] = $file_path . $file_name;
        }
    }
    /**
     * APP 个人账户-禁用子账户
     * @param $id
     * @return \App\Helpers\json
     */
    public function stopChild( $id ,Request $request )
    {
        $arr = UserValidator::childDisable($request);
        $id = intval($id);
        try {
            JWTAuth::parseToken()->getPayload();
            switch (intval( $arr['status'] )){
                //启用
                case  1:
                    $re = DB::table('hotel_user')->where('id', $id)->update(['status' => 1]);break;
                //禁用
                case 0:
                    $re = DB::table('hotel_user')->where('id', $id)->update(['status' => 0]);break;
                default:
                    return ReturnMessage::success('失败','1011');

            }

            if ($re) {
                return ReturnMessage::success();
            } else {
                return ReturnMessage::success('失败', '1011');
            }
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
    }
    /**
     * APP 个人账户-重置子账户密码
     * @param $id
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function restPassword( $id, Request $request  )
    {
        $arr =$request->all();
        try{
            JWTAuth::parseToken()->getPayload();
            $password=Common::createPassword($arr['password']);
            $re = DB::table('hotel_user') ->where('id',intval($id))->update(['password'=>$password]);
            if( $re ){
                return ReturnMessage::success();
            }else{
                return ReturnMessage::success('失败' ,'1011');
            }
        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
    }
    /**
     * Token认证接口
     * @return \App\Helpers\json
     */
    public function authToken()
    {
        try{

            $re =JWTAuth::parseToken()->getPayload();
            $new_Data= DB::table('hotel_user')
                ->join('hotel','hotel_user.hotel_id','=','hotel.id')
                ->where('hotel_user.id',$info['id'])
                ->first();
            $new_Datas = json_decode(json_encode($new_Data),true);
            return ReturnMessage::success('success','1000');

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }
    /**
     * 退出登录接口
     */
    public  function destroy()
    {
        $user = JWTAuth::parseToken()->getPayload();
        $id = $user['foo'];
        $user_data= Hotel::getUserFirst($id);
        if( $user_data['status_login'] ){
            $re = DB::table('hotel_user')->where('id',$id)->update(['status_login'=> 0]);
            if( $re ){
                return ReturnMessage::success();
            }else{
                ReturnMessage::success('失败','1011');
            }
        }
        return ReturnMessage::success();
    }
    /**
     * 首页获得服务类目
     * @return \App\Helpers\json|mixed
     */
    public function getServer(Request $request)
    {
        try {
            $arr =$request->only('service_type');
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data = Hotel::getUserFirst($id);
            $hid = $user_data['hotel_id'];
            $data =DB::table('hotel_server')
                ->join('server_item','hotel_server.server_id','server_item.id')
                ->where([
                    'hotel_id'=>$hid,
                        'service_type' =>$arr['service_type']
                ])
                ->select('server_item.id','parent_id','name','picture')
                ->get();
            $data =Common::json_array( $data );
            $items = array();
            foreach( $data as $k=>$v){
                if($v['id'] ==20){
                    unset($data[$k]);
                }
                $items[$v['parent_id']] = $v;
            }
            $ids =array_keys( $items );

            $last_data = DB::table('server_item')
                        ->whereIn('id',$ids)
                        ->select('id','parent_id','name','picture')
                        ->orderBy('sort','desc')
                        ->get();
            $last_data =Common::json_array( $last_data );

            $fdata = array();
            foreach( $last_data as $k=>$v){
                $fdata[$v['id']] = $v;
            }

            $final_data =array_merge($fdata,$data);
            $jdata =array();
            foreach( $final_data as $k=>$v){
                $jdata[$v['id']] = $v;
            }
            $tree =[];
            foreach($jdata as $item){
                if(isset($jdata[$item['parent_id']])){
                    $jdata[$item['parent_id']]['son'][] = &$jdata[$item['id']];
                }else{
                    $tree[] = &$jdata[$item['id']];

                }
            }

            foreach($tree as $k=>$v){
                $tree[$k]['picture'] ='http://travel.shidaichuxing.com/upload/'.$tree[$k]['picture'];
                $tree[$k]['service_type'] = $arr['service_type'];
            }

            return ReturnMessage::successData($tree);
        }catch (JWTException $e){
            return ReturnMessage::success('非法token', '1009');
        }
    }
    /**
     * 测试api
     * @return int
     */
    public function test()
    {

    }
    /**
     * 出行地
     * @param Request $request
     * @return \App\Helpers\json
     */
    public function getTravel( Request $request)
    {
        $arr =UserValidator::getTravel($request);
        $area =$arr['area'];
        $type =$arr['type'];
        $data =Config::get('order.'.$type.'.'.$area);
        if( $data ){
            return ReturnMessage::successData($data);
        }else{
            return ReturnMessage::success('失败','1011');
        }
    }
    /**
     * 账户统计
     * @return \App\Helpers\json|mixed
     */
    public function getAccount()
    {
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data = Hotel::getUserFirst($id);
            $cip_fee=DB::table('hotel_fee')->where('company_id',$user_data['hotel_id'])->first();
            $cip_fee =Common::json_array($cip_fee);
            $where =['user_id'=>$user_data['id']];
            //今日新增
            $new_order =Order::getNewOder($where);
            //本月订单总量
            $month_sum =Order::getMonthMum($where);
            $order_data =Order::orderList($where);
            foreach($order_data as $k=>$v){
                if($v['cip'] == 1){
                    $order_data[$k]['total_fee'] =$v['price']+$cip_fee['cip_fee'];
                }else{
                    $order_data[$k]['total_fee'] =$v['price'];
                }
                $order_data[$k]['server_title'] = DB::table('server_item')->where('id',$v['type'])->value('name');
                $order_data[$k]['car_name'] = Config::get('order.car_series.'.$v['car_id']);
                $order_data[$k]['date'] =date('Y-m',$v['created_at']);
            }

            $collection = collect($order_data);
            //待结算费用
            $unpaid = $collection->sum('total_fee');
            //账户流水明细
            $detail =$collection->groupBy('date')->toArray();
//            if($user_data['rebate'] == 0 ){
//                //不返佣
//                $rebate =[];
//            }else{
//                //查询返佣角色的详细信息
//                $rebate =DB::table('hotel_roles')->where('id',$user_data['rebate'])->first();
//                $rebate =Common::json_array($rebate);
//                //返佣的比率
//                $rebate_detail =intval($rebate['rebate'])/100;
//
//            }
            $last_data=[
                'news' =>$new_order,
                'count'=>$month_sum,
                'unpaid'=>$unpaid,
                'detail' =>$detail
            ];
            $last_data=ReturnMessage::toString($last_data);
            return ReturnMessage::successData($last_data);
        }catch (JWTException $e){
            return ReturnMessage::success('非法token', '1009');
        }
    }
    /**
     *财务管理
     * @return \App\Helpers\json|mixed
     */
    public function getFinancial()
    {
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data = Hotel::getUserFirst($id);
            $cip_fee=DB::table('hotel_fee')->where('company_id',$user_data['hotel_id'])->first();
            $cip_fee =Common::json_array($cip_fee);
            $where =['hotel_id'=>$user_data['hotel_id']];
            //本月消费金额
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $start = strtotime($BeginDate);
            $end =strtotime(date('Y-m-d', strtotime("$BeginDate +1 month")))-1;
            $m_data =DB::table('order')->where($where)->whereBetween('created_at',[$start,$end])->get();
            $m_data =Common::json_array($m_data);
            foreach ($m_data as $key=>$val){
                if($val['cip'] == 1){
                    $m_data[$key]['total_fee'] =$val['price']+$cip_fee['cip_fee'];
                }else{
                    $m_data[$key]['total_fee'] =$val['price'];
                }
                $m_data[$key]['server_title'] = DB::table('server_item')->where('id',$val['type'])->value('name');
            }
            $collection = collect($m_data);
            //本月消费金额结算费用
            $m_fee = $collection->sum('total_fee');

            //本月订单总量
            $month_sum =Order::getMonthMum($where);
            $order_data =Order::orderList($where);
            foreach($order_data as $k=>$v){
                if($v['cip'] == 1){
                    $order_data[$k]['total_fee'] =$v['price']+$cip_fee['cip_fee'];
                }else{
                    $order_data[$k]['total_fee'] =$v['price'];
                }
                $order_data[$k]['date'] =date('Y-m',$v['created_at']);
            }
            $collection = collect($order_data);
            //待结算费用
            $unpaid = $collection->sum('total_fee');
            //财务明细
            $detail =$collection->groupBy('date')->toArray();
//            if($user_data['rebate']){
//                //查询结算表的返佣情况
//            }
            $last_data=[
                'news' =>$m_fee,
                'count'=>$month_sum,
                'unpaid'=>$unpaid,
                'detail' =>$detail
            ];
            $last_data=ReturnMessage::toString($last_data);
            return ReturnMessage::successData($last_data);
        }catch (JWTException $e){
            return ReturnMessage::success('非法token', '1009');
        }
    }
    /**
     * 财务明细筛选
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function getFilter( Request $request )
    {
        $arr =$request ->only('clearing_type','start','end');
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];

            //查询当前用户的酒店ID和type
            $user_data = Hotel::getUserFirst($id);
            $handle = DB::table('order');
            $where =[
                ['hotel_id',$user_data['hotel_id']]
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
                    //->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
                    ->where($where)
                    ->whereBetween('complete_at', [$start, $end])->get();
            }else{

                $data =$handle
                    //->select('id','end','origin','type','orders_name','orders_phone','order_number','created_at','appointment','status','bottom_number')
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
     * 获得日志列表
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function getLog()
    {
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            $user_data =Hotel::getUserFirst($id);
            $log_data =Log::getLogList(['hotelId'=>$user_data['hotel_id']]);
            if( count($log_data) != 0){
                return ReturnMessage::successData($log_data);
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
     * 获得当前的酒店员工
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function getHotelUser()
    {
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            $user_data =Hotel::getUserFirst($id);
            $users =DB::table('hotel_user')
                ->where('hotel_id',$user_data['hotel_id'])
                ->select('id','name')
                ->get();
            $users =Common::json_array($users);
            if( count($users) != 0){
                return ReturnMessage::successData($users);
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
     * 日志筛选
     * @param Request $request
     * @return \App\Helpers\json|\Illuminate\Http\JsonResponse|mixed
     */
    public function filterLog(Request $request)
    {
        $arr =$request ->only('id','start','end');
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];

            //查询当前用户的酒店ID和type
            $user_data = Hotel::getUserFirst($id);
            $handle = DB::table('log');
            $where =[
               ['hotelId',$user_data['hotel_id']]
            ];
            $arr['userId'] =$arr['id'];
            unset($arr['id']);
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
                    ->where($where)
                    ->whereBetween('createTime', [$start, $end])->get();
            }else{
                $data =$handle
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

    public function getLogName()
    {
        try {
            $user = JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            $user_data =Hotel::getUserFirst($id);
            $name = Db::table('hotel_user')
                ->where("hotel_id",$user_data['hotel_id'])
                ->select('id','name')
                ->get();
            $users =Common::json_array($name);
            return ReturnMessage::successData($users);
        }catch (JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
    }
}