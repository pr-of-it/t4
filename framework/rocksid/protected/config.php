<?php

return [
    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'rocksid',
            'user' => 'mysql',
            'password' => 'mysql',
        ]
    ],
    'auth' => [
        'expire' => 31536000 // 1 year
    ],
    'mail' => [
        'method' => 'php',
    ],
    'extensions' => [
        'jquery' => [
        ],
        'bootstrap' => [
        ],
    ],
];