<?php

namespace T4\MVC;

use T4\Core\TSingleton;

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

    protected $extensions = ['html', 'json'];

    protected function __construct()
    {
        $this->app = Application::getInstance();
    }

    /**
     * Разбирает URL, поступивший из браузера,
     * используя методы разбора URL и внутреннего пути.
     * Возвращает объект роутинга
     * @param string $url
     * @return \T4\MVC\Route
     * @throws \T4\MVC\ERouterException
     */
    public function parseUrl($url)
    {

        $url = $this->splitExternalPath($url);
        $routes = $this->getRoutes();

        foreach ($routes as $urlTemplate => $internalPath) {
            if ( false !== $params = $this->matchUrlTemplate($urlTemplate, $url->base) )
            {
                $internalPath = preg_replace_callback(
                    '~\<(\d+)\>~',
                    function ($m) use ($params) {
                        return $params[$m[1]];
                    },
                    $internalPath
                );
                $route = $this->splitInternalPath($internalPath);
                $route->format = $url->extension ? : 'html';
                return $route;
            }
        }

        throw new ERouterException('Route to path \'' . $url->base . '\' is not found');

    }

    /**
     * Конфиг с правилами роутинга
     * @return \T4\Core\Config
     */
    protected function getRoutes()
    {
        return $this->app->getRouteConfig();
    }

    /**
     * Разбирает URL, выделяя basePath и расширение
     * @param string $url
     * @return \T4\MVC\Route
     */
    protected function splitExternalPath($url)
    {
        $urlExtension = '';
        foreach ($this->extensions as $ext) {
            if (false !== strpos($url, '.' . $ext)) {
                $urlExtension = $ext;
                break;
            }
        }
        $baseUrl = str_replace('.' . $urlExtension, '', $url) ? : '/';
        return new Route([
            'base' => $baseUrl,
            'extension' => $urlExtension,
        ]);
    }

    /**
     * Проверка соответствия URL (базового) его шаблону из правил роутинга
     * Возвращает false в случае несоответствия
     * или массив параметров (возможно - пустой) в случае совпадения URL с шаблоном
     * @param string $template
     * @param string $url
     * @return array|bool
     */
    protected function matchUrlTemplate($template, $url)
    {
        $template = '~^'.preg_replace('~\<(\d+)\>~', '(?<p_$1>.+?)', $template).'$~';
        if (!preg_match($template, $url, $m)) {
            return false;
        } else {
            $matches = [];
            foreach ( $m as $key => $value ) {
                if (substr($key, 0, 2)=='p_') {
                    $matches[substr($key, 2)] = $value;
                }
            }
            return $matches;
        };
    }

    /**
     * Разбирает внутренний путь /модуль/контроллер/действие(параметры)
     * Возвращает объект роутинга
     * @param string $path
     * @return \T4\MVC\Route
     * @throws \T4\MVC\ERouterException
     */
    protected function splitInternalPath($path)
    {
        if (!preg_match(self::INTERNAL_PATH_PATTERN, $path, $m)) {
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

        return new Route([
            'module' => $m[1],
            'controller' => $m[2] ? : self::DEFAULT_CONTROLLER,
            'action' => $m[3] ? : self::DEFAULT_ACTION,
            'params' => $params
        ]);

    }

}