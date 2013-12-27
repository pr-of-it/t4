<?php

namespace T4\MVC;

use T4\Core\TSingleton;
use T4\Core\Config;

use T4\MVC\ERouterException;

class Router
{
    use TSingleton;

    public function parseUrl($url)
    {
        $routes = new Config(ROOT_PATH . DS . 'routes.php');
        /*
        foreach ($routes as $route) {
            if (preg_match('#' . $route['path'] . '#', $url, $m)) {
                return $route;
            }
        }
        */
        if ( isset($routes[$url]) ) {
            // TODO: не работает регулярка!
            if ( !preg_match('#\/([a-zA-Z0-9_-]*)\/([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+)#', $routes[$url]) )
                throw new ERouterException('Invalid route \'' . $routes[$url] . '\' is not found');
            return $this->splitPath($routes[$url]);
        } else
            throw new ERouterException('Route to path \'' . $url . '\' is not found');

    }

    protected function splitPath($path) {
        $path = explode('/', $path);
        return [
            'module'        => $path[1],
            'controller'    => $path[2],
            'action'        => $path[3],
        ];
    }

}