<?php

namespace T4\Orm;

use T4\Core\Std;

/**
 * Class TMagic
 * @package T4\Orm
 *
 * @mixin \T4\Orm\Model
 */
trait TMagic
{

    public function __isset($key)
    {
        if (parent::__isset($key)) {
            return true;
        }

        /** @var \T4\Orm\Model $class */
        $class = get_class($this);

        if (array_key_exists($key, $class::getColumns())) {
            return true;
        }

        $keys = explode('.', $key);
        $key = array_shift($keys);
        if (array_key_exists($key, $class::getRelations())) {
            return true;
        }

        return false;
    }

    public function __get($key)
    {
        $st = parent::__get($key);
        if (null !== $st) {
            return $st;
        }

        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $relations = $class::getRelations();
        $keys = explode('.', $key);
        $key = array_shift($keys);

        if (isset($relations[$key])) {
            $this->{$key} = $this->getRelationLazy($key, $relations[$key]);
            if (empty($keys)) {
                return $this->{$key};
            } else {
                if ($this->{$key} instanceof Std)
                    return $this->{$key}->{implode('.', $keys)};
                else
                    return null;
            }
        }

    }

    public function __set($key, $value)
    {
        // Relations
        $class = get_class($this);
        $relations = $class::getRelations();
        $keys = explode('.', $key);

        if (isset($relations[$keys[0]])) {
            $this->setRelation($keys, $value);
            return;
        }

        // Non-relation columns
        parent::__set($keys[0], $value);
    }

    /**
     * Вызов статических методов модели, определенных в расширениях
     * @param string $method
     * @param array $argv
     * @return mixed
     * @throws \T4\Orm\Exception
     */
    public static function __callStatic($method, $argv)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_called_class();
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            /** @var \T4\Orm\Extension $extensionClassName */
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            if ($extensionClassName::hasMagicStaticMethod($method)) {
                return call_user_func_array([$extensionClassName, $method], array_merge([$class], $argv));
            }
        }
        throw new Exception('No static method ' . $method . ' found in ORM extensions in model class ' . $class);
    }

    /**
     * Вызов динамических методов модели, определенных в расширениях
     * @param string $method
     * @param array $argv
     * @return mixed
     * @throws \T4\Orm\Exception
     */
    public function __call($method, $argv)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            /** @var \T4\Orm\Extension $extension */
            $extension = new $extensionClassName;
            if ($extension->hasMagicDynamicMethod($method)) {
                return call_user_func_array([$extension, $method], array_merge([$this], $argv));
            }
        }
        throw new Exception('No dynamic method ' . $method . ' found in ORM extensions in model class ' . $class);
    }

}