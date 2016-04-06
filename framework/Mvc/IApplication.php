<?php

namespace T4\Mvc;

interface IApplication
{

    public function getPath();
    public function getRouteConfigPath();
    public function getModulePath($module = null);
    public function getControllerTemplatesPath($module = null, $controller);

    public function existsModule($module = null);
    public function existsController($module = null, $controller);

}