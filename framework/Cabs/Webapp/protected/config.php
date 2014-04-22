<?php

return [
    'db' => [
        'default' => [
            'driver' => '{{driver}}',
            'host' => '{{host}}',
            'dbname' => '{{dbname}}',
            'user' => '{{user}}',
            'password' => '{{password}}',
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