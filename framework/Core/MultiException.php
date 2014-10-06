<?php

namespace T4\Core;

class MultiException
    extends Exception
    implements \IteratorAggregate
{

    protected $errors = [];

    public function __construct($message = null, $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous=null)
    {
        if (null !== $message)
            $this->addError($message, $code);
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }

    public function addError($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous=null)
    {
        $this->errors[] = new Exception($message, $code, $severity, $filename, $lineno, $previous);
    }

    public function isEmpty()
    {
        return 0 === count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->errors);
    }
}