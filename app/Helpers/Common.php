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
}