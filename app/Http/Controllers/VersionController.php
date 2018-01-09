<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2018/1/9
 * Time: 16:05
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function getVersion(Request $request)
    {
        $arr = $request->only('from','version');
        switch ($arr['from']){
            case 'Android':
                return response()->json([
                    'code' =>'1000',
                    'info' => 'success',
                    'data' => intval($arr['version'])
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