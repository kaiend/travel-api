<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Helpers\SaveImage;
use App\Helpers\Sms;
use App\Library\WxPay\WxPay;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Validators\UserValidator;
use App\Helpers\ReturnMessage;
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

		if (Redis::exists($input['phone']))
			return ReturnMessage::success('验证码不要重复发送',1002);

		$code = $this->createCode();

		$res = $this->sendSMS($input['phone'],$code);

		if (!empty($res)){
			$res = json_decode($res,true);
			if($res['error'] == 0)
				//添加缓存 以手机号为键 验证码为值 缓存2分钟
				Redis::setex($input['phone'],120,$code);
				return ReturnMessage::success();
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
	 * @author yxk
	 * @param $request
	 * @return mixed
	 * */
	public function register( Request $request )
	{
		$input = UserValidator::register($request);

		try {
			User::create($input);
		} catch (\Exception $e) {
			return ReturnMessage::success('注册失败',1002);
		}

		$data['phone'] = $input['phone'];
 		$info  = User::getUserFirst($data);

		return ReturnMessage::successData($info);
	}

	/**
	 * 登录
	 *
	 * @author yxk
	 * @param $request
	 * @return mixed
	 * */
	public function login( Request $request )
	{
		$input = UserValidator::login($request);

		$info  = User::getUserFirst($input);

		if (!empty($info)){
			$info = $info->toArray();
			$info['token'] = $this->token();
			return ReturnMessage::successData($info);
		}

		return ReturnMessage::success('用户或密码错误',1002);
	}

	/**
	 * token 生成(留存字段 用与安全验证)
	 * */
	private function token()
	{
		return '';
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
		UserValidator::verifyCode($request);
		return ReturnMessage::success();
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

		$data['user_pass'] = Common::createPassword($input['user_pass']);

		$where['phone'] = $input['phone'];

		try {
			User::modifyUser($where,$data);
		} catch (\Exception $e) {
			return ReturnMessage::success('修改密码失败',1002);
		}

		return ReturnMessage::success();
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
	 * 出行卡数据填写
	 *
	 * @author yxk
	 * @param $request
	 * @return mixed
	 * */
	public function travelCard( Request $request )
	{
		$input = UserValidator::travelCard($request);

		$where['id'] = $input['id'];
		unset($input['id']);

		try {
			User::modifyUser($where,$input);
		} catch (\Exception $e) {
			return ReturnMessage::success('修改失败',1002);
		}

		return ReturnMessage::success();
	}

	/**
	 * 上传出行卡
	 * @param Request $request
	 * @return mixed
	 *
	 * */
	public function updateTravelCard(Request $request)
	{
		if(!$request->hasFile('travelCard')){
			return ReturnMessage::success('上传文件为空！',1002);
		}

		$file = $request->file('travelCard');
		//判断文件上传过程中是否出错
		if(!$file->isValid()){
			return ReturnMessage::success('文件上传出错！',1002);
		}

		$newFileName = md5(time().rand(0,10000));

		$data['travel_card'] = SaveImage::travelCard($newFileName,$file);

		return ReturnMessage::successData($data);

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


	/**
	 * 获取小程序openid
	 * @param $request
	 * @return array
	 * */
	public function getOpenId( Request $request )
	{
		$code = $request->input('code');

		return ReturnMessage::successData(WxPay::getSession($code));
	}
}