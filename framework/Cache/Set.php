<?php

namespace T4\Cache;

use T4\Core\Config;
use T4\Core\Std;

class Set
    extends Std
{

    public function __construct(Config $config)
    {
        foreach ($config as $name => $driverConfig) {
            $this->$name = $driverConfig;
        }
    }

    protected function innerGet($key)
    {
        $value = parent::innerGet($key);
        if ($value instanceof Config) {
            $value = Driver::instance($value);
            $this->key = $value;
        }
        return $value;
    }

}