<?php

namespace T4\Http;

class Exception
    extends \T4\Core\Exception
{

    const DEFAULT_ERROR_CODE = 400;

    public function __construct($message = '', $code = self::DEFAULT_ERROR_CODE, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}