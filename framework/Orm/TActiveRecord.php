<?php

namespace T4\Orm;

use T4\Core\Collection;

/**
 * Class TActiveRecord
 * @package T4\Orm
 *
 * @mixin \T4\Orm\Model
 */
trait TActiveRecord
{

    protected function validate()
    {
        return true;
    }

    public function afterFind()
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            /** @var \T4\Orm\Extension $extension */
            $extension = new $extensionClassName;
            if (!$extension->afterFind($this))
                return false;
        }
        return true;
    }

    protected function beforeSave()
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
        if ($this->validate() && $this->beforeSave()) {
            $this->saveRelationsBefore();
            get_class($this)::getDbDriver()->save($this);
            if ($this->isNew()) {
                $this->wasNew = true;
            }
            $this->setNew(false);
        } else {
            return false;
        }
        $this->saveRelationsAfter();
        $this->afterSave();
        return $this;
    }

    protected function saveRelationsBefore()
    {
        foreach (static::getRelations() as $key => $relation) {
            switch ($relation['type']) {
                case static::BELONGS_TO:
                    if ( null === $this->{$key} || $this->{$key} instanceof Model ) {
                        $this->saveRelationsBeforeBelongsTo($key);
                    }
                    break;
            }
        }
    }

    protected function saveRelationsAfter()
    {
        foreach (static::getRelations() as $key => $relation) {
            switch ($relation['type']) {

                case static::HAS_ONE:
                    if ( !empty($this->{$key}) || $this->{$key} instanceof Model ) {
                        $this->saveRelationsAfterHasOne($key);
                    }
                    break;

                case static::HAS_MANY:
                    if (!empty($this->{$key}) && $this->{$key} instanceof Collection ) {
                        $this->saveRelationsAfterHasMany($key);
                    }
                    break;
                
                case static::MANY_TO_MANY:
                    if ( !empty($this->{$key}) && $this->{$key} instanceof Collection ) {
                        $this->saveRelationsAfterManyToMany($key);
                    }
                    break;
                
            }
        }
    }

    protected function afterSave()
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

    protected function beforeDelete()
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

    protected function afterDelete()
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            /** @var \T4\Orm\Extension $extension */
            $extension = new $extensionClassName;
            $extension->afterDelete($this);
        }
        return true;
    }

}