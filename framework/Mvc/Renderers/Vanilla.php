<?php

namespace T4\Mvc\Renderers;

use T4\Core\Helpers;
use T4\Mvc\Controller;
use T4\Mvc\ViewException;

class Vanilla
{

    protected $paths = [];
    protected $controller;

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

    public function addTemplatePath($path)
    {
        $this->paths[] = Helpers::getRealPath($path);
    }

    protected function findTemplate($template)
    {
        foreach ($this->paths as $path) {
            if (is_readable($path . DS . $template))
                return $path . DS . $template;
        }
        throw new ViewException('Cannot find template \'' . $template . '\' in paths [' . implode(';', $this->paths) . ']');
    }

    public function render($template, $data = [])
    {
        if ($data instanceof Std)
            extract($data->getData());
        else
            extract((array)$data);

        $templatePath = $this->findTemplate($template);

        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        return $content;
    }

    public function display($template, $data = [])
    {
        print $this->render($template, $data);
    }

}