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
	protected $hidden = ['created_at'];

}