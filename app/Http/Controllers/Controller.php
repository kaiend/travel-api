<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 记录用户操作日志
     * @param $userid
     * @param $username
     * @param $operation
     * @param $hotelId
     */
    public function hotelLog($userid,$username,$operation,$hotelId,$content = ''){
        $data['userId'] = $userid;
        $data['name'] = $username;
        $data['operation'] = $operation;
        $data['createTime'] = time();
        $data['hotelId'] = $hotelId;
        $data['companyId'] =$hotelId;
        $data['content'] =$content;

        DB::table('log')->insert($data);
    }
}
