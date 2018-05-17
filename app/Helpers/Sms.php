<?php
/**
 * Created by PhpStorm.
 * User: yxk
 * Date: 17/10/17
 * Time: 下午5:05
 */
namespace App\Helpers;

class Sms
{

	//螺丝帽api:key
	private $api_key = 'api:key-99ad26d3e050041f9ea0b5c21b0a6dc2';

    /**
     * 发送短信
     *
     * @param $mobile //手机号码
     * @param $msg //短信内容
     * @return mixed
     */
    public function sendSMS( $mobile, $msg )
    {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

	    curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, FALSE);

	    curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_USERPWD  , $this->api_key);

	    curl_setopt($ch, CURLOPT_POST, TRUE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile,'message' => $msg));

	    $res = curl_exec( $ch );
	    curl_close( $ch );
//	    $res  = curl_error( $ch );
	    return $res;
    }

    /**
     * 模拟get请求
     * @param $url
     * @return mixed
     */
    public function get_curl_json($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        if(curl_errno($ch)){
            print_r(curl_error($ch));
        }
        curl_close($ch);
        return json_decode($result,TRUE);
    }



    /**
     * 魔术获取
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * 魔术设置
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
