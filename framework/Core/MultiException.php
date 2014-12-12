<?php

namespace T4\Core;

class MultiException
    extends Exception
    implements \IteratorAggregate, \Countable
{

    protected $exceptions;
    protected $class;

    /**
     * @param string $class
     */
    public function __construct($class = '\T4\Core\Exception')
    {
        $this->class = $class;
        $this->exceptions = new Collection();
    }

    /**
     * @param string|\T4\Core\Exception $error
     * @param int $code
     * @param int $severity
     * @param string $filename
     * @param int $lineno
     * @param null $previous
     * @throws \T4\Core\Exception
     */
    public function add($error = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous=null)
    {
        if ($error instanceof Exception) {
            if ($error instanceof $this->class) {
                $this->exceptions[] = $error;
            } else {
                throw new Exception('Incompatible exception class' . get_class($error));
            }
        } else {
            $class = $this->class;
            $this->exceptions[] = new $class($error, $code, $severity, $filename, $lineno, $previous);
        }
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }

    public function count()
    {
        return $this->exceptions->count();
    }

    public function isEmpty()
    {
        return 0 === $this->count();
    }

    public function getIterator()
    {
        return $this->exceptions->getIterator();
    }
}