<?php

namespace T4\Core;


trait TSingleton
{

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    public static function getInstance($new = false)
    {
        static $instance = null;
        if (null === $instance || $new)
            $instance = new static;
        return $instance;
    }

}