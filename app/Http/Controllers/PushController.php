<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6
 * Time: 14:59
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use JPush\Client;

class PushController extends Controller
{
    private $appKey ='50505e64af2ea4b5e8e27e26';
    private $master_secret ='f90b3ccdce62056bb134aaaf';
    private function newClient()
    {
        $client =new Client($this->appKey,$this->master_secret);

        return $client;
    }

    public function pushStatus( Request $request )
    {
        $arr =$request->only('order_id','status');
        $jpush =$this->newClient();
        dd( $jpush);die;
//        $response = $jpush->push()
//            ->setPlatform('all')
//            ->addAllAudience()
//            ->setNotificationAlert('hello tp3.2')
//            ->send();
//        print_r($response);
    }
}