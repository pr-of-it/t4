<?php

namespace T4\Core;

trait TCollection
{
    use TArrayAccess;

    /**
     * @param $value
     * @return $this
     */
    public function add($value)
    {
        return $this->append($value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function prepend($value)
    {
        $this->storage = array_merge([$value], $this->storage);
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function append($value)
    {
        $this->storage = array_merge($this->storage, [$value]);
        return $this;
    }

    /**
     * @param \T4\Core\IArrayable|array $values
     * @return $this
     */
    public function merge($values)
    {
        if ($values instanceof IArrayable) {
            $values = $values->toArray();
        } else {
            $values = (array)$values;
        }
        $this->storage = array_merge($this->storage, $values);
        return $this;
    }

    public function slice($offset, $length=null)
    {
        return new static(array_slice($this->storage, $offset, $length));
    }

    public function existsElement(array $attributes)
    {
        if (empty($attributes))
            return false;
        foreach ($this as $element) {
            $elementAttributes = [];
            if (!is_array($element) && !(is_object($element) && $element instanceof \Traversable)) {
                continue;
            }
            foreach ($element as $key => $val) {
                if (array_key_exists($key, $attributes))
                    $elementAttributes[$key] = $val;
            }
            if ($attributes == $elementAttributes)
                return true;
        }
        return false;
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
     * @param callable $callback
     * @return static
     */
    public function uasort(callable $callback) {
        $copy = $this->toArray();
        uasort($copy, $callback);
        return new static($copy);
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function uksort(callable $callback) {
        $copy = $this->toArray();
        uksort($copy, $callback);
        return new static($copy);
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

    /**
     * @param callable $callback
     * @return static
     */
    public function sort(callable $callback)
    {
        return $this->uasort($callback);
    }

    /**
     * @return static
     */
    public function reverse() {
        $reversed = array_reverse($this->toArray(), true);
        return new static($reversed);
    }


    /**
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return new static(array_values(array_map($callback, $this->toArray())));
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback)
    {
        return new static(array_values(array_filter($this->toArray(), $callback)));
    }

    /**
     * @param mixed $start
     * @param callable $callback
     * @return mixed
     */
    public function reduce($start, callable $callback)
    {
        return array_reduce($this->toArray(), $callback, $start);
    }

    /**
     * @param mixed $what
     * @return array
     */
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

    /**
     * @param $by
     * @return array|static[]
     */
    public function group($by)
    {
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
            $ret[$key]->add($element);
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