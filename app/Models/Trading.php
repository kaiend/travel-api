<?php
namespace App\Models;

class Trading extends Model
{
	protected $guarded = [];

	/**
	 * 消费记录
	 */
	protected $table = 'trading';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = [];

}