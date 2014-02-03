<?php

namespace T4\Mvc;

use T4\Core\Exception;
use T4\Core\Std;

class View
{

    const BLOCK_TAG_PATTERN = '~\<t4:block[\s]+path=\"(.*)\"([\s]+\/)?\>~i';

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
        return $this->postProcess(
            $this->twig->render($template, $data->toArray())
        );
    }

    public function display($template, $data = [])
    {
        print $this->postProcess(
            $this->twig->render($template, $data->toArray())
        );
    }

    protected function postProcess($content)
    {
        $content = $this->parseBlocks($content);
        return $content;
    }

    protected function parseBlocks($content)
    {
        $app = Application::getInstance();
        preg_match_all(self::BLOCK_TAG_PATTERN, $content, $m);
        foreach ($m[0] as $n => $tag) {
            $blockPath = $m[1][$n];
            try {
                $block = $app->callBlock($blockPath);
                $content = str_replace($tag, $block, $content);
            } catch (Exception $e) {
                echo $e->getMessage();
                $content = str_replace($tag, '', $content);
            }
        }
        return $content;
    }

}