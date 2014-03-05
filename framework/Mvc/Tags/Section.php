<?php

namespace T4\Mvc\Tags;

use T4\Mvc\Tag;

class Section
    extends Tag
{

    protected function render()
    {
        $id = $this->params->id;
        return 'Section #'.$id;
    }

}