<?php

namespace App\Library\WxPay;

require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';

class WxPay extends \WxPayNotify
{
	//创建订单
	public function createOrder($data) {
		//         初始化值对象
		$input = new \WxPayUnifiedOrder();
		//         文档提及的参数规范：商家名称-销售商品类目
		$input->SetBody($data['body']);
		//         订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
		$input->SetOut_trade_no($data['out_trade_no']);
		//         费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
		$input->SetTotal_fee($data['total_fee']);
		$input->SetNotify_url("http://travel.times-vip.com/api//WxNotify");//需要自己写的notify.php
		$input->SetTrade_type("JSAPI");
		//         由小程序端传给后端或者后端自己获取，写自己获取到的，
		$input->SetOpenid($data['openid']);
//		$input->SetOpenid($this->getSession()->openid);
		//         向微信统一下单，并返回order，它是一个array数组
		$order = \WxPayApi::unifiedOrder($input);
		//         json化返回给小程序端
//		header("Content-Type: application/json");
//		echo $this->getJsApiParameters($order);
		return $this->getJsApiParameters($order);
	}

	private function getJsApiParameters($UnifiedOrderResult)
	{    //判断是否统一下单返回了prepay_id
		if(!array_key_exists("appid", $UnifiedOrderResult)
			|| !array_key_exists("prepay_id", $UnifiedOrderResult)
			|| $UnifiedOrderResult['prepay_id'] == "")
		{
			throw new \WxPayException("参数错误");
		}
		$jsapi = new \WxPayJsApiPay();
		$jsapi->SetAppid($UnifiedOrderResult["appid"]);
		$timeStamp = time();
		$jsapi->SetTimeStamp("$timeStamp");
		$jsapi->SetNonceStr(WxPayApi::getNonceStr());
		$jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
		$jsapi->SetSignType("MD5");
		$jsapi->SetPaySign($jsapi->MakeSign());
		$parameters = json_encode($jsapi->GetValues());
		return $parameters;
	}

	//这里是服务器端获取openid的函数
//    private function getSession($code) {
//        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.\WxPayConfig::APPID.'&secret='.\WxPayConfig::APPSECRET.'&js_code='.$code.'&grant_type=authorization_code';
//        $response = json_decode(file_get_contents($url));
//        return $response;
//    }

	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new \WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = \WxPayApi::orderQuery($input);

		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}

	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}

}
