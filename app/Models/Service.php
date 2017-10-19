<?php
namespace App\Models;

class Service extends Model
{
	protected $guarded = [];

	/**
	 * 服务表
	 */
	protected $table = 'service';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = ['sort','status'];

	/**
	 * 获取服务列表
	 *
	 * @return array
	 * */
	public static function getServiceList()
	{
		return static::where('status','1')->orderBy('sort','asc')->get()->toArray();
	}

	/**
	 * 获取某一项服务
	 *
	 * @param $where
	 * @return array
	 * */
	public static function getUserFirst( array $where)
	{
		return static::where( $where )->first();
	}

}