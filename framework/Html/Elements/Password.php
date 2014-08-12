<?php

namespace T4\Html\Elements;

class Password
    extends Input
{

    public function render()
    {
        $this->setType('password');
        return parent::render();
    }

}