<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Validators\PayValidator;
use App\Http\Controllers\Controller;
use App\Library\WxPay\WxPay;
use App\Models\Order;
use App\Models\TopUp;
use App\Models\Trading;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
	private $order_pay = 'pay';


	/**
	 * 生成微信订单
	 * @author yxk
	 * @param $request
	 * */
	public function WxOrder( Request $request )
	{
		$res = (new WxPay())->createOrder(PayValidator::WxOrder($request));
		header("Content-Type: application/json");
		echo $res;
	}

	/**
	 * 微信支付回调
	 * */
	public function WxNotify(  )
	{
		$res = false;

		$postXml = file_get_contents("php://input"); //接收微信参数
        $attr = $this->xmlToArray($postXml);
        print_r($attr);exit;
		if (empty($postXml)) {
			return $res;
		}

//		$attr = $this->xmlToArray($postXml);

		$total_fee = $attr['total_fee'];
		$open_id = $attr['openid'];
		$out_trade_no = $attr['out_trade_no'];
		$time = $attr['time_end'];

		//CZ 170509 0000129001
		$type = substr( $out_trade_no , 0 , 2 );

		//判断类型 充值
		if ( $type == 'CZ' ) {
			$userId = (int)substr( $out_trade_no , 8 , 6 );
			$data['user_id'] = $userId;
			$data['price'] = $total_fee;
			$data['number'] = $out_trade_no;
			$data['created_at'] = time();
			$res = self::topUpDate($data);

		}
		else {

			$orderNumber = substr( $out_trade_no , 0 , 15 );

			$order = Order::getOrderFirst(['order_number' => $orderNumber]);

			if (!(empty($order))){
				$order = $order->toArray();
				if ($order['price'] == $total_fee){
					$trading['user_id'] = $order['user_id'];
					$trading['order_number'] = $orderNumber;
					$trading['pay_number'] = $out_trade_no;
					$trading['money'] = $total_fee;
					$trading['pay_way'] = 'WX';
					$trading['remake'] = '订单'.$orderNumber.'消费';
					$trading['created_at'] = time();
					$res = self::orderSuccess($trading);
				}else{
					Log::info($open_id.'-----'.$out_trade_no.'-----'.$total_fee.'---价格不一致');
				}
			}else{
				Log::info($open_id.'-----'.$out_trade_no.'-----'.$total_fee.'---价格不一致');
			}
		}

		if ($res)
			self::return_success();

	}

	//将xml格式转换成数组
	protected function xmlToArray($xml) {

		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);

		$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$val = json_decode(json_encode($xmlstring), true);

		return $val;
	}

	/**
	 * 账户充值
	 *
	 * @author yxk
	 * @param $request
	 *
	 * */
	public function topUp( Request $request )
	{
		$res = (new WxPay())->createOrder(PayValidator::topUp($request));
		header("Content-Type: application/json");
		print_r($res);exit;
		echo $res;
	}

	/**
	 * 充值成功数据操作
	 *
	 * @author yxk
	 * @param $data
	 * @return bool
	 * */
	private function topUpDate( $data )
	{
		DB::beginTransaction();
		try {
//			TopUp::create($data);
//			User::where('id',$data['user_id'])->increment('balance',$data['price']);
			DB::table('top_up')->insertGetId($data);
            DB::table('personal_user')->where('id',$data['user_id'])->increment('balance',$data['price']);
//			print_r($data);exit;
			DB::commit();
			return true;
		} catch (\Exception $e) {
			DB::rollBack();
			Log::info('充值入库失败', ['context' => $e->getMessage()]);
			return false;
		}
	}

	/**
	 * 订单支付成功处理
	 *
	 * @author yxk
	 * @param $data
	 * @return bool
	 * */
	private function orderSuccess( $data )
	{

		DB::beginTransaction();
		try {
			Trading::create($data);
			Order::modifyOrder(['order_number' => $data['order_number']],['status'=>$this->order_pay]);
			DB::commit();
			return true;
		} catch (\Exception $e) {
			DB::rollBack();
			Log::info('订单微信支付回调失败', ['context' => $e->getMessage()]);
			return false;
		}

	}


	/*
     * 给微信发送确认订单金额和签名正确，SUCCESS信息 -xzz0521
     */
	private function return_success(){
		$return['return_code'] = 'SUCCESS';
		$return['return_msg'] = 'OK';
		$xml_post = '<xml>
                    <return_code>'.$return['return_code'].'</return_code>
                    <return_msg>'.$return['return_msg'].'</return_msg>
                    </xml>';
		echo $xml_post;exit;
	}
}