<?php

namespace T4\Core;

class Collection extends \ArrayObject
{

    function prepend($value) {
        return $this->exchangeArray(array_merge([$value], $this->getArrayCopy()));
    }

    function append($value) {
        return $this->exchangeArray(array_merge($this->getArrayCopy(),[$value]));
    }


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