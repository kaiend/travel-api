<?php

namespace App\Http\Controllers;

use App\Helpers\Sms;
use Illuminate\Http\Request;
use App\Http\Validators\UserValidator;


class UserController extends Controller
{
	/**
	 * 发送验证码
	 * @author yxk
	 * @param $request
	 * */
	public function sendCode( Request $request )
	{
		$input = UserValidator::sendCode($request);

		$code = $this->createCode();

		$res = $this->sendSMS($input['phone'],$code);

		dd($res);

	}


	/**
	 * 发生验证码
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


}