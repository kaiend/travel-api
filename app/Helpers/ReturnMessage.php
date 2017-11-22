<?php
/**
 * Created by PhpStorm.
 * User: yxk
 * Date: 2017/10/18
 * Time: 上午11:31
 */
namespace App\Helpers;


class ReturnMessage
{
	/**
	 * 操作成功返回
	 * @param $code
	 * @param $info
	 * @param mixed $data
	 * @return json
	 */
	public static function success($info='success', $code = "1000", $data = false )
	{
		$responseData = [
			'code' => (string)$code,
			'info' => $info,
		];

		if ($data !== false && is_array($data))
			$responseData['data'] = self::toString($data);

        if ($data == false) $responseData['data']=[];

		return json_encode($responseData);
	}

	/**
	 * 只返回数据
	 * @param $data
	 * @param $info
	 * @return mixed
	 */
	public static function successData($data, $info='success')
	{
		$code = "1000";

		if (is_object($data))
			$data = $data->toArray();

		if (!is_array($data) || empty($data)) {
			$info = '暂无数据';
			$data = false;
		}
		return self::success($info, $code, $data);
	}

	/**
	 * 返回数组值全部为字符串
	 * @param array $data
	 * @return array
	 */
	public static function toString(array $data)
	{
		foreach ($data as $key => $value) {
			if (is_array($value))
				$data[$key] = self::toString($value);
			elseif (!is_string($value))
				$data[$key] = (string) $value;
		}

		return $data;
	}
}




