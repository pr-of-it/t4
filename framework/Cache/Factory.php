<?php

namespace T4\Cache;

use T4\Core\Config;

class Factory
{

    /**
     * @param string $name
     * @return \T4\Cache\ACache
     */
    public static function getInstance($name = null, Config $config = null)
    {
        $className = '\T4\Cache' . $name;
        if (null !== $name && class_exists($className))
            return new $className($config);
        else
            return new Local($config);
    }

}