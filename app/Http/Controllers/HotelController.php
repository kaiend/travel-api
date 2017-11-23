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
use Illuminate\Support\Facades\Redis;
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
                    ->select('id','name','mobile','position','type')
                    ->where([
                        ['type' , 3] ,
                        ['status'],
                        ['hotel_id' , $user_data['hotel_id']]
                    ])
                    ->get();
            }else{

                $data = DB::table('hotel_user')
                    ->select('id','name','mobile','position','type')
                    ->where('hotel_id' , $user_data['hotel_id'])
                    ->whereIn('type', [2,3])
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

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }

    public function uploadPhoto( Request $request )
    {
        $arr =$request->all();
        try {
            JWTAuth::parseToken()->getPayload();

//            $image = $_FILES["photo"]["tmp_name"];
//            $fp = fopen($image, "r");
//            $xmlstr = fread($fp, $_FILES["photo"]["size"]); //二进制数据流
            $xmlstr = $arr['photo'];
            //保存地址
            $img_dir ='./uploads/'. date("Ym")."/"; //新图片名称

            if (! file_exists ( $img_dir )) {
                mkdir ( "$img_dir", 0777, true );
            }

            //要生成的图片名字
            $file_name = $img_dir.md5(time().mt_rand(10, 99)).".jpg";
            $new_file = fopen($file_name,"w"); //打开文件准备写入
            $re=fwrite($new_file,$xmlstr); //写入二进制流到文件
            fclose($new_file); //关闭文件
            if( $re ){
                $data =[
                    ['avatar' =>  $file_name]
                ];
                return ReturnMessage::successData($data);
            }else{
                return ReturnMessage::success('失败' ,'1011');
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
            if( $c_id ){
                return ReturnMessage::success('账户已被占用','1008');
            }else{
                if( $user_data['type'] == 3 ){
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
                                'type'=> 3,
                                'hotel_id' =>$user_data['hotel_id'],
                                'avatar'   =>$arr['avatar']
                            ]
                        );
                        return ReturnMessage::success('success','1000');
                    }

                }else{
                    if(in_array(intval($arr['type']),[1,2,3]) ){
                        DB::table('hotel_user')->insert(
                            [
                                'name' => $arr['name'],
                                'mobile'=> $arr['phone'],
                                'department'=>$arr['department'],
                                'position'=> $arr['position'],
                                'type'=> intval($arr['type']),
                                'hotel_id' =>$user_data['hotel_id'],
                                'avatar'   =>$arr['avatar']
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
     * APP 个人账户-禁用子账户
     * @param $id
     * @return \App\Helpers\json
     */
    public function stopChild( $id )
    {
        $id = intval($id);
        try {
            JWTAuth::parseToken()->getPayload();

            $re = DB::table('hotel_user')->where('id', $id)->update(['status' => 0]);
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
            $re = DB::table('hotel_user') ->where('id',intval($id))->update('password',$password);
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

            JWTAuth::parseToken()->getPayload();

            return ReturnMessage::success('success','1000');

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }

    }
    public function test()
    {
        return 1;
    }

}