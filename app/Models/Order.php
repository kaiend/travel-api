<?php
namespace App\Models;

class Order extends Model
{
	protected $guarded = [];

	/**
	 * 充值记录
	 */
	protected $table = 'wx_order';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = [];


	/**
	 * 获取订单信息
	 *
	 * @param $where
	 * @return array
	 * */
	public static function getOrderFirst( array $where)
	{
		return static::where( $where )->first();
	}

	/**
	 * 根据条件修改
	 * @param array $where
	 * @param array $input
	 * @return int
	 * */
	public static function modifyOrder( array $where, array $input)
	{
		return static::where( $where )->update($input);
	}

	/**
	 * 获取订单列表
	 * @param array $where
	 * @return array
	 * */
	public static function orderList( array $where )
	{
		return static::where( $where )->get()->toArray();
	}

}