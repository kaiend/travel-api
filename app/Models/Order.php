<?php
namespace App\Models;

use App\Helpers\Common;

class Order extends Model
{
	protected $guarded = [];

	/**
	 * 充值记录
	 */
	protected $table = 'order';

	/**
	 * 允许批量赋值的字段
	 * */
	protected $fillable = [];

	/**
	 * 隐藏字段
	 * */
	protected $hidden = [];

	/**
	 * 获取订单信息
	 *
	 * @param $where
	 * @return array
	 * */
	public static function getOrderFirst( array $where)
	{

        $obj=  static::where( $where )->first()->toArray();
		return $obj;
	}
	/**
	 * 根据条件修改
	 * @param array $where
	 * @param array $input
	 * @return int
	 * */
	public static function modifyOrder( array $where, array $input)
	{
	    $obj =static::where( $where )->update($input);
		return $obj;
	}
	/**
	 * 获取订单列表
	 * @param array $where
	 * @return array
	 * */
	public static function orderList( array $where )
	{
        $obj =static::where( $where )->orderBy('created_at','desc')->get()->toArray();
		return $obj;
	}
    /**
     * 今日新增订单
     * @param $where
     * @return int
     */
    public static function getNewOder(array $where)
    {
        $t = time();
        $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        $obj =static::where($where)->whereBetween('created_at',[$start,$end])->count();
        return $obj;
    }
    /**
     * 月结算总量
     * @param $where
     * @return int
     */
    public static function getMonthMum(array $where)
    {
        $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
        $start = strtotime($BeginDate);
        $end =strtotime(date('Y-m-d', strtotime("$BeginDate +1 month")))-1;
        $obj =static::where($where)->whereBetween('created_at',[$start,$end])->count();
        return $obj;
    }
}