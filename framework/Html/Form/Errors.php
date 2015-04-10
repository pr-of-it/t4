<?php

namespace T4\Html\Form;

use T4\Core\MultiException;

class Errors
    extends MultiException
{

    protected $fields = [];

    public function add($field, $error = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null)
    {
        if ($error instanceof Exception) {
            if ($error instanceof $this->class) {
                $this[] = $error;
            } else {
                throw new Exception('Incompatible exception class' . get_class($error));
            }
        } else {
            $class = $this->class;
            $error = new $class($error, $code, $severity, $filename, $lineno, $previous);
            $this[] = $error;
        }

        $this->fields[$field][] = $error;
        return $this;
    }

    public function getForField($field)
    {
        return $this->fields[$field];
    }

}