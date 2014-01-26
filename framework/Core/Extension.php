<?php

namespace T4\Core;

use T4\Mvc\Application;

abstract class Extension
{

    /**
     * Опции, заданные через файл конфигурации
     * @var \T4\Core\Config
     */
    protected $options;

    /**
     * Ссылка на объект приложения
     * @var \T4\Mvc\Application
     */
    protected $app;

    public function __construct($options)
    {
        $this->options = $options;
        $this->app = Application::getInstance();
    }

    abstract public function init();

}