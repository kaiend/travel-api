<?php
namespace App\Models;

class User extends Model
{
	protected $guarded = [];

	/**
	 * 用户表
	 */
	protected $table = 'personal_user';

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
	 * @return array
	 * */
	public static function getUserFirst( array $where)
	{
        $data = static::where( $where )->first();
        return $data;
	}

	/**
	 * 根据条件修改
	 * @param array $where
	 * @param array $input
	 * @return int
	 * */
	public static function modifyUser( array $where, array $input)
	{
		return static::where( $where )->update($input);
	}

}