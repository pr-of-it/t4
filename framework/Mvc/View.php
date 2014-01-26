<?php

namespace T4\Mvc;

use T4\Core\Std;

class View
{

    protected $paths = [];
    protected $twig;

    public function __construct($paths = [])
    {
        $this->paths = (array)$paths;
        $loader = new \Twig_Loader_Filesystem($paths);
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addExtension(new TwigExtension());
    }

    public function render($template, $data = [])
    {
        return $this->twig->render($template, $data);
    }

    public function display($template, Std $data)
    {
        $this->twig->display($template, (array)$data);
    }

}