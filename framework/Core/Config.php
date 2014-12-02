<?php

namespace T4\Core;

class Config extends Std {

    public function __construct($path=null) {
        if ( null !== $path ) {
            $this->load($path);
        }
    }

    public function load($path)
    {
        if ( !is_readable($path) ) {
            throw new Exception('Config file ' . $path . ' is not found or is not readable');
        }
        $this->fromArray(include($path));
    }

}