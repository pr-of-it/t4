<?php

namespace T4\Html\Elements;

class Number
    extends Input
{

    public function render()
    {
        $this->setType('number');
        return parent::render();
    }

}