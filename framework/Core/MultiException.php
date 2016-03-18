<?php

namespace T4\Core;

class MultiException
    extends Exception
    implements IArrayAccess, ICollection
{

    use TCollection {
        append as protected collectionAppend;
        prepend as protected collectionPrepend;
    }

    protected $class = Exception::class;

    public function __construct($class = Exception::class)
    {
        if ( !is_a($class, \Exception::class, true) ) {
            throw new Exception('Invalid MultiException base class');
        }
        $this->class = $class;
    }

    public function append($value)
    {
        if (!($value instanceof $this->class)) {
            throw new Exception('MultiException class mismatch');
        }
        return $this->collectionAppend($value);
    }

    public function prepend($value)
    {
        if (!($value instanceof $this->class)) {
            throw new Exception('MultiException class mismatch');
        }
        return $this->collectionPrepend($value);
    }

    public function addException($message = "", $code = 0, \Exception $previous = null)
    {
        $class = $this->class;
        $this->add(new $class($message, $code, $previous));
    }

}