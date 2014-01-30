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
    }

    public function render($template, $data = [])
    {
        if ( !($data instanceof Std) )
            $data = new Std($data);
        $data->this = $this->links;
        return $this->twig->render($template, $data->toArray());
    }

    public function display($template, $data)
    {
        if ( !($data instanceof Std) )
            $data = new Std($data);
        $data->this = $this->links;
        $this->twig->display($template, $data->toArray());
    }

}