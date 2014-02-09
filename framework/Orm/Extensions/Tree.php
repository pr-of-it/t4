<?php

namespace T4\Orm\Extensions;

class Tree
{

    public function prepareColumns($columns)
    {
        return $columns + [
            '__lft' => ['type' => 'int'],
            '__rgt' => ['type' => 'int'],
            '__lvl' => ['type' => 'int'],
        ];
    }

    public function prepareIndexes($indexes)
    {
        return $indexes + [
            ['columns' => '__lft'],
            ['columns' => '__rgt'],
            ['columns' => '__lvl'],
        ];
    }

} 