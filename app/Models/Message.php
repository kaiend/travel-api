<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 18:38
 */

namespace App\Models;


class Message extends Model
{
    protected $guarded = [];

    /**
     * 消息设置
     */
    protected $table = 'message';

    /**
     * 允许批量赋值的字段
     * */
    protected $fillable = [];

    /**
     * 隐藏字段
     * */
    protected $hidden = [];

    /**
     * 获取制定通知消息
     * @param array $where
     * @return array
     */
    public static function getMessageFirst( array $where)
    {
        $obj=  static::where( $where )->first()->toArray();
        return $obj;
    }
}