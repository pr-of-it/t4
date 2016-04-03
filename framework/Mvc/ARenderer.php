<?php

namespace T4\Mvc;

use T4\Fs\Helpers;

abstract class ARenderer
{
    protected $view;
    protected $controller;
    protected $paths = [];

    public function __construct($paths = [])
    {
        foreach ((array)$paths as $path) {
            $this->addTemplatePath($path);
        }
    }

    public function setView(View $view)
    {
        $this->view = $view;
    }

    public function addTemplatePath($path)
    {
        $this->paths[] = Helpers::getRealPath($path);
    }

    final protected function findTemplate($template)
    {
        foreach ($this->paths as $path) {
            if (is_readable($path . DS . $template))
                return $path . DS . $template;
        }
        throw new ViewException('Cannot find template \'' . $template . '\' in paths [' . implode(';', $this->paths) . ']');
    }

    abstract public function render($template, $data = []);

}