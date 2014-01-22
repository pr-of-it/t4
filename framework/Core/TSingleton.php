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

    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance)
            $instance = new static;
        return $instance;
    }

}