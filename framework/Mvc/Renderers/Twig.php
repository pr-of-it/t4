<?php

namespace T4\Mvc\Renderers;

use T4\Core\Std;
use T4\Mvc\Application;
use T4\Mvc\ARenderer;
use T4\Mvc\Controller;
use T4\Mvc\View;

class Twig
    extends ARenderer
{

    protected $twig;
    protected $links;

    public function __construct($paths = [])
    {
        $this->paths = (array)$paths;
        $this->links = new Std;

        $this->links->app = Application::instance();

        $loader = new \Twig_Loader_Filesystem($paths);
        $this->twig = new \Twig_Environment($loader, [
            'cache' => ROOT_PATH_PROTECTED . '/Cache/Twig',
            'auto_reload' => true,
        ]);
        $this->twig->addGlobal('app', $this->links->app);
        $this->twig->addExtension(new TwigExtensions());
    }

    public function setView(View $view)
    {
        parent::setView($view);
        $this->links->view = $this->view;
        $this->twig->addGlobal('view', $this->links->view);
    }

    public function addTemplatePath($path)
    {
        parent::addTemplatePath($path);
        $loader = $this->twig->getLoader();
        $loader->setPaths($this->paths);
        $this->twig->setLoader($loader);
    }

    // TODO: непонятно что с этим делать. Вообще-то надо во View этот метод использовать, а не здесь
    public function setController(Controller $controller)
    {
        $this->links->controller = $controller;
        $this->twig->addGlobal('controller', $this->links->controller);
    }

    public function render($template, $data = [])
    {
        if ($data instanceof Std)
            $data = $data->getData();
        else
            $data = (array)$data;

        return $this->twig->render($template, $data);
    }

}