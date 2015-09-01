<?php

namespace T4\Core;

class Collection
    extends \ArrayObject
    implements IArrayable
{

    public function prepend($value)
    {
        $this->exchangeArray(array_merge([$value], $this->getArrayCopy()));
        return $this;
    }

    public function append($value)
    {
        $this->exchangeArray(array_merge($this->getArrayCopy(), [$value]));
        return $this;
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
     * @return static
     */
    public function asort()
    {
        $copy = $this->getArrayCopy();
        asort($copy);
        return new static($copy);
    }

    /**
     * @return static
     */
    public function ksort()
    {
        $copy = $this->getArrayCopy();
        ksort($copy);
        return new static($copy);
    }

    /**
     * @param callable $cmp_function
     * @return static
     */
    public function uasort($cmp_function) {
        $copy = $this->getArrayCopy();
        uasort($copy, $cmp_function);
        return new static($copy);
    }

    /**
     * @param callable $cmp_function
     * @return static
     */
    public function uksort($cmp_function) {
        $copy = $this->getArrayCopy();
        uksort($copy, $cmp_function);
        return new static($copy);
    }

    /**
     * @param \Closure $callback
     * @return static
     */
    public function sort(\Closure $callback)
    {
        return $this->uasort($callback);
    }

    /**
     * @return static
     */
    public function natsort() {
        $copy = $this->getArrayCopy();
        natsort($copy);
        return new static($copy);
    }

    /**
     * @return static
     */
    public function natcasesort() {
        $copy = $this->getArrayCopy();
        natcasesort($copy);
        return new static($copy);
    }

    public function filter(callable $callback)
    {
        return new static(array_values(array_filter($this->toArray(), $callback)));
    }

    public function findAllByAttributes(array $attributes)
    {
        return $this->filter(function ($x) use ($attributes) {
            $elementAttributes = [];
            foreach ($x as $key => $value) {
                if (array_key_exists($key, $attributes)) {
                    $elementAttributes[$key] = $value;
                }
            }
            return $elementAttributes == $attributes;
        });
    }

    public function findByAttributes(array $attributes)
    {
        $allCollection = $this->findAllByAttributes($attributes);
        return $allCollection->isEmpty() ? null : $allCollection[0];
    }

    public function isEmpty()
    {
        return empty($this->getArrayCopy());
    }

    public function collect($what)
    {
        $ret = [];
        foreach ($this as $element) {
            if ($what instanceof \Closure) {
                $ret[] = $what($element);
            } elseif (is_array($element)) {
                $ret[] = $element[$what];
            } elseif (is_object($element)) {
                $ret[] = $element->$what;
            }
        }
        return $ret;
    }

    public function group($by) {
        $ret = [];
        foreach ($this as $element) {
            if ($by instanceof \Closure) {
                $key = $by($element);
            } elseif (is_array($element)) {
                $key = $element[$by];
            } elseif (is_object($element)) {
                $key = $element->$by;
            }
            if (!isset($ret[$key])) {
                $ret[$key] = new static;
            }
            $ret[$key]->append($element);
        }
        return $ret;
    }

    public function __call($method, array $params = [])
    {
        foreach ($this as $element) {
            call_user_func_array([$element, $method], $params);
        }
    }

    /**
     * IArrayable implement
     */

    public function toArray()
    {
        return $this->getArrayCopy();
    }

    public function fromArray($data)
    {
        $this->exchangeArray($data);
        return $this;
    }
}