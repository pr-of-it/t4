<?php

namespace T4\Core;

class Collection extends \ArrayObject
{

    public function prepend($value)
    {
        return $this->exchangeArray(array_merge([$value], $this->getArrayCopy()));
    }

    public function append($value)
    {
        return $this->exchangeArray(array_merge($this->getArrayCopy(), [$value]));
    }

    public function existsElement(array $properties = [])
    {
        if (empty($properties))
            return false;
        foreach ($this as $element) {
            $elementProperties = [];
            foreach ($element as $key => $val) {
                if (array_key_exists($key, $properties))
                    $elementProperties[$key] = $val;
            }
            if ($properties == $elementProperties)
                return true;
        }
        return false;
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

    public function collect($what)
    {
        $ret = [];
        foreach ($this as $element) {
            if (is_callable($what)) {
                $ret[] = $what($element);
            } elseif (is_array($element)) {
                $ret[] = $element[$what];
            } else {
                $ret[] = $element->$what;
            }
        }
        return $ret;
    }

}