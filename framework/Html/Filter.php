<?php

namespace T4\Html;

use T4\Dbal\Connection;
use T4\Mvc\View;

abstract class Filter
{

    protected $name;
    protected $value;
    protected $options = [];

    public function __construct($name, $value, $options = [])
    {
        $this->name  = $name;
        $this->value = $value;
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    protected function setOptions($options = [])
    {
        $this->options = $options;
        return $this;
    }

    abstract public function getQueryOptions(Connection $connection, $options = []) : array;

    public function renderFormElement(array $htmlOptions = []) : string
    {
        if (isset($this->options['template'])) {
            $dir = dirname($this->options['template']);
            $template = basename($this->options['template']);
        } else {
            $reflector = new \ReflectionClass(static::class);
            $filename = $reflector->getFileName();
            $dir = dirname($filename);
            $template = pathinfo(basename($filename), PATHINFO_FILENAME) . '.html';
        }

        $view = new View('Twig');
        $view->addTemplatePath($dir);
        return $view->render($template, [
            'name' => $this->name,
            'value' => $this->value,
            'options' => $this->options,
            'html' => $htmlOptions,
        ]);
    }

}