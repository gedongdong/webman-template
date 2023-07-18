<?php
return [
    // 默认数据库
    'default'     => 'mysql',
    // 各种数据库配置
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST', 'localhost'),
            'port'        => env('DB_PORT', 3306),
            'database'    => env('DB_DATABASE', 'test'),
            'username'    => env('DB_USERNAME', 'root'),
            'password'    => env('DB_PASSWORD', 'root'),
            'unix_socket' => '',
            'charset'     => env('DB_CHARSET', 'utf8mb4'),
            'collation'   => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix'      => env('DB_PREFIX', ''),
            'strict'      => true,
            'engine'      => null,
        ],
    ],
];
