<?php
/**
 * Created by PhpStorm.
 * User: Aimy
 * Date: 2017/11/30
 * Time: 15:35
 */
return [
    'car_series' =>[
        2 => '宝马',
        3 => '奔驰',
        4 => '别克',
        5 => '奥迪',
        6 => '宝马5系',
        7 => '宝马7系',
        8 => '奔驰S400',
        9 => '奔驰E200',
        10 => '别克GL8',
        11 =>'奔驰唯雅诺',
        12 => '奥迪A8',
        13 => '奥迪A6',
        14 => '奔驰迈巴赫S450'
    ],
    'type' =>[
        20 => '定制用车',
        21 =>'包车服务',
        39 =>'包车服务',
        40 =>'包车服务',
        41 =>'包车服务',
        26 =>'接机服务',
        27 =>'送机服务',
        28 =>'接站服务',
        29 =>'送站服务',
        30 =>'特殊路线服务',
        31 =>'活动用车',
        32 =>'内部用车',
        33 =>'特殊路线服务',
        34 =>'特殊路线服务',
        80 =>'特殊路线服务',
        81 =>'特殊路线服务',
        82 =>'特殊路线服务',
        83 =>'特殊路线服务',
        85 =>'特殊路线服务',
    ],
    'app_server' =>[
        20 => '定制包车',
        22 => '接送机',
        23 => '接送站',
        21 => '包车',
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
        ],
        '332'=>[
            [
                'coordinate'=> '117.368077,39.13701',
                'name' => '天津滨海国际机场T2航站楼'
            ]
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
        ],
        '332' =>[
            [
                'coordinate'=> '117.216586,39.141773',
                'name' => '天津站'
            ],
            [
                'coordinate'=> '117.169986,39.16427',
                'name' => '天津西站'
            ],
            [
                'coordinate'=> '117.067499,39.062802',
                'name' => '天津南站'
            ],
            [
                'coordinate'=> '117.215946,39.172524',
                'name' => '天津北站'
            ],
            [
                'coordinate'=> '117.649419,39.031483',
                'name' => '塘沽站'
            ],
            [
                'coordinate'=> '117.689958,39.011058',
                'name' => '于家堡站'
            ],
            [
                'coordinate'=> '117.404624,40.032737',
                'name' => '蓟州站'
            ],
            [
                'coordinate'=> '117.008631,39.148694',
                'name' => '杨柳青站'
            ],
            [
                'coordinate'=> '117.617575,39.085301',
                'name' => '滨海站'
            ],
            [
                'coordinate'=> '117.767464,39.241501',
                'name' => '滨海北站'
            ],
        ]
    ],
    'trace'=>[
        1 =>'下单',
        2 =>'指派司机',
        3 =>'司机已接单',
        4 =>'备车中',
        5 =>'前往目的地',
        6 =>'到达指定地点',
        7 =>'开始服务',
        8 =>'即将到达目的地',
        9 =>'结束服务'
    ],
    'status_name'=>[
        0 =>'已取消',
        1 =>'已下单',
        2 =>'待执行',
        3 =>'待执行',
        4 =>'待执行',
        5 =>'执行中',
        6 =>'执行中',
        7 =>'执行中',
        8 =>'执行中',
        9 =>'完成',
        10 =>'待审核'
    ],
    'detail_status_name'=>[
        10 =>'等待主管审批',
        9  =>'订单已完成',
        8  =>'司机正在服务',
        7  =>'司机正在服务',
        6  =>'司机正在服务',
        5  =>'司机正在服务',
        4  =>'司机正在服务',
        3  =>'司机已接单',
        2  =>'等待司机接单',
        1  =>'等待调度派单',
        0  =>'已取消'
    ]
];
//[
//    'coordinate'=> '',
//    'name' => ''
//],