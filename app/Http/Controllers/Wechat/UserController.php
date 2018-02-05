<?php

namespace App\Http\Controllers\Wechat;

use App\Helpers\Common;
use App\Helpers\Sms;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Validators\UserValidator;
use App\Http\Controllers\Controller;
use App\Helpers\ReturnMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
	/**
	 * 发送验证码
	 * @author yxk
	 * @param $request
	 * @return mixed;
	 * */
	public function sendCode( Request $request )
	{
		$input = UserValidator::sendCode($request);

//		if (Redis::exists($input['phone']))
//			return ReturnMessage::success('验证码不要重复发送',1002);
        if (Redis::exists($input['phone'])){
            $code = Redis::get($input['phone']);
        }else{
            $code = $this->createCode();
        }

		$res = $this->sendSMS($input['phone'],$code);

		if (!empty($res)){
			$res = json_decode($res,true);
			if($res['error'] == 0)
				//添加缓存 以手机号为键 验证码为值 缓存30分钟
				Redis::setex($input['phone'],1800,$code);
			$data = [
			    'mobile' =>$input['phone'],
                'code'   =>$code
            ];
				return ReturnMessage::success('success',1000,$data);
//				return ReturnMessage::success($code);
		}

		return ReturnMessage::success('验证码发送失败',1002);
	}
	/**
	 * 生成验证码
	 * @author yxk
	 * @param int $num  长度
	 * @return int $code
	 */
	private function createCode($num = 4)
	{
		$code = rand(1,9);
		for ($i = 0; $i < $num-1; $i++) {
			$code .= rand(0,9);
		}
		return $code;
	}
	/**
	 * 调用短信发送接口
	 *
	 * @author yxk
	 * @param string $phone 长度
	 * @param string $code
	 * @return mixed
	 * */
	public function sendSMS( $phone, $code )
	{
		$msg = $code.'(验证码):'.'工作人员不会向您索要，请勿向任何人泄露。【时代出行】';
		return (new Sms)->sendSMS($phone,$msg);
	}
	/**
	 * 注册
	 *
	 * @author yyy
	 * @param $request
	 * @return mixed
	 * */
	public function register( Request $request )
	{
		$input = UserValidator::register($request);

		try {
            $datas = DB::table('personal_user')
                ->where([
                    ['phone',$input['phone']],
                    ['user_pass',$input['user_pass']]
                ])->first();
            dump($datas);die;
            if(!empty($datas)){
                DB::table('personal_user')->insert([
                    'phone' => $input['phone'],
                    'user_pass' => $input['user_pass'],
                ]);
            }else{
                return ReturnMessage::success('该手机号已注册',1005);
            }

		} catch (\Exception $e) {
			return ReturnMessage::success('注册失败',1002);
		}

		$data['phone'] = $input['phone'];
 		//$info  = User::getUserFirst($data);
        $info =  DB::table('personal_user')
            ->where([
                ['phone',$input['phone']],
            ])->first();
        $info=Common::json_array($info);
		return ReturnMessage::successData($info);
	}
	/**
	 * 登录
	 *
	 * @author yyy
	 * @param $request
	 * @return mixed
	 * */
	public function login( Request $request )
	{
		$input = UserValidator::login($request);
		//dump($input);die
        $info = DB::table('personal_user')
            ->where([
                ['phone',$input['phone']],
                ['user_pass',$input['user_pass']]
            ])->first();
		if (!empty($info)){
			$info = $info->toArray();
			//$info['token'] = $this->token();
			return ReturnMessage::successData($info);
		}

		return ReturnMessage::success('用户或密码错误',1002);
	}
	/**
	 * 判断验证码是否正确
	 *
	 * @author yxk
	 * @param $request
	 * @return mixed
	 * */
	public function verifyCode( Request $request )
	{
		$re =UserValidator::verifyCode($request);
		if($re['jsoncallback']=='callback'){
            $data = [
                'code' =>1000,
                'info'   =>'success'
            ];
            $result =json_encode($data);
            $callback=$re['jsoncallback'];
            return $callback."($result)";
        }else{
            return ReturnMessage::success();
        }

	}
	/**
	 * 修改秘密
	 *
	 * @author yxk
	 * @param $request
	 * @return mixed
	 * */
	public function modifyPassword( Request $request )
	{
		$input = UserValidator::modifyPassword($request);

		$data['password'] = Common::createPassword($input['password']);

		$where['mobile'] = $input['phone'];
		try {
			User::modifyUser($where,$data);
            if($input['jsoncallback']=='callback'){
                $data = [
                    'code' =>1000,
                    'info'   =>'success'
                ];
                $result =json_encode($data);
                $callback=$input['jsoncallback'];
                return $callback."($result)";
            }else{
                return ReturnMessage::success();
            }
		} catch (\Exception $e) {
			return ReturnMessage::success('修改密码失败',1002);
		}
	}
	/**
	 * 用户注销
	 * @param $request
	 * @return mixed
	 * */
	public function logout( Request $request )
	{
		//因暂时没有业务需求，只留出接口
		return ReturnMessage::success();
	}

	/**
	 * 获取用户信息
	 * @param $request
	 * @return array
	 * */
	public function getUserInfo( Request $request )
	{

		$input['id'] = $request->input('user_id');

		$info  = User::getUserFirst($input);

		return ReturnMessage::successData($info);
	}

	public function test( Request $request)
    {
        $arr =$request->only('oid');
        $id =$arr['oid'];
        //$odata =Order::getOrderFirst(['id'=>$id]);
        $odata=DB::table('order')->where('id',$id)->first();
        $odata =json_decode(json_encode($odata),true);
        $msg ='您好,您的【'.$odata['type'].'】用车服务预约成功,司机'.mb_substr($odata['chauffeur_name'],0,1).'师傅 联系电话:'.$odata['chauffeur_phone'].'将在【'.date('Y-m-d H:i:s',$odata['appointment']).'】到【'.$odata['origin'].'】接您。您的预约车辆为【'.$odata['car_series'].'】,车牌号【'.$odata['car_number'].'】。如有任何疑问,请联系致电：010-85117878。【时代出行】';
        $phone =$odata['passenger_phone'];
        return (new Sms)->sendSMS($phone,$msg);
    }
}