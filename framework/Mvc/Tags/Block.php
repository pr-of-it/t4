<?php

namespace T4\Mvc\Tags;

use T4\Mvc\Application;
use T4\Mvc\Tag;

class Block
    extends Tag
{

    protected function render()
    {
        $app = Application::getInstance();
        $block = $app->callBlock($this->params->path, $this->params);
        return $block;
    }

}