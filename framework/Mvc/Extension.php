<?php

namespace T4\Mvc;

use T4\Core\Config;
use T4\Core\Std;

/**
 * Class Extension
 * @package T4\Mvc
 *
 * @property \T4\Core\Config $config
 * @property string $path
 * @property string $assetsPath
 *
 * @property \T4\Mvc\Application $app
 */
abstract class Extension
    extends Std
{

    public function __construct(Config $config)
    {
        $this->config = $config;
        $reflect = new \ReflectionClass($this);
        $this->path = dirname($reflect->getFileName());
        $this->assetsPath = '/' . str_replace(DS, '/', str_replace(\T4\ROOT_PATH, '', $this->path));
        $this->app = Application::instance();
    }

    abstract public function init();

}