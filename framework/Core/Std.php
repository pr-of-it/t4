<?php

namespace T4\Core;

use Traversable;

class Std
    extends \stdClass
    implements \ArrayAccess, \Countable, \IteratorAggregate, IArrayable
{

    public function __construct(array $data=[])
    {
        set_error_handler([$this, 'errorHandler'], E_WARNING);
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if ('Creating default object from empty value' == $errstr && isset($errcontext['obj']) && $errcontext['obj'] instanceof static) {
            return true;
        }
        return false;
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
        return count(get_object_vars($this));
    }

    /**
     * Arrayable implemetation
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

    public function merge($obj)
    {
        $this->fromArray($obj->toArray());
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
        $this->fromArray($data);
        return $this;
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
}