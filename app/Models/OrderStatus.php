<?php
namespace App\Models;

use App\Helpers\Common;

class OrderStatus extends Model
{
	protected $guarded = [];

	/**
	 * 车系表
	 */
	protected $table = 'order_status';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];
    /**
     * 隐藏字段
     * */
    protected $hidden = ['id','order_number'];
    /**
     * 获取订单的轨迹信息
     * @param $id
     * @return mixed
     */
    public static function  getOrderTrace( $id )
    {
        $obj= static::where(['order_number'=> $id])->orderBy('id','desc')->get();
        $data = Common::json_array($obj);
        return $data;
    }

}