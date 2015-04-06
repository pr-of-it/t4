<?php

namespace T4\Core;

abstract class Mixin
{

    protected $_caller;

    final public function setCaller($caller)
    {
        $this->_caller = $caller;
    }

}