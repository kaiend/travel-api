<?php
namespace App\Models;

class CarSeries extends Model
{
	protected $guarded = [];

	/**
	 * 车系表
	 */
	protected $table = 'car_series';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = ['sort','status','created_at'];

	/**
	 * 获取车系列表
	 *
	 * @return array
	 * */
	public static function getCarSeriesList()
	{
		return static::where('status','1')->orderBy('sort','asc')->get()->toArray();
	}

	/**
	 * 获取某一项车系
	 *
	 * @param $where
	 * @return array
	 * */
	public static function getCarSeriesFirst( array $where)
	{
		return static::where( $where )->first();
	}

}