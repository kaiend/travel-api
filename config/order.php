<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/30
 * Time: 15:35
 */
return [
    'car_series' =>[
        18 => '宝马',
        19 => '奔驰',
        20=> '宝马7系',
        21=> '宝马5系',
        22=>'奔驰S',
    ],
    'type' =>[
        21,39,40,41 =>'包车',
        26 =>'接机',
        27 =>'送机',
        28 =>'接站',
        29 =>'送站',
        30,33,34 =>'特殊路线',
        31 =>'活动用车',
        32 =>'内部用车',
    ],
    'app_server' =>[
        20 => '定制包车',
        21 => '包车',
        22 => '接送机',
        23 => '接送站',
        30 => '特殊路线',
        31 => '活动用车',
        32 => '内部用车'
    ],
    'detail'=>[
        'order_number' =>'订单编号',
        'orders_name'=>'下单人',
        'server_title' =>'服务类型',
        'appointment'=>'用车时间',
        'passenger_name'=> '乘车人',
        'passenger_people'=>'乘车人数',
        'passenger_phone'=>'联系电话',
        'car_id' => '乘坐车型',
        'price'=> '预计费用',
        'bottom_number' =>'酒店单号',
        'remarks' =>'备注',
    ],
    'flight'=>[
        '131'=>[
            [
                'coordinate'=> '116.594566,40.086792',
                'name' => '北京首都机场T1航站楼'
            ],
            [
                'coordinate'=> '116.600726,40.086705',
                'name' => '北京首都机场T2航站楼'
            ],
            [
                'coordinate'=> '116.619758,40.072776',
                'name' => '北京首都机场T3航站楼'
            ],
            [
                'coordinate'=> '116.400712,39.790456',
                'name' => '北京南苑机场'
            ],
        ]
    ],

    'train'=>[
        '131'=>[
            [
                'coordinate'=> '116.433737,39.90978',
                'name' => '北京站'
            ],
            [
                'coordinate'=> '116.359489,39.951655',
                'name' => '北京北站'
            ],
            [
                'coordinate'=> '116.385814,39.871182',
                'name' => '北京南站'
            ],
            [
                'coordinate'=> '116.328097,39.900858',
                'name' => '北京西站'
            ],
            [
                'coordinate'=> '116.489951,39.907681',
                'name' => '北京东站'
            ],
        ]
    ]
];
//[
//    'coordinate'=> '',
//    'name' => ''
//],