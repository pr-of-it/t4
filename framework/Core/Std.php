<?php

namespace T4\Core;

use ArrayAccess;
use Traversable;

class Std
    extends \stdClass
    implements ArrayAccess, \Countable, \IteratorAggregate, IArrayable
{

    public function __construct($data=null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }

    /**
     * ArrayAccess implementation
     */

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    /**
     * Countable implementation
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count((array)$this);
    }

    /**
     * Arrayable implementation
     */

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ( $this as $key => $value ) {
            if ( $value instanceof static ) {
                $data[$key] = $value->toArray();
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * @param array $data
     * @return \T4\Core\Std $this
     */
    public function fromArray($data) {
        foreach ( $data as $key => $value ) {
            if ( is_array($value) ) {
                $this->{$key} = new static;
                $this->{$key}->fromArray($value);
            } else {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * @param \T4\Core\Std | array $obj
     * @return \T4\Core\Std $this
     */
    public function merge($obj)
    {
        if ($obj instanceof self) {
            $obj = $obj->toArray();
        } else {
            $obj = (array)$obj;
        }
        $this->fromArray(array_merge($this->toArray(), $obj));
        return $this;
    }

    /**
     * Применяется в моделях
     * Заполняет модель внешними данными
     * @param $data
     * @return $this
     */
    public function fill($data)
    {
        return $this->merge($data);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }


    public function __isset($key)
    {
        $method = 'get' . ucfirst($key);
        return
            isset($this->{$key}) || method_exists($this, $method);
    }

    public function __get($key)
    {
        $method = 'get' . ucfirst($key);
        if ( method_exists($this, $method) )
            return $this->$method();
        else
            return $this->$key;
    }

    public function __set($key, $value)
    {
        $method = 'set' . ucfirst($key);
        if ( method_exists($this, $method) )
            $this->$method($value);
        else
            $this->$key = $value;
    }

}