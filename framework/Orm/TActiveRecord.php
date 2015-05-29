<?php

namespace T4\Orm;

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
            $class = get_class($this);
            $driver = $class::getDbDriver();
            $driver->save($this);
            if ($this->isNew())
                $this->wasNew = true;
            $this->setNew(false);
        } else {
            return false;
        }
        $this->afterSave();
        return $this;
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