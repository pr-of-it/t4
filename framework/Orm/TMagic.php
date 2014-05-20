<?php

namespace T4\Orm;

use T4\Core\Std;

trait TMagic {

    public function __get($key)
    {
        $class = get_class($this);

        // Такое свойство есть в перечне полей модели, но установлено не было
        $columns = $class::getColumns();
        if (isset($columns[$key]))
            return null;

        // Такое свойство есть в перечне связей, но установлено не было
        $relations = $class::getRelations();
        $keys = explode('.', $key);
        $key = array_shift($keys);

        if (isset($relations[$key])) {
            $this->{$key} = $this->getRelationLazy($key);
            if (empty($keys)) {
                return $this->{$key};
            } else {
                if ( $this->{$key} instanceof Std)
                    return $this->{$key}->{implode('.', $keys)};
                else
                    return null;
            }
        }

        // Ни один из вариантов не сработал
        throw new Exception('No such column or relation: ' . $key . ' in model of ' . $class . ' class');

    }

    public function __isset($key)
    {
        $class = get_class($this);

        // Такое свойство есть в перечне полей модели, но установлено не было
        $columns = $class::getColumns();
        if (isset($columns[$key]))
            return true;

        // Такое свойство есть в перечне связей, но установлено не было
        $relations = $class::getRelations();
        $keys = explode('.', $key);
        $key = array_shift($keys);
        if (isset($relations[$key]))
            return true;

        return false;
    }

    public static function __callStatic($method, $argv)
    {
        $class = get_called_class();
        $extensions = $class::getExtensions();
        foreach ( $extensions as $extension ) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\'.ucfirst($extension);
            $extension = new $extensionClassName;
            try {
                if (method_exists($extension, 'callStatic')) {
                    $result = $extension->callStatic($class, $method, $argv);
                    return $result;
                }
            } catch (\T4\Orm\Extensions\Exception $e) {
                continue;
            }
        }
    }

    public function __call($method, $argv)
    {
        $class = get_class($this);
        $extensions = $class::getExtensions();
        foreach ( $extensions as $extension ) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\'.ucfirst($extension);
            $extension = new $extensionClassName;
            try {
                if (method_exists($extension, 'call')) {
                    $result = $extension->call($this, $method, $argv);
                    return $result;
                }
            } catch (\T4\Orm\Extensions\Exception $e) {
                continue;
            }
        }
    }

}