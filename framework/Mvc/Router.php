<?php

namespace T4\Mvc;

use T4\Core\Std;
use T4\Core\TSingleton;

class Router
{
    use TSingleton;

    const INTERNAL_PATH_PATTERN = '~^\/([^\/]*?)\/([^\/]*?)\/([^\/]*?)\/?(\((.*)\))?$~i';

    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'Default';

    /**
     * @var \T4\Core\Config
     */
    protected $config;

    /**
     * Распознаваемые расширения в URL
     * @var array
     */
    protected $allowedExtensions = ['html', 'json'];

    /**
     * @param \T4\Core\Std $config
     * @return \T4\Mvc\Router $this
     */
    public function setConfig(Std $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Разбирает URL, поступивший из браузера,
     * используя методы разбора URL и внутреннего пути.
     * Возвращает объект роутинга
     * @param string $url
     * @return \T4\Mvc\Route
     * @throws \T4\Mvc\RouterException
     */
    public function parseUrl($url)
    {
        $url = $this->splitExternalPath($url);

        if (!empty($this->config)) {
            foreach ($this->config as $urlTemplate => $internalPath) {
                if (false !== $params = $this->matchUrlTemplate($urlTemplate, $url->base)) {
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
        }

        return $this->guessInternalPath($url);
    }

    /**
     * Разбирает URL, выделяя basePath и расширение
     * @param string $url
     * @return \T4\Mvc\Route
     */
    protected function splitExternalPath($url)
    {
        $parts = parse_url($url);
        $basePath = isset($parts['path']) ? $parts['path'] : null;

        if (empty($basePath)) {
            $extension = null;
        } else {
            $extension = pathinfo($basePath, PATHINFO_EXTENSION);
            $basePath = str_replace('.' . $extension, '', $basePath);
        }

        if (!in_array($extension, $this->allowedExtensions)) {
            $extension = '';
        }

        return new Route([
            'base' => $basePath,
            'extension' => $extension,
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
        $template = '~^' . preg_replace('~\<(\d+)\>~', '(?<p_$1>.+?)', $template) . '$~i';
        if (!preg_match($template, $url, $m)) {
            return false;
        } else {
            $matches = [];
            foreach ($m as $key => $value) {
                if (substr($key, 0, 2) == 'p_') {
                    $matches[substr($key, 2)] = $value;
                }
            }
            return $matches;
        }
    }

    /**
     * Разбирает внутренний путь /модуль/контроллер/действие(параметры)
     * Возвращает объект роутинга
     * @param string $path
     * @return \T4\Mvc\Route
     * @throws \T4\Mvc\RouterException
     */
    public function splitInternalPath($path)
    {
        if (!preg_match(self::INTERNAL_PATH_PATTERN, $path, $m)) {
            throw new RouterException('Invalid route \'' . $path . '\'');
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
            'module' => ucfirst($m[1]),
            'controller' => ucfirst($m[2]) ? : self::DEFAULT_CONTROLLER,
            'action' => ucfirst($m[3]) ? : self::DEFAULT_ACTION,
            'params' => $params
        ]);

    }

    /**
     * Возвращает канонический внутренний путь, построенный из объекта роутинга
     * Не учитывает параметры
     * @param Route $route
     * @return string
     */
    public function makeInternalPath(Route $route)
    {
        return '/' . $route->module . '/' .
        ($route->controller == self::DEFAULT_CONTROLLER ? '' : $route->controller) . '/' .
        ($route->action == self::DEFAULT_ACTION ? '' : $route->action);
    }

    /**
     * Пытается подобрать соответствующий роутинг для URL, отсутствующего в конфиге роутинга
     * @param \T4\Mvc\Route $url
     * @return Route
     * @throws RouterException
     */
    protected function guessInternalPath($url)
    {
        $urlParts = preg_split('~/~', $url->base, -1, PREG_SPLIT_NO_EMPTY);
        $app = \T4\Mvc\Application::getInstance();

        if (0 == count($urlParts)) {
            return new Route([
                'module' => '',
                'controller' => self::DEFAULT_CONTROLLER,
                'action' => self::DEFAULT_ACTION,
                'params' => [],
                'format' => $url->extension ? : 'html',
            ]);
        }

        if (1 == count($urlParts)) {
            if ($app->existsModule($urlParts[0]))
                return new Route([
                    'module' => ucfirst($urlParts[0]),
                    'controller' => self::DEFAULT_CONTROLLER,
                    'action' => self::DEFAULT_ACTION,
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            elseif ($app->existsController('', $urlParts[0]))
                return new Route([
                    'module' => '',
                    'controller' => ucfirst($urlParts[0]),
                    'action' => self::DEFAULT_ACTION,
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            else
                return new Route([
                    'module' => '',
                    'controller' => self::DEFAULT_CONTROLLER,
                    'action' => ucfirst($urlParts[0]),
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
        }

        if (2 == count($urlParts)) {
            if ($app->existsModule($urlParts[0])) {
                if ($app->existsController($urlParts[0], $urlParts[1])) {
                    return new Route([
                        'module' => ucfirst($urlParts[0]),
                        'controller' => ucfirst($urlParts[1]),
                        'action' => self::DEFAULT_ACTION,
                        'params' => [],
                        'format' => $url->extension ? : 'html',
                    ]);
                } else {
                    return new Route([
                        'module' => ucfirst($urlParts[0]),
                        'controller' => self::DEFAULT_CONTROLLER,
                        'action' => ucfirst($urlParts[1]),
                        'params' => [],
                        'format' => $url->extension ? : 'html',
                    ]);
                }
            } elseif ($app->existsController('', $urlParts[0])) {
                return new Route([
                    'module' => '',
                    'controller' => ucfirst($urlParts[0]),
                    'action' => ucfirst($urlParts[1]),
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            }
        }

        if (3 == count($urlParts)) {
            if ($app->existsModule($urlParts[0]) && $app->existsController($urlParts[0], $urlParts[1])) {
                return new Route([
                    'module' => ucfirst($urlParts[0]),
                    'controller' => ucfirst($urlParts[1]),
                    'action' => ucfirst($urlParts[2]),
                    'params' => [],
                    'format' => $url->extension ? : 'html',
                ]);
            }
        }

        throw new RouterException('Route to path \'' . $url->base . '\' is not found');

    }

}