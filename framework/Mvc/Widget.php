<?php

namespace T4\Mvc;

use T4\Core\Std;

abstract class Widget
{

    protected $options;
    protected $app;

    public function __construct($options = [])
    {
        $this->app = Application::instance();
        $this->options = new Std();
        $this->options->fromArray($options);
    }

    abstract public function render();

}