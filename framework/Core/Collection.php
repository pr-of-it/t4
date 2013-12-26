<?php

namespace T4\Core;

class Collection extends \ArrayObject
{

    /**
     * Проброс метода ко всем объектам коллекции
     */
    public function __call($method, array $params = [])
    {
        foreach ($this as $element) {
            call_user_func_array([$element, $method], $params);
        }
    }

}