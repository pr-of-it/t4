<?php

namespace App\Models;

use T4\Orm\Model;

class Block
    extends Model
{
    public static $schema = [
        'table' => '__blocks',
        'columns' => [
            'section'   => ['type'=>'int'],
            'path'      => ['type'=>'string'],
            'options'   => ['type'=>'text', 'default'=>'{}'],
            'order'     => ['type'=>'int', 'default'=>0],
        ],
    ];
}