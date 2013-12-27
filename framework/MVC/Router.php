<?php

namespace T4\MVC;

use T4\Core\TSingleton;
use T4\Core\Config;

use T4\MVC\ERouterException;

class Router
{
    use TSingleton;

    public function getRoute($path)
    {
        $routes = new Config(ROOT_PATH . DS . 'routes.php');
        foreach ($routes as $route) {
            if (preg_match('#' . $route['path'] . '#', $path, $m)) {
                return $route;
            }
        }
        throw new ERouterException('Route to path \'' . $path . '\' is not found');
    }

}