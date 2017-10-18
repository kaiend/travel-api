<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Models;

class Model extends Models
{
	/**
	 * 主键
	 * */
	protected $primaryKey = 'id';

	/**
	 * 指定模型是否使用时间戳
	 * @var bool
	 */
	public $timestamps = false;
}