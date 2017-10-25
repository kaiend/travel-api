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
	}
}