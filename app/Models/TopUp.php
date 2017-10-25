<?php
namespace App\Models;

class TopUp extends Model
{
	protected $guarded = [];

	/**
	 * 充值记录
	 */
	protected $table = 'top_up';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = ['created_at'];

}