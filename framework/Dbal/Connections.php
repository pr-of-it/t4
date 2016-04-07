<?php

namespace T4\Dbal;

use T4\Core\Config;
use T4\Core\Std;

class Connections
    extends Std
{

    public function __construct(Config $config)
    {
        foreach ($config as $name => $connectionConfig) {
            $this->$name = $connectionConfig;
        }
    }

    protected function innerGet($key)
    {
        $value = parent::innerGet($key);
        if ($value instanceof Config) {
            $value = new Connection($value);
            $this->key = $value;
        }
        return $value;
    }

}