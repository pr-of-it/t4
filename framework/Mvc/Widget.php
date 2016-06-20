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
        foreach ($options as $key => $value) {
            $this->options->{$key} = $value;
        }
    }

    abstract public function render();

}