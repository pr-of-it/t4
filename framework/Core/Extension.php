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
     * @var string Путь к папке расширения
     */
    protected $path;

    /**
     * Ссылка на объект приложения
     * @var \T4\Mvc\Application
     */
    protected $app;

    public function __construct($options)
    {
        $this->options = $options;
        $reflect = new \ReflectionClass($this);
        $this->path = dirname($reflect->getFileName());
    }

    public function setApp(Application $app)
    {
        $this->app = $app;
    }

    abstract public function init();

}