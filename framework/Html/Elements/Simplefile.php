<?php

namespace T4\Html\Elements;

class Simplefile
    extends Input
{

    public function render()
    {
        $this->setType('file');
        return parent::render();
    }

} 