<?php

namespace T4\Html\Elements;

class Int
    extends Input
{

    public function render()
    {
        $this->setType('number');
        return parent::render();
    }

}