<?php

namespace T4\Mvc\Tags;

use T4\Core\Exception;
use T4\Mvc\Application;
use T4\Mvc\Tag;

class Block
    extends Tag
{

    public function render()
    {
        try {
            $app = Application::instance();

            $path = $this->params->path;
            unset($this->params->path);

            $template = isset($this->params->template) ? $this->params->template : '';
            unset($this->params->template);

            $block = $app->callBlock($path, $template, $this->params);
            return $block;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

}