<?php

namespace T4\Core;

use T4\Core\Exception;

class MultiException
    extends Exception
    implements IArrayAccess, ICollection
{

    use TCollection;

    protected $class = Exception::class;

    public function __construct($class = Exception::class)
    {
        $this->class = $class;
    }

    public function append($value)
    {
        if (!($value instanceof $this->class)) {
            throw new Exception('MultiException class mismatch');
        }
        $this->storage = array_merge($this->storage, [$value]);
        return $this;
    }

    public function prepend($value)
    {
        if (!($value instanceof $this->class)) {
            throw new Exception('MultiException class mismatch');
        }
        $this->storage = array_merge([$value], $this->storage);
        return $this;
    }

    public function addException($message = '', $code = 0)
    {
        $class = $this->class;
        $this->add(new $class($message, $code));
    }

}