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

    public function isNew()
    {
        return $this->isNew;
    }

    public function setDeleted($deleted)
    {
        $this->isDeleted = $deleted;
    }

    public function isDeleted()
    {
        return $this->isDeleted;
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
        $driver = static::getDbDriver();
        $driver->save($this);
        $this->setNew(false);
    }

    public function delete()
    {
        $driver = static::getDbDriver();
        $driver->delete($this);
        $this->setDeleted(true);
    }

}