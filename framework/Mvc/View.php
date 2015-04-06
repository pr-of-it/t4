<?php

namespace T4\Mvc;

use T4\Html\Meta;
use T4\Mvc\Renderers\Vanilla;

class View
{

    /**
     * @var \T4\Mvc\ARenderer
     */
    protected $renderer;

    /**
     * @var \T4\Html\Meta
     */
    public $meta;

    public function __construct($renderer = '', $paths = [])
    {
        $this->meta = new Meta();
        if (empty($renderer) || 'vanilla' == $renderer) {
            $this->renderer = new Vanilla($paths);
        } else {
            $class = '\T4\Mvc\Renderers\\' . ucfirst($renderer);
            if (class_exists($class)) {
                $this->renderer = new $class($paths);
            } else {
                $this->renderer = new Vanilla($paths);
            }
        }
        if (method_exists($this->renderer, 'setView')) {
            $this->renderer->setView($this);
        }
    }

    public function addTemplatePath($path)
    {
        $this->renderer->addTemplatePath($path);
    }

    /**
     * @var \T4\Mvc\Controller
     */
    protected $controller;

    public function setController(Controller $controller)
    {
        $this->controller = $controller;
        if (method_exists($this->renderer, 'setController')) {
            $this->renderer->setController($controller);
        }
    }

    public function render($template, $data = [])
    {
        return $this->postProcess(
            $this->renderer->render($template, $data)
        );
    }

    public function display($template, $data = [])
    {
        print $this->render($template, $data);
    }

    protected function postProcess($content)
    {
        $content = $this->parseTags($content);
        return $content;
    }

    const TAG_PATTERN = '~<t4:(?P<tag>[^>\s]+)[\s]*(?P<params>[\s\S]*?)(/>|>)((?P<html>[\s\S]*?)</t4:(?P=tag)>)?~i';

    protected function parseTags($content)
    {
        preg_match_all(self::TAG_PATTERN, $content, $m);
        foreach ($m['tag'] as $n => $tag) {
            $tagClassName = '\T4\Mvc\Tags\\' . ucfirst($tag);
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