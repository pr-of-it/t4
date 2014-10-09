<?php

namespace T4\Mvc;

use T4\Core\Helpers;

abstract class ARenderer
{
    protected $controller;
    protected $paths = [];

    public function __construct($paths = [])
    {
        foreach ((array)$paths as $path) {
            $this->addTemplatePath($path);
        }
    }

    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    final public function addTemplatePath($path)
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

    final public function display($template, $data = [])
    {
        print $this->render($template, $data);
    }

} 