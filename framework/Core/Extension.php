<?php

namespace T4\Core;

use T4\Mvc\IApplication;

abstract class Extension
{

    /**
     * Опции, заданные через файл конфигурации
     * @var \T4\Core\Config
     */
    protected $options;

    /**
     * @var string Физический путь к папке расширения
     */
    protected $path;

    /**
     * @var string Путь к папке приложения в условной системе assets
     */
    protected $assetsPath;

    /**
     * Ссылка на объект приложения
     * @var \T4\Mvc\IApplication
     */
    protected $app;

    public function __construct($options)
    {
        $this->options = $options;
        $reflect = new \ReflectionClass($this);
        $this->path = dirname($reflect->getFileName());
        $this->assetsPath = '/' . str_replace(DS, '/', str_replace(\T4\ROOT_PATH, '', dirname($reflect->getFileName())));

    }

    public function setApp(IApplication $app)
    {
        $this->app = $app;
    }

    abstract public function init();

}