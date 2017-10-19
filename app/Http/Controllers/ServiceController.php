<?php

namespace App\Http\Controllers;

use App\Helpers\ReturnMessage;
use App\Models\CarSeries;
use App\Models\Service;

class ServiceController extends Controller
{
	/**
	 * 服务列表
	 * */
//	private function serviceList()
	public function serviceList()
	{
		return Service::getServiceList();
	}

	/**
	 * 车系列表
	 * */
	//	private function serviceList()
	public function carSeriesList()
	{
		return CarSeries::getCarSeriesList();
	}

}