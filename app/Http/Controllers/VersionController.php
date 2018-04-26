<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2018/1/9
 * Time: 16:05
 */

namespace App\Http\Controllers;


use App\Helpers\ReturnMessage;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    /**
     * 版本更新接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVersion(Request $request)
    {
        $arr = $request->only('from');
        switch ($arr['from']){
            case 'Android':
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => [
                        'code' => 5,
                        'name' => '1.0.1',
                        'title' =>'发现新版本（V1.0.1）',
                        'word' =>'新增【财务管理】时刻查看财务汇总
新增【账户统计】浏览所有下单统计，所有数据一目了然！',
                        'url'  =>'https://fir.im/3gct'
                    ]
                ]);
            break;
            case 'IOS':
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => $arr['version']
                ]);
            break;
            default:
                return response()->json([
                    'code' =>'1011',
                    'info' => '失败',
                    'data' =>0
                ]);
        }
    }
}