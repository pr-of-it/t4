<?php

namespace T4\Core;

class Std
    extends \stdClass
    implements \ArrayAccess, \Countable, IArrayable
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

    public function fromArray(array $data) {
        foreach ( $data as $key => $value ) {
            if ( is_array($value) ) {
                $this->{$key} = new static;
                $this->{$key}->fromArray($value);
            } else {
                $this->{$key} = $value;
            }
        }
    }
}