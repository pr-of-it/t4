<?php

namespace T4\Cache;

use T4\Core\Config;

class Driver
{

    public static function instance(Config $config)
    {
        if (empty($config->class) || !($config->class instanceof IDriver)) {
            throw new Exception('Invalid cache driver class name');
        }
        $class = $config->class;
        return new $class($config);
    }

}