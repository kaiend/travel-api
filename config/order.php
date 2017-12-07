<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/30
 * Time: 15:35
 */
return [
    'car_series' =>[
        8 => '宝马',
        9 => '奔驰',
        10=> '宝马5系',
        11=>'奔驰S350',
        12 =>'奥迪',
        13 =>'奥迪A6L'
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
    ]
];