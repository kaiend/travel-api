<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/15
 * Time: 15:08
 */

namespace App\Models;


class Hotel extends Model
{
    protected $guarded = [];

    /**
     * 用户表
     */
    protected $table = 'hotel_user';

    /**
     * 允许批量赋值的字段
     * */
    protected $fillable = [];

    /**
     * 隐藏字段
     * */
    protected $hidden = [];

    public static function  getUserFirst( $id )
    {
        $obj= static::where(['id'=> $id])->first();
        return json_decode(json_encode($obj),true);
    }

}