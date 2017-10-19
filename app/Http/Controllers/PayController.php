<?php

namespace App\Http\Controllers;

use App\Http\Pay\WxPay\WxPay;
use App\Http\Validators\PayValidator;
use Illuminate\Http\Request;

class PayController extends Controller
{

	/**
	 * 生成微信订单
	 * @author yxk
	 * @param $request
	 * */
	public function WxOrder( Request $request )
	{
		$res = (new WxPay)->createOrder(PayValidator::WxOrder($request));
		header("Content-Type: application/json");
		echo $res;
	}

	/**
	 * 微信支付回调
	 * */
	public function WxNotify(  )
	{
		$postXml = $GLOBALS["HTTP_RAW_POST_DATA"]; //接收微信参数
		if (empty($postXml)) {
			return false;
		}

		$attr = $this->xmlToArray($postXml);

		$total_fee = $attr['total_fee'];
		$open_id = $attr['openid'];
		$out_trade_no = $attr['out_trade_no'];
		$time = $attr['time_end'];


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