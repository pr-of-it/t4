<?php

namespace T4\Core;

use T4\Mvc\Application;

abstract class Widget {

    protected $options;
    protected $app;

    public function __construct($options=[])
    {
        $this->app = Application::getInstance();
        $this->options = new Std();
        $this->options->fromArray($options);
    }

    abstract public function render();

}