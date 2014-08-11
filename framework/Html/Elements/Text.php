<?php

namespace T4\Html\Elements;

class Text
    extends Input
{

    public function render()
    {
        $this->setType('text');
        return parent::render();
    }

}