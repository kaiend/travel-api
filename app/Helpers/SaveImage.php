<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class SaveImage
{
	/**
	 * 存储出行卡
	 *
	 * @param $filename
	 * @param $file
	 * @return string
	 */
	public static function travelCard($filename, $file)
	{
		$domain = $_SERVER['HTTP_HOST'];
		$destinationPath = '/uploads/travel_card/';

		$suffix = '.png';
		$filename = $filename . $suffix;
//		$mark = '?v=' . time(); //修改URL

		try {
			Image::make($file)->save(public_path().$destinationPath . $filename);
		} catch (\Exception $e) {
			Log::info('save-img-avatar', ['context' => $e->getMessage()]);
		}

		return $domain . $destinationPath . $filename;
	}
}