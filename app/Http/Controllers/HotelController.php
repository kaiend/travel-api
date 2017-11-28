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
use App\Http\Validators\UserValidator;
use App\Models\Hotel;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;


class HotelController extends Controller
{
    /**
     *
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
        $info = DB::table('hotel_user')->where($input) ->first();

        if (!empty($info)){
            $info = json_decode(json_encode($info),true);
            $info['token'] = $this->token( $info['id'] );
            return ReturnMessage::successData($info);
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
        $data['password'] = '';

        try {

            DB::table('hotel_user')->where('mobile','=',$input['mobile'])->update(['password' => $data['password']]);
            $data = DB::table('hotel_user')->where('mobile','=',$input['mobile'])->get();
            $info = json_decode(json_encode($data),true);
            $info['token'] = $this->token( $info['id'] );

        } catch (\Exception $e) {
            return ReturnMessage::success('修改密码失败',1011);
        }

        return ReturnMessage::successData($info);
    }

    /**
     * 修改密码
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

            DB::table('hotel_user')->where('mobile','=',$input['mobile'])->update(['password' => $data['password']]);
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

            $user_data =Hotel::getUserFirst( $id );

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
                        DB::table('hotel_user')->insert(
                            [
                                'name' => $arr['name'],
                                'mobile'=> $arr['phone'],
                                'department'=>$arr['department'],
                                'position'=> $arr['position'],
                                'type'=>$arr['type'],
                                'hotel_id' =>$user_data['hotel_id'],
                                'avatar'   =>$avatar
                            ]
                        );
                        return ReturnMessage::success('success','1000');
                    }

                }else{
                    if(in_array(intval($arr['type']),[1,2,3,4]) ){
                        DB::table('hotel_user')->insert(
                            [
                                'name' => $arr['name'],
                                'mobile'=> $arr['phone'],
                                'department'=>$arr['department'],
                                'position'=> $arr['position'],
                                'type'=> intval($arr['type']),
                                'hotel_id' =>$user_data['hotel_id'],
                                'avatar'   =>$avatar
                            ]
                        );
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
            return ReturnMessage::success('success','1000');

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }

    public  function destroy()
    {

    }
    public function test()
    {
        return 1;
    }

}