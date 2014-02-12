<?php

namespace T4\Orm;

abstract class Extension
{

    public function prepareColumns($columns)
    {
        return $columns;
    }

    public function prepareIndexes($indexes)
    {
        return $indexes;
    }

    public function beforeSave($model)
    {
        return true;
    }

    public function afterSave($model)
    {
        return true;
    }

    public function callStatic($class, $method, $argv)
    {
        throw new Exception('Method ' . $method . ' is not found in extension ' . get_called_class());
    }

    public function call($model, $method, $argv)
    {
        throw new Exception('Method ' . $method . ' is not found in extension ' . get_called_class());
    }

}