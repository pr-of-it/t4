<?php

namespace T4\Mvc;

use T4\Core\Std;

class View
{

    protected $paths = [];
    protected $twig;

    protected $links;

    public function __construct($paths = [])
    {

        $this->paths = (array)$paths;
        $this->links = new Std;
        $this->links->app = Application::getInstance();

        $loader = new \Twig_Loader_Filesystem($paths);
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addExtension(new TwigExtension());

    }

    public function setController(Controller $controller)
    {
        $this->links->controller = $controller;
        $this->twig->addGlobal('controller', $this->links->controller);
    }

    public function render($template, $data = [])
    {
        return $this->twig->render($template, $data->toArray());
    }

    public function display($template, $data)
    {
        $this->twig->display($template, $data->toArray());
    }

}