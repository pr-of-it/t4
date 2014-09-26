<?php

namespace T4\Html\Form;

class Exception
    extends \T4\Core\Exception
{

    protected $errors = [];

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function setError($field, $code, $message='')
    {
        $this->errors[$field][$code] = $message;
    }

    public function getErrors($field='')
    {
        if (empty($field))
            return $this->errors;
        elseif (isset($this->errors[$field]))
            return $this->errors[$field];
        return [];
    }

}