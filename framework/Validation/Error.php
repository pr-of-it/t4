<?php

namespace T4\Validation;

use T4\Core\Exception;


/**
 * Class Error
 * @package T4\Validation
 *
 * @property mixed $value
 */
class Error
    extends Exception
{

    public $value;

    public function __construct($value = null, $message = "", $code = 0, Exception $previous = null)
    {
        $this->value = $value;
        parent::__construct($message, $code, $previous);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($val)
    {
        $this->value = $val;
        return $this;
    }

}