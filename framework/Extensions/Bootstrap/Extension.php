<?php

namespace T4\Extensions\Bootstrap;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        var_dump($this->path);
    }

}