<?php
namespace App\Models;

class User extends Model
{
	protected $guarded = [];

	/**
	 * 用户表
	 */
	protected $table = 'user';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = [];


	/**
	 * 获取用户信息
	 *
	 * @param $where
	 * @param $page
	 * @return array
	 * */
	public static function getNews( array $where , array $page )
	{
		return static::where( $where )->orderBy( 'created_at' , 'desc' )->forPage( $page[ 'page' ] , $page[ 'pageSize' ] )->get()->toArray();
	}

}