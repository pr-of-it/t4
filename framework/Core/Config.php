<?php

namespace T4\Core;
use T4\Core\EConfigException;


class Config extends Std {

    public function __construct($path=null) {
        if ( null !== $path ) {
            if ( !is_readable($path) )
                throw new EConfigException('Config file ' . $path . ' is not found or is not readable');
            $this->fromArray(include($path));
        }
    }

} 