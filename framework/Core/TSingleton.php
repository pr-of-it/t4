<?php

namespace T4\Core;

trait TSingleton
//implements ISingleton
{

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
     * @param bool $new
     * @return static
     */
    public static function instance($new = false)
    {
        static $instance = null;
        if (null === $instance || $new)
            $instance = new static;
        return $instance;
    }

}