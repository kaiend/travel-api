<?php

namespace App\Http\Controllers;

use App\Http\Validators\PayValidator;
use App\Library\WxPay\WxPay;
use App\Models\TopUp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{

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
		$postXml = file_get_contents("php://input"); //接收微信参数
		if (empty($postXml)) {
			return false;
		}

		$attr = $this->xmlToArray($postXml);

		$total_fee = $attr['total_fee'];
		$open_id = $attr['openid'];
		$out_trade_no = $attr['out_trade_no'];
		$time = $attr['time_end'];

		//CZ 170509 0000129001
		$type = substr( $out_trade_no , 0 , 2 );
		//判断类型 充值
		if ( $type == 'CZ' ) {
			$userId = (int)substr( $out_trade_no , 8 , 6 );
			$date['user_id'] = $userId;
			$data['price'] = $total_fee;
			$data['created_at'] = $time;
			self::topUpDate($data);
		}
		else {
			//

			//获取订单信息
			//判断订单金额与实际金额是否相符
			Log::info($open_id.'-----'.$out_trade_no);
			//支付结果处理
		}

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
			TopUp::create($data);
			User::where('id',$data['user_id'])->increment('balance',$data['price']);
			DB::commit();
			return true;
		} catch (\Exception $e) {
			DB::rollBack();
			return false;
		}
	}


}