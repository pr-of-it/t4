<?php

namespace T4\Orm;

use T4\Core\Std;

trait TMagic {

    public function __get($key)
    {
        // Такое свойство установлено в объекте, неважно каким путем, например как Std
        if (isset($this->{$key}))
            return $this->{$key};

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

}