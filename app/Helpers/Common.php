<?php

namespace App\Helpers;

class Common
{
	/**
	 * 生成密码
	 * @param string $pass
	 * @return string
	 * */
	public static function createPassword( $pass )
	{
		return '###'.md5(md5($pass));
	}

	/**
	 * 生成订单号
	 * */
	public static function createNumber()
	{
		return date('ymds').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 7);
        //return md5('chuxing'.time());
	}

	/**
	 * 转时间格式
	 * @param $data
	 * @return array
	 * */
	public static function formatTime( $data )
	{
		if (empty($data))
			return $data;

		if(count($data) == count($data,1)){
			if (isset($data['created_at']) && $data['created_at']) {
				$data['created_at'] = date('Y-m-d H:i', $data['created_at']);
			}
			if (isset($data['appointment']) && $data['appointment'])
			{
				$data['appointment'] = date('Y-m-d H:i', $data['appointment']);
			}
		}else{
			foreach ($data as &$value){
				if (isset($value['created_at']) && $value['created_at']) {
					$value['created_at'] = date('Y-m-d H:i', $value['created_at']);
				}
				if (isset($value['appointment']) && $value['appointment'])
				{
					$value['appointment'] = date('Y-m-d H:i', $value['appointment']);
				}
			}
		}
		return $data;
	}

    /**
     * 转数组
     * @param $result
     * @return mixed
     */
    public static function json_array($result)
    {
        $result_json = json_encode($result);
        return json_decode($result_json, true);
    }

    /**
     * 时间转换函数
     * @param $timestamp
     * @return string
     */
    public static function timeInterval($start,$end) {
        $str='';
        $format=array('秒钟','分钟','小时','天','个月');
        if(is_numeric($start) && is_numeric($end)){
            $i=$end-$start;
            switch($i){
                case 60>$i: $str=$i.$format[0];break;
                case 3600>$i: $str=round ($i/60).$format[1];break;
                case 86400>$i:
                    $m = $i%3600;
                    switch($m){
                        case $m>60: $str=floor($i/3600).$format[2].floor ($m/60).$format[1];break;
                        case $m<60: $str=$str=round ($i/3600).$format[2];break;
                    }
                    break;
                case 2592000>$i:
                    $str=round ($i/86400).$format[3];break;
                case 31104000>$i: $str=round ($i/2592000).$format[4];break;
                case $i>31104000: $str=date('m-d', $timestamp);break;
            }
        }
        return $str;
    }

    /**
     * 测试消息提醒
     * @param $order_id
     * @param $mid
     * @param $title
     * @param $mark
     * @param $content
     * @param $data
     * @return mixed
     */
    public function goEasy($order_id,$mid,$title,$mark,$content,$data)
    {

        if(strpos($content,'xxx') !== false){
            $preg = preg_replace("\[xxx\]", $data['user'], $content);
        }

        if(strpos($content,'time') !== false){
            $preg = preg_replace("\[time\]", $data['time'], $preg);
        }
        if(!isset($preg)){
            $preg = $content;
        }

        $msg = array(
            'order_id' => $order_id,
            'mid' => $mid,
            'title' => $title,
            'content' => $preg,
            'create_time' => time(),
        );
        $list_id = Db::name('message_list')->insertGetId($msg);

        $data = array(
            'appkey' => 'BC-af1909bf4e844d7f8d9d18604a910fc4',
            'channel' => $mark,
            'content' => $list_id,
        );

        $url = 'http://rest-hangzhou.goeasy.io/publish';
        $result = $this->vpost($url,$data);
        return $result;
    }
    //楼上的demo
//    if($resu){
//    $message_sql = Db::name('message')
//    ->where('condition',1)
//    ->where('status',1)
//    ->find();
//    $message_data = array(
//    'user' => session('name'),
//    'time' => time(),
//    );
//    if($message_sql){
//    $msg = $this->goEasy($result,$message_sql['id'],$message_sql['title'],$message_sql['mark'],$message_sql['content'],$message_data);
//    }
//    $this->success('添加成功',url("adminOrder/index"));
}