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
}