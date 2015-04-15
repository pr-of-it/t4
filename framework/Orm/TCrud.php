<?php

namespace T4\Orm;

trait TCrud
{

    protected $isNew = true;
    protected $wasNew = false;
    protected $isDeleted = false;

    public function setNew($new)
    {
        $this->isNew = $new;
        return $this;
    }

    public function isNew()
    {
        return $this->isNew;
    }

    public function wasNew()
    {
        return $this->wasNew;
    }

    public function setDeleted($deleted)
    {
        $this->isDeleted = $deleted;
        return $this;
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    public function refresh()
    {
        if ($this->isNew())
            return $this;
        $class = get_class($this);
        $this->merge($class::findByPk($this->getPk())->toArray());
        return $this;
    }

    /*
     * Find methods
     */

    /**
     * @param string|\T4\Dbal\QueryBuilder $query
     * @param array $params
     * @return \T4\Orm\Model
     */
    public static function findAllByQuery($query, $params = [])
    {
        $driver = static::getDbDriver();
        return $driver->findAllByQuery(get_called_class(), $query, $params);
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder $query
     * @param array $params
     * @return \T4\Orm\Model
     */
    public static function findByQuery($query, $params = [])
    {
        $driver = static::getDbDriver();
        return $driver->findByQuery(get_called_class(), $query, $params);
    }

    /**
     * @param array $options
     * @return \T4\Core\Collection
     */
    public static function findAll($options = [])
    {
        $driver = static::getDbDriver();
        return $driver->findAll(get_called_class(), $options);
    }

    /**
     * @param string $column
     * @param mixed $value
     * @param array $options
     * @return \T4\Core\Collection
     */
    public static function findAllByColumn($column, $value, $options = [])
    {
        $driver = static::getDbDriver();
        return $driver->findAllByColumn(get_called_class(), $column, $value, $options);
    }

    /**
     * @param string $column
     * @param mixed $value
     * @param array $options
     * @return \T4\Orm\Model
     */
    public static function findByColumn($column, $value, $options = [])
    {
        $driver = static::getDbDriver();
        return $driver->findByColumn(get_called_class(), $column, $value, $options);
    }

    /**
     * @param mixed $value
     * @return \T4\Orm\Model
     */
    public static function findByPK($value)
    {
        return static::findByColumn(static::PK, $value);
    }

    public static function countAll($options = [])
    {
        $driver = static::getDbDriver();
        return $driver->countAll(get_called_class(), $options);
    }

    public static function countAllByColumn($column, $value, $options = [])
    {
        $driver = static::getDbDriver();
        return $driver->countAllByColumn(get_called_class(), $column, $value, $options);
    }

}