<?php

namespace T4\Core;

class Collection
    implements \ArrayAccess, \Countable, \IteratorAggregate, IArrayable
{
    use TArrayAccess;

    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }

    public function prepend($value)
    {
        $this->storage = array_merge([$value], $this->storage);
        return $this;
    }

    public function append($value)
    {
        $this->storage = array_merge($this->storage, [$value]);
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
        $copy = $this->toArray();
        asort($copy);
        return new static($copy);
    }

    /**
     * @return static
     */
    public function ksort()
    {
        $copy = $this->toArray();
        ksort($copy);
        return new static($copy);
    }

    /**
     * @param callable $cmp_function
     * @return static
     */
    public function uasort($cmp_function) {
        $copy = $this->toArray();
        uasort($copy, $cmp_function);
        return new static($copy);
    }

    /**
     * @param callable $cmp_function
     * @return static
     */
    public function uksort($cmp_function) {
        $copy = $this->toArray();
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
        $copy = $this->toArray();
        natsort($copy);
        return new static($copy);
    }

    /**
     * @return static
     */
    public function natcasesort() {
        $copy = $this->toArray();
        natcasesort($copy);
        return new static($copy);
    }

    public function map(callable $callback)
    {
        return new static(array_values(array_map($callback, $this->toArray())));
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

}