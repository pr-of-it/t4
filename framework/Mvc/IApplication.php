<?php

namespace T4\Mvc;

use T4\Core\Config;

interface IApplication
    extends \T4\Core\IApplication
{

    public function getModulePath($module = null);
    public function getControllerTemplatesPath($module = null, $controller);

    public function existsModule($module = null);
    public function existsController($module = null, $controller);
    public function createController($module = null, $controller);

    public function getRouteConfigPath();
    public function setRoutes(Config $config = null);
    public function getRouter() : IRouter;

    public function run();

}