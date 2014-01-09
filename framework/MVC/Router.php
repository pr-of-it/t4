<?php

namespace T4\MVC;

use T4\Core\TSingleton;
use T4\Core\Config;

use T4\MVC\ERouterException;

class Router
{
    use TSingleton;

    const PATH_PATTERN = '~^\/([^\/]*?)\/([^\/]*?)\/([^\/]*?)(\((.*)\))?$~';

    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'default';

    /**
     * Ссылка на объект приложения
     * @var \T4\MVC\Application
     */
    protected $app;

    private function __construct()
    {
        $this->app = Application::getInstance();
    }

    public function parseUrl($url)
    {
        $routes = $this->app->getRouteConfig();
        if (isset($routes[$url])) {

            if (!$this->checkPath($routes[$url]))
                throw new ERouterException('Invalid route \'' . $routes[$url] . '\'');

            $route = $this->splitPath($routes[$url]);

            switch (true) {
                case false !== strpos('.html', $url):
                default:
                    $route['format'] = 'html';
                    break;
            }

            return $route;

        } else
            throw new ERouterException('Route to path \'' . $url . '\' is not found');
    }

    protected function checkPath($path)
    {
        return preg_match(self::PATH_PATTERN, $path);
    }

    protected function splitPath($path)
    {
        preg_match(self::PATH_PATTERN, $path, $m);

        $params = isset($m[5]) ? $m[5] : '';
        if (!empty($params)) {
            $params = explode(',', $params);
            $p = [];
            foreach ($params as $pair) {
                list($name, $value) = explode('=', $pair);
                $p[$name] = $value;
            }
            $params = $p;
        } else $params = [];

        return [
            'module' => $m[1],
            'controller' => $m[2] ? : self::DEFAULT_CONTROLLER,
            'action' => $m[3] ? : self::DEFAULT_ACTION,
            'params' => $params
        ];

    }

}