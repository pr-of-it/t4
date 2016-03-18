<?php

namespace T4\Core;

class Std
    implements \ArrayAccess, \Countable, \IteratorAggregate, IArrayable
{

    /**
     * @implement \ArrayAccess
     */
    use TStdGetSet;

    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }

    /**
     * Countable implementation
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->__data);
    }

    /**
     * Arrayable implementation
     */

    /**
     * @param array $data
     * @return \T4\Core\Std $this
     */
    public function fromArray($data)
    {
        $data = (array)$data;
        foreach ($data as $key => $value) {
            if (is_null($value) || is_scalar($value) || $value instanceof \Closure) {
                $this->innerSet($key, $value);
            } else {
                $this->innerSet($key, new static);
                $this->{$key}->fromArray($value);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->__data as $key => $value) {
            if ($value instanceof self) {
                $data[$key] = $value->toArray();
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @return \T4\Core\Std $this
     */
    public function append($data)
    {
        $this[] = $data;
        return $this;
    }

    /**
     * @param \T4\Core\IArrayable|array $obj
     * @return \T4\Core\Std $this
     */
    public function merge($obj)
    {
        if ($obj instanceof IArrayable) {
            $obj = $obj->toArray();
        } else {
            $obj = (array)$obj;
        }
        foreach ($obj as $key => $value)
            $this->$key = $value;
        return $this;
    }

    /**
     * @param \T4\Core\IArrayable|array $data
     * @return \T4\Core\Std $this
     * @throws \T4\Core\MultiException
     */
    public function fill($data)
    {
        if ($data instanceof IArrayable) {
            $data = $data->toArray();
        } else {
            $data = (array)$data;
        }

        $errors = new MultiException();

        foreach ($data as $key => $value) {
            try {
                $this->$key = $value;
            } catch (\Exception $e) {
                if ($e instanceof MultiException) {
                    $errors->merge($e);
                } else {
                    $errors->add($e);
                }
            }
        }

        if (!$errors->isEmpty()) {
            throw $errors;
        }

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->__data);
    }

}