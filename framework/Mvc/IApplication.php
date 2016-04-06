<?php

namespace T4\Mvc;

use T4\Core\Config;

interface IApplication
{

    public function getPath();
    public function getRouteConfigPath();
    public function getModulePath($module = null);
    public function getControllerTemplatesPath($module = null, $controller);

    public function existsModule($module = null);
    public function existsController($module = null, $controller);

    public function setConfig(Config $config = null);
    public function setRoutes(Config $config = null);

}