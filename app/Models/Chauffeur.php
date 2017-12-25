<?php
namespace App\Models;

use App\Helpers\Common;

class Chauffeur extends Model
{
	protected $guarded = [];

	/**
	 * 用户表
	 */
	protected $table = 'chauffeur';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = [];
    /**
     * 关闭自动更新create_at等字段
     * @var bool
     */
    public $timestamps = false;

    /**
     * 获取用户信息
     * @param $id
     * @return mixed
     */
    public static function  getUserFirst( $id )
    {
        $obj= static::where(['id'=> $id])->first();
        $data = Common::json_array($obj);
        return $data;
    }

    /**
     * 修改司机某字段
     * @param array $where
     * @param array $input
     * @return mixed
     */
    public static function modifyUser( array $where, array $input)
    {
        $obj = static::where( $where )->update($input);
        $data = Common::json_array($obj);
        return $data;
    }

    public static function test(){
        $flights=Chauffeur::all();


        $flights =Common::json_array( $flights);
        return  $flights;
    }

}