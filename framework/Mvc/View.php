<?php

namespace T4\Mvc;

use T4\Core\Exception;
use T4\Core\Std;

class View
{

    const TAG_PATTERN = '~<t4:(?P<tag>[^>\s]+)[\s]*(?P<params>[\s\S]*?)(/>|>)((?P<html>[\s\S]*?)</t4:(?P=tag)>)?~i';

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

        $this->twig->addGlobal('app', $this->links->app);

    }

    public function setController(Controller $controller)
    {
        $this->links->controller = $controller;
        $this->twig->addGlobal('controller', $this->links->controller);
    }

    public function render($template, $data = [])
    {
        return $this->postProcess(
            $this->twig->render($template, (array)$data)
        );
    }

    public function display($template, $data = [])
    {
        print $this->postProcess(
            $this->twig->render($template, (array)$data)
        );
    }

    protected function postProcess($content)
    {
        $content = $this->parseTags($content);
        return $content;
    }

    protected function parseTags($content)
    {
        preg_match_all(self::TAG_PATTERN, $content, $m);
        foreach ($m['tag'] as $n => $tag) {
            $tagClassName = '\\' . __NAMESPACE__ . '\\Tags\\'.ucfirst($tag);
            $tag = new $tagClassName($m['params'][$n], $m['html'][$n]);
            try {
                $content = str_replace($m[0][$n], $tag->render(), $content);
            } catch (Exception $e) {
                echo $e->getMessage();
                $content = str_replace($m[0][$n], '', $content);
            }
        }
        return $content;
    }

}