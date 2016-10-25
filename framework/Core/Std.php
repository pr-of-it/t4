<?php

namespace T4\Core;

class Std
    implements \ArrayAccess, \Countable, \Iterator, IArrayable
{

    /**
     * @implements \ArrayAccess
     * @implements \Iterator
     * @implements \Countable
     */
    use TStdGetSet;

    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }

    /**
     * Arrayable implementation
     */

    /**
     * @param array $data
     * @return $this
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
    public function toArray() : array
    {
        $data = [];
        foreach (array_keys($this->__data) as $key) {
            $value = $this->innerGet($key);
            if ($value instanceof self) {
                $data[$key] = $value->toArray();
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }
    /**
     * @return array
     */
    public function toArrayRecursive() : array
    {
        $data = [];
        foreach (array_keys($this->__data) as $key) {
            $value = $this->innerGet($key);
            if ($value instanceof IArrayable) {
                $data[$key] = $value->toArrayRecursive();
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
     * @return static $this
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

}