<?php

namespace T4\Core;

abstract class Mixin {

    protected $caller;

    final public function setCaller($caller)
    {
        $this->$caller = $caller;
    }

}