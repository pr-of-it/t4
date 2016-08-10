<?php

namespace T4\Validation;

use T4\Core\Exception;

class Error
    extends Exception
{

    public $value;

    public function __construct($value = "", $message = "", $code = 0, Exception $previous = null)
    {
        $this->value = $value;
        parent::__construct($message, $code, $previous);
    }

}