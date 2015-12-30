<?php

namespace T4\Orm;

trait TCrud
{

    protected $isNew = true;
    protected $wasNew = false;
    protected $isDeleted = false;

    /**
     * @param bool $new
     * @return $this
     */
    public function setNew($new)
    {
        $this->isNew = $new;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @return bool
     */
    public function wasNew()
    {
        return $this->wasNew;
    }

    /**
     * @param bool $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->isDeleted = $deleted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @return $this
     */
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
     * @return static
     */
    public static function findAllByQuery($query, $params = [])
    {
        $driver = static::getDbDriver();
        $models = $driver->findAllByQuery(get_called_class(), $query, $params);
        $models->afterFind();
        return $models;
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder $query
     * @param array $params
     * @return static
     */
    public static function findByQuery($query, $params = [])
    {
        $driver = static::getDbDriver();
        $model = $driver->findByQuery(get_called_class(), $query, $params);
        if (!empty($model)) {
            $model->afterFind();
        }
        return $model;
    }

    /**
     * @param array $options
     * @return \T4\Core\Collection|static[]
     */
    public static function findAll($options = [])
    {
        $driver = static::getDbDriver();
        $models = $driver->findAll(get_called_class(), $options);
        $models->afterFind();
        return $models;
    }

    /**
     * @param array $options
     * @return static
     */
    public static function find($options = [])
    {
        $driver = static::getDbDriver();
        $model = $driver->find(get_called_class(), $options);
        if (!empty($model)) {
            $model->afterFind();
        }
        return $model;
    }

    /**
     * @param string $column
     * @param mixed $value
     * @param array $options
     * @return \T4\Core\Collection|static[]
     */
    public static function findAllByColumn($column, $value, $options = [])
    {
        $driver = static::getDbDriver();
        $models = $driver->findAllByColumn(get_called_class(), $column, $value, $options);
        $models->afterFind();
        return $models;
    }

    /**
     * @param string $column
     * @param mixed $value
     * @param array $options
     * @return static
     */
    public static function findByColumn($column, $value, $options = [])
    {
        $driver = static::getDbDriver();
        $model = $driver->findByColumn(get_called_class(), $column, $value, $options);
        if (!empty($model)) {
            $model->afterFind();
        }
        return $model;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public static function findByPK($value)
    {
        return static::findByColumn(static::PK, $value);
    }

    /**
     * @param string|\T4\Dbal\QueryBuilder $query
     * @param array $params
     * @return int
     */
    public static function countAllByQuery($query, $params = [])
    {
        $driver = static::getDbDriver();
        return $driver->countAllByQuery(get_called_class(), $query, $params);
    }

    /**
     * @param array $options
     * @return int
     */
    public static function countAll($options = [])
    {
        $driver = static::getDbDriver();
        return $driver->countAll(get_called_class(), $options);
    }

    /**
     * @param string $column
     * @param mixed $value
     * @param array $options
     * @return int
     */
    public static function countAllByColumn($column, $value, $options = [])
    {
        $driver = static::getDbDriver();
        return $driver->countAllByColumn(get_called_class(), $column, $value, $options);
    }

}