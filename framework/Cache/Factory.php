<?php

namespace T4\Cache;

class Factory
{

    /**
     * @param string $name
     * @return \T4\Cache\ACache
     */
    public static function getInstance($name = null)
    {
        $className = '\T4\Cache' . $name;
        if (null !== $name && class_exists($className))
            return new $className;
        else
            return new Local();
    }

}