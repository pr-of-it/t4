<?php

namespace App\Models;

use T4\Orm\Model;

class Blocks
    extends Model
{
    public static $schema = [
        'table' => '__blocks',
        'columns' => [
            'section'   => ['type'=>'int'],
            'path'      => ['type'=>'string'],
            'options'   => ['type'=>'text'],
            'order'     => ['type'=>'int'],
        ],
    ];
}