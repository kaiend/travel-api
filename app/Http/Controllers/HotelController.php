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
        $info = DB::table('hotel_user')->select('mobile','password','id')->where($input) ->first();

        if (!empty($info)){
            $info = json_decode(json_encode($info),true);
            $info['token'] = $this->token( $info['id'] );
            unset($info['id']);
            return ReturnMessage::successData($info);
        }

        return ReturnMessage::success('用户不存在或密码错误',1002);
    }

    public function editPassword( Request $request)
    {

        $input = UserValidator::editPassword($request);
        $input['mobile'] = $input['phone'];
        unset($input['phone']);
        $data['password'] = Common::createPassword($input['password']);


        try {

            DB::table('hotel_user')->where('mobile','=',$input['mobile'])->update(['password' => $data['password']]);

        } catch (\Exception $e) {
            return ReturnMessage::success('修改密码失败',1002);
        }

        return ReturnMessage::success();
    }

    //APP子账户列表
    public function getList()
    {
        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = intval($user['foo']);

            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);

            if( $user_data['type'] == 3 ){
                return ReturnMessage::success('你是员工哦' ,'1010');
            }else if( $user_data['type'] == 2 ){
                //管理者查询 -----员工账号
                $data = DB::table('hotel_user')
                    ->select('id','name','mobile','position','type')
                    ->where([
                        ['type' , 3] ,
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
    //APP 个人账户-添加子账户
    public function addChild( Request $request)
    {
        $arr=$request->all();
        try{
            $user=JWTAuth::parseToken()->getPayload();
            $id = $user['foo'];
            //查询当前用户的酒店ID和type
            $user_data= Hotel::getUserFirst($id);
            if( $user_data['type'] == 3 ){
                return ReturnMessage::success('你是员工哦' ,'1010');
            }else if( $user_data['type'] == 2 ){
                //管理者添加-----员工账号

            }

        }catch(JWTException $e){
            return ReturnMessage::success('非法token' ,'1009');
        }
    }

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