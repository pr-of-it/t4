<?php

namespace T4\Http;

class E403Exception
    extends Exception
{

    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }

}