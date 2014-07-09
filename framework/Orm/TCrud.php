<?php

namespace T4\Orm;

trait TCrud
{

    protected $isNew = true;
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

    public function setDeleted($deleted)
    {
        $this->isDeleted = $deleted;
        return $this;
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /*
     * Find methods
     */

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

    /*
     * Save model methods
     */

    public function beforeSave()
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            /** @var \T4\Orm\Extension $extension */
            $extension = new $extensionClassName;
            if (!$extension->beforeSave($this))
                return false;
        }
        return true;
    }

    public function save()
    {
        if ($this->beforeSave()) {
            $class = get_class($this);
            $driver = $class::getDbDriver();
            $driver->save($this);
            $this->setNew(false);
        } else {
            return false;
        }
        $this->afterSave();
        return $this;
    }

    public function afterSave()
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            /** @var \T4\Orm\Extension $extension */
            $extension = new $extensionClassName;
            if (!$extension->afterSave($this))
                return false;
        }
        return true;
    }

    /*
     * Delete model methods
     */

    public function beforeDelete()
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            /** @var \T4\Orm\Extension $extension */
            $extension = new $extensionClassName;
            if (!$extension->beforeDelete($this))
                return false;
        }
        return true;
    }

    public function delete()
    {
        if ($this->isNew())
            return false;
        if ($this->beforeDelete()) {
            $class = get_class($this);
            $driver = $class::getDbDriver();
            $driver->delete($this);
            $this->setDeleted(true);
        } else {
            return false;
        }
        $this->afterDelete();
        return $this;
    }

    public function afterDelete()
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            /** @var \T4\Orm\Extension $extension */
            $extension = new $extensionClassName;
            if (!$extension->afterDelete($this))
                return false;
        }
        return true;
    }

}