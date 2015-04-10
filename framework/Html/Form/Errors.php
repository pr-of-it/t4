<?php

namespace T4\Html\Form;

use T4\Core\MultiException;

class Errors
    extends MultiException
{

    protected $errors = [];

    public function add($field, $error = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null)
    {
        $exception = new Exception($error, $code, $severity, $filename, $lineno, $previous);
        $this->errors[$field][] = $exception;
        parent::add($exception);
    }

    public function getErrorsForField($field)
    {
        return $this->errors[$field];
    }

}