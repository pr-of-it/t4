<?php

namespace T4\Widgets;

use T4\Mvc\Widget;

class ActiveTable
    extends Widget
{

    public function render()
    {
        var_dump($this->options->data);
    }

}