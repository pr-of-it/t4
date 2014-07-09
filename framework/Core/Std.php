<?php

namespace T4\Core;

use ArrayAccess;
use Traversable;

class Std
    implements ArrayAccess, \Countable, \IteratorAggregate, IArrayable
{

    protected $__data = [];

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
        return isset($this->__data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->__data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->__data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->__data[$offset]);
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
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ( $this->__data as $key => $value ) {
            if ( $value instanceof self ) {
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
        $data = (array)$data;
        foreach ( $data as $key => $value ) {
            if ( is_scalar($value) ) {
                $this->{$key} = $value;
            } else {
                $this->{$key} = new static;
                $this->{$key}->fromArray($value);
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
        $this->__data = array_merge($this->toArray(), $obj);
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
        return new \ArrayIterator($this->__data);
    }

    /*
     * "Magic" methods
     */

    public function __isset($key)
    {
        return
            isset($this->__data[$key]) || method_exists($this, 'get' . ucfirst($key));
    }

    public function __unset($key)
    {
        unset($this->__data[$key]);
    }

    public function __get($key)
    {
        if (!$this->__isset($key)) {
            $debug =  debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0];
            if ($debug['function'] == '__get' && $debug['object'] === $this && $debug['type'] == '->') {
                $property = $debug['args']['0'];
                $line = (file($debug['file'])[$debug['line']-1]);
                if (preg_match('~\-\>' . $property . '\-\>.+\=~', $line, $m)) {
                    $this->__data[$property] = new static;
                    return $this->__data[$property];
                }
            }
            //trigger_error('Undefined property: ' . get_class($this) . '::' . $key, \E_USER_NOTICE);
            return;
        }

        $method = 'get' . ucfirst($key);
        if ( method_exists($this, $method) )
            return $this->$method();

        return $this->__data[$key];
    }

    public function __set($key, $value)
    {
        $method = 'set' . ucfirst($key);
        if ( method_exists($this, $method) )
            $this->$method($value);
        else
            $this->__data[$key] = $value;
    }

}