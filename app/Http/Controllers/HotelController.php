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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
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
    public function test()
    {
        return 1;
    }

}