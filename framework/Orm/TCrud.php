<?php

namespace T4\Orm;


trait TCrud
{

    protected $isNew = true;
    protected $isDeleted = false;

    public function setNew($new)
    {
        $this->isNew = $new;
    }

    public function setDeleted($deleted)
    {
        $this->isDeleted = $deleted;
    }

    public static function findAllByColumn($column, $value)
    {
        $driver = static::getDbDriver();
        return $driver->findAllByColumn(get_called_class(), $column, $value);
    }

    public static function findByColumn($column, $value)
    {
        $driver = static::getDbDriver();
        return $driver->findByColumn(get_called_class(), $column, $value);
    }

    public static function findByPK($value)
    {
        return static::findByColumn(static::PK, $value);
    }

    public function save()
    {
        $this->isNew = false;
    }

    public function delete()
    {
        $this->isDeleted = true;
    }

}