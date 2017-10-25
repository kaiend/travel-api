<?php

namespace App\Http\Controllers;

use App\Http\Validators\PayValidator;
use App\Library\WxPay\WxPay;
use Illuminate\Http\Request;
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

		//获取订单信息
		//判断订单金额与实际金额是否相符
		Log::info($open_id.'-----'.$out_trade_no);
		//支付结果处理

	}

	//将xml格式转换成数组
	protected function xmlToArray($xml) {

		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);

		$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$val = json_decode(json_encode($xmlstring), true);

		return $val;
	}
}