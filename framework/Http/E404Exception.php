<?php

namespace T4\Http;

class E404Exception
    extends Exception
{
    
    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }

}