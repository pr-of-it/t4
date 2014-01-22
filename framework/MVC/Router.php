<?php

namespace T4\MVC;

use T4\Core\TSingleton;
use T4\Core\Config;

use T4\MVC\ERouterException;

class Router
{
    use TSingleton;

    const INTERNAL_PATH_PATTERN = '~^\/([^\/]*?)\/([^\/]*?)\/([^\/]*?)(\((.*)\))?$~';

    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'default';

    /**
     * Ссылка на объект приложения
     * @var \T4\MVC\Application
     */
    protected $app;

    protected $extensions = ['.html', '.json'];

    private function __construct()
    {
        $this->app = Application::getInstance();
    }

    public function parseUrl($url)
    {

        $baseUrl = str_replace($this->extensions, '', $url) ?: '/';
        $urlExtension = '';
        foreach ($this->extensions as $ext) {
            if (false !== strpos($url, $ext)) {
                $urlExtension = $ext;
                break;
            }
        }

        $routes = $this->app->getRouteConfig();
        if (isset($routes[$baseUrl])) {
            $route = $this->splitInternalPath($routes[$baseUrl]);
            $route['format'] = $urlExtension ? substr($urlExtension, 1): 'html';
            return $route;
        } else {
            throw new ERouterException('Route to path \'' . $baseUrl . '\' is not found');
        }

    }

    public function splitInternalPath($path)
    {
        if ( !preg_match(self::INTERNAL_PATH_PATTERN, $path, $m) ) {
            throw new ERouterException('Invalid route \'' . $routes[$baseUrl] . '\'');
        };

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