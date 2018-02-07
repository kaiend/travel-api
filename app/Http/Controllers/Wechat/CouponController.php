<?php

namespace App\Http\Controllers\Wechat;

use App\Helpers\Common;
use App\Helpers\Sms;
use App\Http\Validators\CouponValidator;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Validators\UserValidator;
use App\Http\Controllers\Controller;
use App\Helpers\ReturnMessage;
use App\Http\Validators\OrderValidator;
use App\Models\Trading;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Exception;


class CouponController extends Controller
{
    /**
     * 获取出行卡
     * @param $request
     * @return mixed
     * */
    public function getCard( Request $request)
    {
        try {
            $card = DB::table('coupon_groups')
                        ->select('id','name','price','member')
                        ->where([
                            ['genre',3],
                            ['payment',2],
                            ['status',1],
                        ])->get();

            if(empty($card)){
                return ReturnMessage::success('数据为空',1011);
            }else{
                $data = json_decode(json_encode($card),true);
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => ReturnMessage::toString($data)
                ]);
            }
        } catch (\Exception $e) {
            return ReturnMessage::success('获取出行卡失败',1011);
        }
    }

    /**
     * 获取我的优惠券
     * @param $request
     * @return mixed
     * */
    public function getCoupon( Request $request)
    {
        $input = CouponValidator::myCoupon($request);
        try {
            $coupon = DB::table('coupon_groups')
                ->leftJoin('coupon_user','coupon_user.coupon_id','=','coupon_groups.id')
                ->where([
                    ['coupon_user.user_id',$input['user_id']],
                    ['coupon_user.is_used',1],
                    ['coupon_user.genre','!=',3]
                ])
                ->select('coupon_user.coupon_code','coupon_groups.name','coupon_groups.price','coupon_groups.end_time','coupon_groups.rule')
                ->get();

            if(empty($coupon)){
                return ReturnMessage::success('数据为空',1011);
            }else{
                $data = json_decode(json_encode($coupon),true);
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => ReturnMessage::toString($data)
                ]);
            }
        } catch (\Exception $e) {
            return ReturnMessage::success('获取优惠券失败',1011);
        }
    }

    /**
     * 领取优惠券
     * @param $request
     * @return mixed
     * */
    public function getUserCoupon( Request $request)
    {
        $input = CouponValidator::buyCoupon($request);

        try {
            //拆开coupon_id
            $coupon = explode(',',$input['coupon_id']);

            foreach ($coupon as $val){
                $genre = DB::table('coupon_groups')->where('id',$val)->value('genre');
                $param = array(
                    'coupon_id' => $val,
                    'user_id' => $input['user_id'],
                    'coupon_code' => common::createNumber(),
                    'coupon_pass' => $this->createPass(),
                    'redeem_time' => time(),
                    'genre' => $genre
                );
                $coupons[] = DB::table('coupon_user')->insertGetId($param);
            }

            if(empty($coupons)){
                return ReturnMessage::success('数据创建失败',1011);
            }else{

                $dat = DB::table('coupon_groups')
                    ->leftJoin('coupon_user','coupon_user.coupon_id','=','coupon_groups.id')
                    ->whereIn('coupon_user.id',$coupons)
                    ->select('coupon_user.coupon_code','coupon_user.coupon_pass','coupon_groups.name','coupon_groups.price','coupon_groups.end_time','coupon_groups.rule')
                    ->get();
                $data = json_decode(json_encode($dat),true);
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => ReturnMessage::toString($data)
                ]);
            }
        } catch (\Exception $e) {
            return ReturnMessage::success('领取优惠券失败',1011);
        }
    }

    /**
     * 出行卡绑定
     * @param $request
     * @return mixed
     * */
    public function cardBind( Request $request)
    {
        //获取出行卡账号和密码
        $input = CouponValidator::cardGoBind($request);

        try {
            $coupon = DB::table('coupon_user')
                    ->where([
                        ['coupon_code',$input['coupon_code']],
                        ['coupon_pass',$input['coupon_pass']],
                    ])
                    ->first();
            if(empty($coupon)){
                return ReturnMessage::success('出行卡绑定失败，请检查您的账号和密码是否正确',1011);
            }else{
                $time = time();
                //如果有该出行卡则绑定，更改出行卡状态为已使用，时间为创建时间
                $result = DB::table('coupon_user')
                            ->where('coupon_code',$input['coupon_code'])
                            ->update([
                                'is_used' => 2,
                                'use_time' => $time,
                            ]);
                //更改之后添加内容
                DB::table('personal_user_card')->insertGetId([
                    'user_id' => $input['user_id'],
                    'card_code' => $input['coupon_code'],
                    'create_at' => $time,
                ]);
                if(empty($result)){
                    return ReturnMessage::success('数据创建失败',1011);
                }else{
                    return response()->json([
                        'code' =>'1000',
                        'info' => 'success',
                        'data' => []
                    ]);
                }
            }
        } catch (\Exception $e) {
            return ReturnMessage::success('绑定出行卡失败',1011);
        }
    }

    /**
     * 我的出行卡额度
     */
    public function getMyCard( Request $request)
    {
        $input = CouponValidator::myCoupon($request);

        try {
            $user = DB::table('personal_user')
                    ->where([
                        ['id',$input['user_id']]
                    ])
                    ->select('id','user_nickname','phone','travel_card_money')
                    ->first();

            $card_time[] = DB::table('card_audit')
                        ->where([
                            ['uid',$input['user_id']]
                        ])
                        ->orderBy('create_time','desc')
                        ->value('create_time');

            $card_time[] = DB::table('coupon_user')
                        ->where([
                            ['user_id',$input['user_id']]
                        ])
                        ->orderBy('use_time','desc')
                        ->value('use_time');
            $data = json_decode(json_encode($user),true);
            if(!empty($card_time)){
                rsort($card_time);
                $data['time'] = $card_time[0];
            }else{
                $data['time'] = 0;
            }

            if(empty($user)){
                return ReturnMessage::success('获取出行卡余额失败',1011);
            }else{

                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => ReturnMessage::toString($data)
                ]);
            }
        } catch (\Exception $e) {
            return ReturnMessage::success('获取我的出行卡失败',1011);
        }
    }



    /**
     * 生成优惠券密码
     * @author lb
     * @param int $num  长度
     * @return int $code
     */
    private function createPass($num = 6)
    {
        $code = rand(1,9);
        for ($i = 0; $i < $num-1; $i++) {
            $code .= rand(0,9);
        }
        return $code;
    }

    /**
     * 发送短信
     */
    public function sendSms( Request $request)
    {
        $input = CouponValidator::MyCard($request);

        try{
            $send = $this->sendMessage($input['phone'],$input['name'],$input['coupon_code'],$input['coupon_pass']);
            return response()->json([
                'code' =>'1000',
                'info' => 'success',
                'data' => ReturnMessage::toString($send)
            ]);
        } catch (\Exception $e){
            return ReturnMessage::success('获取我的出行卡失败',1011);
        }
    }

    /**
     * 发送短信函数
     */
    private function sendMessage($phone,$name,$coupon_code,$coupon_pass)
    {
        $msg ='【时代出行】您已成功购买时代出行'.$name.'（卡类型），卡号：'.$coupon_code.'，密码：'.$coupon_pass.'（请妥善保管，切勿将密码告知他人）。为保证您的正常使用，请您尽快登录时代出行微信小程序完成绑定。如有任何疑问请致电010-59477666';
        return (new Sms())->sendSMS($phone,$msg);
    }
}