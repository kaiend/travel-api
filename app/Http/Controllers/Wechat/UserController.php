<?php

namespace App\Http\Controllers\Wechat;

use App\Helpers\Common;
use App\Helpers\Sms;
use App\Helpers\SaveImage;
use App\Library\WxPay\WxPay;
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
            if(empty($datas)){
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
			$info = Common::json_array($info);
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

        $info = Common::json_array($info);

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
        $input['travel_card_phone'] =$input['phone'];
        unset($input['id']);
        unset($input['phone']);
        $input['card_audit_status']=0;
        try {
            User::modifyUser($where,$input);
            //成功后，插入一条进入card_audit表中
            DB::table('card_audit')->insert([
                'uid' =>$where['id'],
                'card_audit_status' =>0,
                'pic' =>$input['travel_card'],
                'create_time' =>time()
            ]);
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

        return ReturnMessage::successData([$data['travel_card']]);

    }
    /*
     * 提交企业申请
     */
    public function business(Request $request){
        $input = $request->input();
        $data = DB::table('business')->insert([
            'user_id'=>$input['user_id'],
            'company_name'=>$input['company_name'],
            'user_name'=>$input['user_name'],
            'user_position'=>$input['user_position'],
            'user_phone'=>$input['user_phone'],
        ]);
        if($data){
            return ReturnMessage::success('申请成功',1000);
        }else{
            return ReturnMessage::success('申请失败',1002);
        }
    }
    /**
     * 测距
     * @param \Illuminate\Http\Request $request
     * @return bool|mixed
     */
    public function getDistance(Request $request)
    {
        $arr =$request->only('origins','destinations');
        $url ='http://api.map.baidu.com/routematrix/v2/driving?output=json&origins='.$arr['origins'].'&destinations='.$arr['destinations'].'&ak=RGwhFRkSZfva32BN96csoObm4FIfiCAY';
        function getUrl($url, $timeout = 5)
        {
            $url = str_replace("&amp;", "&", urldecode(trim($url)));
            //$cookie = tempnam ("/tmp", "CURLCOOKIE");
            $ch = curl_init();
            //curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
            curl_setopt($ch, CURLOPT_URL, $url);
            //curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $content = curl_exec($ch);
            $response = curl_getinfo($ch);
            if ($response['http_code'] != 200) {
                return false;
            }
            return $content;
        }
        return getUrl($url);
    }
    /**
     * 获取用户优惠券
     * @param $request
     * @return array
     * */
    public function user_coupon( Request $request )
    {
        $input = $request->input();

        $data = DB::table('coupon_user')
            ->leftJoin('coupon_groups','coupon_user.coupon_id','=','coupon_groups.id')
            ->where([
                ['coupon_user.user_id',$input['user_id']],
                ['coupon_groups.type',$input['type']],
                ['coupon_user.is_used',1],
            ])
            ->select('coupon_user.coupon_id','coupon_user.coupon_code','coupon_groups.name','coupon_groups.price','coupon_groups.end_time')
            ->get();

        $datas = common::json_array($data);
            if($datas){
                return ReturnMessage::successData($datas);
            }else{
                return ReturnMessage::success('暂无数据',1001);
            }



    }

    /**
     * 修改用户优惠券
     * @param $request
     * @return array
     * */
    public function Updatecoupon( Request $request )
    {
        $input = $request->input();

        $dat['is_used'] = 2;
        $where['coupon_id'] = $input['coupon_id'];
        $where['user_id'] = $input['user_id'];
        $result = Db::table('coupon_user')->where($where)->update($dat);
        if($result){
            return ReturnMessage::success('使用成功',1000);
        }else{
            return ReturnMessage::success('使用失败',1001);
        }


    }

}