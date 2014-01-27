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
    'extensions' => [
        'jquery' => [
            'className' => 'Jquery\\Extension'
        ],
        'bootstrap' => [
            'className' => 'Bootstrap\\Extension'
        ],
    ],
];