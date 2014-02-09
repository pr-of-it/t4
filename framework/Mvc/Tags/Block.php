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
        $path = $this->params->path;
        unset($this->params->path);
        $block = $app->callBlock($path, $this->params);
        return $block;
    }

}