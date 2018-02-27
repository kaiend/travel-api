<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/26
 * Time: 11:25
 */

namespace App\Models;


class Log extends Model
{
    protected $guarded = [];

    /**
     * 用户表
     */
    protected $table = 'log';

    /**
     * 允许批量赋值的字段
     * */
    protected $fillable = [];

    /**
     * 隐藏字段
     * */
    protected $hidden = [];

    public static function  getLogFirst( $id )
    {
        $obj= static::where(['id'=> $id])->first();
        return json_decode(json_encode($obj),true);
    }
    public static function  getLogList(array $where  )
    {
        $obj= static::where($where)->get();
        return json_decode(json_encode($obj),true);
    }
}